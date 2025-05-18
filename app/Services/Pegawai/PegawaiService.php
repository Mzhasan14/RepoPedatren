<?php

namespace App\Services\Pegawai;

use App\Models\Alamat\Kabupaten;
use App\Models\Alamat\Kecamatan;
use App\Models\Alamat\Negara;
use App\Models\Alamat\Provinsi;
use App\Models\Berkas;
use App\Models\Biodata;
use App\Models\JenisBerkas;
use App\Models\Keluarga;
use App\Models\Pegawai\Golongan;
use App\Models\Pegawai\GolonganJabatan;
use App\Models\Pegawai\Karyawan;
use App\Models\Pegawai\KategoriGolongan;
use App\Models\Pegawai\MateriAjar;
use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\Pengajar;
use App\Models\Pegawai\Pengurus;
use App\Models\Pegawai\RiwayatJabatanKaryawan;
use App\Models\Pegawai\WaliKelas;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Lembaga;
use App\Models\Pendidikan\Rombel;
use App\Models\WargaPesantren;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PegawaiService
{
    public function getAllPegawai(Request $request)
    {
        try
        {
            // 1) Ambil ID untuk jenis berkas "Pas foto"
            $pasFotoId = DB::table('jenis_berkas')
                        ->where('nama_jenis_berkas', 'Pas foto')
                        ->value('id');
        
            // 2) Subquery: foto terakhir per biodata
            $fotoLast = DB::table('berkas')
                        ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
                        ->where('jenis_berkas_id', $pasFotoId)
                        ->groupBy('biodata_id');
            // 3) Subquery: warga pesantren terakhir per biodata
            $wpLast = DB::table('warga_pesantren')
                        ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
                        ->where('status', true)
                        ->groupBy('biodata_id');
        
            // 4) Query utama
            return DB::table('pegawai')
                            ->join('biodata as b','b.id','pegawai.biodata_id')
                            // join warga pesantren terakhir true (NIUP)
                            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
                            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id') 
                            // join pengajar yang hanya berstatus aktif                    
                            ->leftJoin('pengajar', function($join) {
                                $join->on('pengajar.pegawai_id', '=', 'pegawai.id')
                                     ->where('pengajar.status_aktif', 'aktif')
                                     ->whereNull('pengajar.deleted_at');
                            })
                            // join pengurus yang hanya berstatus aktif
                            ->leftJoin('pengurus', function($join) {
                                $join->on('pengurus.pegawai_id', '=', 'pegawai.id')
                                     ->where('pengurus.status_aktif', 'aktif')
                                     ->whereNull('pengurus.deleted_at');
                            })
                            // join karyawan yang hanya berstatus aktif
                            ->leftJoin('karyawan', function($join) {
                                $join->on('karyawan.pegawai_id', '=', 'pegawai.id')
                                     ->where('karyawan.status_aktif', 'aktif')
                                     ->whereNull('karyawan.deleted_at');

                            })
                            // join wali kelas yang hanya berstatus aktif
                            ->leftJoin('wali_kelas', function($join) {
                                $join->on('pegawai.id', '=', 'wali_kelas.pegawai_id')
                                     ->where('wali_kelas.status_aktif', 'aktif')
                                     ->whereNull('wali_kelas.deleted_at');

                            })
                            // join berkas pas foto terakhir
                            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
                            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                            ->whereNull('pegawai.deleted_at')
                            ->where('pegawai.status_aktif','aktif')
                            ->select(
                                'pegawai.biodata_id as biodata_uuid',
                                'pegawai.id as id',
                                'b.nama as nama',
                                'wp.niup',
                                'pengurus.id as pengurus',
                                'karyawan.id as karyawan',
                                'pengajar.id as pengajar',
                                DB::raw("TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur"),
                                DB::raw("TRIM(BOTH ', ' FROM CONCAT_WS(', ', 
                                GROUP_CONCAT(DISTINCT CASE WHEN pengajar.id IS NOT NULL THEN 'Pengajar' END SEPARATOR ', '),
                                GROUP_CONCAT(DISTINCT CASE WHEN karyawan.id IS NOT NULL THEN 'Karyawan' END SEPARATOR ', '),
                                GROUP_CONCAT(DISTINCT CASE WHEN pengurus.id IS NOT NULL THEN 'Pengurus' END SEPARATOR ', ')
                            )) as status"),
                                'b.nama_pendidikan_terakhir as pendidikanTerkahir',
                                DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
                                )->groupBy(
                                    'pegawai.biodata_id', 
                                    'pegawai.id',
                                    'b.nama',
                                    'wp.niup',
                                    'pengurus.id',
                                    'karyawan.id',
                                    'pengajar.id',
                                    'b.tanggal_lahir',
                                    'b.nama_pendidikan_terakhir'
                                );                                
    }
        catch (\Exception $e) {
            Log::error('Error fetching data pegawai: ' . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "Terjadi kesalahan saat mengambil data pegawai",
                "code" => 500
            ], 500);
        }
                            
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            "biodata_id" => $item->biodata_uuid,
            "id" => $item->id,
            "nama" => $item->nama,
            "niup" => $item->niup ?? '-',
            "umur" => $item->umur,
            "status" => $item->status,
            "pendidikanTerkahir" => $item->pendidikanTerkahir,
            "pengurus" => $item->pengurus ? true : false,
            "karyawan" => $item->karyawan ? true : false,
            "pengajar" => $item->pengajar ? true : false,
            "foto_profil" => $item->foto_profil ? asset('storage/' . $item->foto_profil) : null,
        ]);
    }

    public function store(array $input)
    {
        DB::beginTransaction();

        try {
            $isExisting = false;

            // Cek apakah NIK sudah terdaftar
            $existingBiodata = Biodata::where('nik', $input['nik'])->first();

            if ($existingBiodata) {
                $isExisting = true;

                // Cek apakah sudah terdaftar sebagai pegawai
                $existingPegawai = Pegawai::where('biodata_id', $existingBiodata->id)->first();

                if ($existingPegawai) {
                    // Aktifkan kembali jika nonaktif
                    if ($existingPegawai->status_aktif !== 'aktif') {
                        $existingPegawai->update(['status_aktif' => 'aktif']);
                        if (method_exists($existingPegawai, 'restore')) {
                            $existingPegawai->restore();
                        }
                    }

                    // Validasi untuk masing-masing role
                    if (!empty($input['karyawan']) && Karyawan::where('pegawai_id', $existingPegawai->id)
                        ->where('status_aktif', 'aktif')
                        ->exists()) {
                        throw ValidationException::withMessages([
                            'karyawan' => ['Pegawai untuk data ini sudah ada dan masih memiliki status karyawan aktif.'],
                        ]);
                    }

                    if (!empty($input['pengajar']) && Pengajar::where('pegawai_id', $existingPegawai->id)
                        ->where('status_aktif', 'aktif')
                        ->exists()) {
                        throw ValidationException::withMessages([
                            'pengajar' => ['Pegawai untuk data ini sudah ada dan masih memiliki status pengajar aktif.'],
                        ]);
                    }

                    if (!empty($input['pengurus']) && Pengurus::where('pegawai_id', $existingPegawai->id)
                        ->where('status_aktif', 'aktif')
                        ->exists()) {
                        throw ValidationException::withMessages([
                            'pengurus' => ['Pegawai untuk data ini sudah ada dan masih memiliki status pengurus aktif.'],
                        ]);
                    }

                    if (!empty($input['wali_kelas']) && WaliKelas::where('pegawai_id', $existingPegawai->id)
                        ->where('status_aktif', 'aktif')
                        ->exists()) {
                        throw ValidationException::withMessages([
                            'wali_kelas' => ['Pegawai untuk data ini sudah ada dan masih memiliki status wali kelas aktif.'],
                        ]);
                    }
                }

                // Update biodata lama
                $existingBiodata->update([
                    'negara_id' => $input['negara_id'],
                    'provinsi_id' => $input['provinsi_id'],
                    'kabupaten_id' => $input['kabupaten_id'],
                    'kecamatan_id' => $input['kecamatan_id'],
                    'jalan' => $input['jalan'],
                    'kode_pos' => $input['kode_pos'],
                    'nama' => $input['nama'],
                    'no_passport' => $input['no_passport'],
                    'tanggal_lahir' => Carbon::parse($input['tanggal_lahir']),
                    'jenis_kelamin' => $input['jenis_kelamin'],
                    'tempat_lahir' => $input['tempat_lahir'],
                    'nik' => $input['nik'],
                    'no_telepon' => $input['no_telepon'],
                    'no_telepon_2' => $input['no_telepon_2'],
                    'email' => $input['email'],
                    'jenjang_pendidikan_terakhir' => $input['jenjang_pendidikan_terakhir'],
                    'nama_pendidikan_terakhir' => $input['nama_pendidikan_terakhir'],
                    'anak_keberapa' => $input['anak_keberapa'],
                    'dari_saudara' => $input['dari_saudara'],
                    'tinggal_bersama' => $input['tinggal_bersama'],
                    'smartcard' => $input['smartcard'],
                    'status' => 1,
                    'wafat' => $input['wafat'],
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                ]);
                $biodata = $existingBiodata;
            } else {
                // Insert biodata baru
                $biodata = Biodata::create([
                    'id' => Str::uuid(),
                    'negara_id' => $input['negara_id'],
                    'provinsi_id' => $input['provinsi_id'],
                    'kabupaten_id' => $input['kabupaten_id'],
                    'kecamatan_id' => $input['kecamatan_id'],
                    'jalan' => $input['jalan'],
                    'kode_pos' => $input['kode_pos'],
                    'nama' => $input['nama'],
                    'no_passport' => $input['no_passport'],
                    'tanggal_lahir' => Carbon::parse($input['tanggal_lahir']),
                    'jenis_kelamin' => $input['jenis_kelamin'],
                    'tempat_lahir' => $input['tempat_lahir'],
                    'nik' => $input['nik'],
                    'no_telepon' => $input['no_telepon'],
                    'no_telepon_2' => $input['no_telepon_2'],
                    'email' => $input['email'],
                    'jenjang_pendidikan_terakhir' => $input['jenjang_pendidikan_terakhir'],
                    'nama_pendidikan_terakhir' => $input['nama_pendidikan_terakhir'],
                    'anak_keberapa' => $input['anak_keberapa'],
                    'dari_saudara' => $input['dari_saudara'],
                    'tinggal_bersama' => $input['tinggal_bersama'],
                    'smartcard' => $input['smartcard'],
                    'status' => 1,
                    'wafat' => $input['wafat'],
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                ]);
            }

            // Simpan keluarga jika ada no_kk
            if (!empty($input['no_kk'])) {
                Keluarga::updateOrCreate(
                    ['id_biodata' => $biodata->id],
                    [
                        'no_kk' => $input['no_kk'],
                        'status' => 1,
                        'created_by' => Auth::id(),
                    ]
                );
            }

            // Simpan warga pesantren jika ada niup
            if (!empty($input['niup'])) {
                WargaPesantren::updateOrCreate(
                    ['biodata_id' => $biodata->id],
                    [
                        'niup' => $input['niup'],
                        'status' => 1,
                        'created_by' => Auth::id(),
                    ]
                );
            }

            // Simpan berkas
            if (!empty($input['berkas']) && is_array($input['berkas'])) {
                foreach ($input['berkas'] as $item) {
                    if (!($item['file_path'] instanceof UploadedFile)) {
                        throw new \Exception('Berkas tidak valid');
                    }

                    $path = $item['file_path']->store('berkas', 'public');

                    Berkas::create([
                        'biodata_id' => $biodata->id,
                        'jenis_berkas_id' => (int) $item['jenis_berkas_id'],
                        'file_path' => Storage::url($path),
                        'status' => true,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            // Buat atau ambil pegawai
            $pegawai = Pegawai::firstOrCreate(
                ['biodata_id' => $biodata->id],
                ['status_aktif' => 'aktif', 'created_by' => Auth::id()]
            );

            $resultData = [];

            // Simpan karyawan jika ada
            if (!empty($input['karyawan'])) {
                $karyawan = Karyawan::create([
                    'pegawai_id' => $pegawai->id,
                    'golongan_jabatan_id' => $input['golongan_jabatan_id_karyawan'] ?? null,
                    'lembaga_id' => $input['lembaga_id_karyawan'] ?? null,
                    'jabatan' => $input['jabatan_karyawan'] ?? null,
                    'keterangan_jabatan' => $input['keterangan_jabatan_karyawan'] ?? null,
                    'tanggal_mulai' => $input['tanggal_mulai_karyawan'] ?? now(),
                    'status_aktif' => 'aktif',
                    'created_by' => Auth::id(),
                ]);
                $resultData['karyawan'] = $karyawan;
            }

            // Simpan pengajar jika ada
            if (!empty($input['pengajar'])) {
                $pengajar = Pengajar::create([
                    'pegawai_id' => $pegawai->id,
                    'golongan_id' => $input['golongan_id_pengajar'] ?? null,
                    'lembaga_id' => $input['lembaga_id_pengajar'] ?? null,
                    'jabatan' => $input['jabatan_pengajar'] ?? null,
                    'tahun_masuk' => $input['tanggal_mulai_pengajar'] ?? now(),
                    'status_aktif' => 'aktif',
                    'created_by' => Auth::id(),
                ]);
                $resultData['pengajar'] = $pengajar;

                // Handle multiple materi ajar
                if (!empty($input['materi_ajar']) && is_array($input['materi_ajar'])) {
                    foreach ($input['materi_ajar'] as $materi) {
                        MateriAjar::create([
                            'pengajar_id' => $pengajar->id,
                            'nama_materi' => $materi['nama_materi'],
                            'jumlah_menit' => $materi['jumlah_menit'] ?? null,
                            'tahun_masuk' => $input['tanggal_mulai_materi'] ?? now(),
                            'status_aktif' => 'aktif',
                            'created_by' => Auth::id(),
                        ]);
                    }
                }
            }

            // Simpan pengurus jika ada
            if (!empty($input['pengurus'])) {
                $pengurus = Pengurus::create([
                    'pegawai_id' => $pegawai->id,
                    'golongan_jabatan_id' => $input['golongan_jabatan_id_pengurus'] ?? null,
                    'jabatan' => $input['jabatan_pengurus'] ?? null,
                    'satuan_kerja' => $input['satuan_kerja_pengurus'] ?? null,
                    'keterangan_jabatan' => $input['keterangan_jabatan_pengurus'] ?? null,
                    'tanggal_mulai' => $input['tanggal_mulai_pengurus'] ?? now(),
                    'status_aktif' => 'aktif',
                    'created_by' => Auth::id(),
                ]);
                $resultData['pengurus'] = $pengurus;
            }

            // Simpan wali kelas jika ada
            if (!empty($input['wali_kelas'])) {
                $waliKelas = WaliKelas::create([
                    'pegawai_id' => $pegawai->id,
                    'lembaga_id' => $input['lembaga_id_wali'] ?? null,
                    'jurusan_id' => $input['jurusan_id_wali'] ?? null,
                    'kelas_id' => $input['kelas_id_wali'] ?? null,
                    'rombel_id' => $input['rombel_id_wali'] ?? null,
                    'jumlah_murid' => $input['jumlah_murid_wali'] ?? null,
                    'periode_awal' => $input['periode_awal_wali'] ?? now(),
                    'status_aktif' => 'aktif',
                    'created_by' => Auth::id(),
                ]);
                $resultData['wali_kelas'] = $waliKelas;
            }

            DB::commit();

            return [
                'status' => true,
                'message' => $isExisting
                    ? 'NIK sudah terdaftar, Silahkan anda cek kembali di fitur Pegawai.'
                    : 'Pegawai baru berhasil ditambahkan.',
                'data' => array_merge(['pegawai' => $pegawai], $resultData),
            ];

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Membuat Data pegawai: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'Gagal menyimpan data pegawai.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null,
            ];
        }
    }

    // public function destroy($pegawaiId)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $pegawai = Pegawai::findOrFail($pegawaiId);

    //         // Update status pegawai
    //         $pegawai->status_aktif = 'tidak aktif';
    //         $pegawai->deleted_at = now();
    //         $pegawai->save();

    //         // Update semua relasi karyawan aktif yang belum memiliki tanggal_selesai
    //         $pegawai->karyawan()
    //             ->where('status_aktif', 'aktif')
    //             ->whereNull('tanggal_selesai')
    //             ->update([
    //                 'status_aktif' => 'tidak aktif',
    //                 'tanggal_selesai' => now()
    //             ]);

    //         DB::commit();

    //         return [
    //             'status' => true,
    //             'message' => 'Pegawai berhasil dinonaktifkan.',
    //         ];

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Gagal menonaktifkan pegawai: ' . $e->getMessage());

    //         return [
    //             'status' => false,
    //             'message' => 'Terjadi kesalahan saat menonaktifkan pegawai.',
    //             'error' => $e->getMessage()
    //         ];
    //     }
    // }

}
    

