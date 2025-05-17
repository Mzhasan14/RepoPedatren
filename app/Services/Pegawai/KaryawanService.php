<?php
namespace App\Services\Pegawai;

use App\Models\Berkas;
use App\Models\Biodata;
use App\Models\JenisBerkas;
use Illuminate\Http\UploadedFile;
use App\Models\Keluarga;
use App\Models\Pegawai\Karyawan;
use App\Models\Pegawai\Pegawai;
use App\Models\WargaPesantren;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KaryawanService
{
    public function getAllKaryawan(Request $request)
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
        return Karyawan::Active()
                        // join pegawai yang hanya berstatus true atau akif
                        ->join('pegawai',function ($join){
                            $join->on('pegawai.id','=','karyawan.pegawai_id')
                                ->where('pegawai.status_aktif','aktif')
                                ->whereNull('pegawai.deleted_at');
                        })
                        ->join('biodata as b','b.id','=','pegawai.biodata_id')
                        // relasi ke golongan jabatan yang hanya berstatus true
                        ->leftJoin('golongan_jabatan as g',function ($join) {
                            $join->on('karyawan.golongan_jabatan_id', '=', 'g.id')
                                ->where('g.status', true);
                        })
                        // join ke warga pesantren terakhir true (NIUP)
                        ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id')) 
                        ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id') 
                        // join berkas pas foto terakhir
                        ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))                            
                        ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                        ->leftJoin('lembaga as l','l.id','=','karyawan.lembaga_id')
                        ->whereNull('karyawan.deleted_at')
                        ->select(
                            'pegawai.biodata_id as biodata_uuid', 
                            'karyawan.id as id',
                            'b.nama',
                            'wp.niup',
                            'b.nik',
                            DB::raw("TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur"),
                            'karyawan.keterangan_jabatan as KeteranganJabatan',
                            'l.nama_lembaga',
                            'karyawan.jabatan',
                            'g.nama_golongan_jabatan as nama_golongan',
                            'b.nama_pendidikan_terakhir as pendidikanTerakhir',
                            DB::raw("DATE_FORMAT(karyawan.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
                            DB::raw("DATE_FORMAT(karyawan.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
                            DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
                            )->groupBy(
                                'pegawai.biodata_id', 
                                'karyawan.id',
                                'b.nama',
                                'b.nik',
                                'wp.niup',
                                'b.tanggal_lahir',
                                'karyawan.keterangan_jabatan',
                                'l.nama_lembaga',
                                'karyawan.jabatan',
                                'g.nama_golongan_jabatan',
                                'b.nama_pendidikan_terakhir',
                                'karyawan.updated_at',
                                'karyawan.created_at',
                            );
                        }
                            catch (\Exception $e) {
                                Log::error('Error fetching data karyawan: ' . $e->getMessage());
                                return response()->json([
                                    "status" => "error",
                                    "message" => "Terjadi kesalahan saat mengambil data karyawan",
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
            "niup" => $item->niup ?? "-",
            "nik" => $item->nik,
            "umur" => $item->umur,
            "Keterangan_jabatan" => $item->KeteranganJabatan,
            "lembaga" => $item->nama_lembaga,
            "jenis_jabatan" => $item->jabatan,
            "golongan" => $item->nama_golongan,
            "pendidikanTerakhir" => $item->pendidikanTerakhir,
            "tgl_update" => $item->tgl_update,
            "tgl_input" => $item->tgl_input,
            "foto_profil" => url($item->foto_profil)
        ]);
    }

// public function createStore(array $input)
// {
//     DB::beginTransaction();

//     try {
//         $isExisting = false;

//         // Cek apakah NIK sudah terdaftar
//         $existingBiodata = Biodata::where('nik', $input['nik'])->first();

//         if ($existingBiodata) {
//             $isExisting = true;

//             // Cek apakah sudah terdaftar sebagai pegawai
//             $existingPegawai = Pegawai::where('biodata_id', $existingBiodata->id)->first();

//             if ($existingPegawai) {
//                 // Aktifkan kembali jika nonaktif
//                 if ($existingPegawai->status_aktif !== 'aktif') {
//                     $existingPegawai->update(['status_aktif' => 'aktif']);
//                     if (method_exists($existingPegawai, 'restore')) {
//                         $existingPegawai->restore();
//                     }
//                 }

//                 // Tidak boleh menambahkan karyawan baru jika masih aktif
//                 if (Karyawan::where('pegawai_id', $existingPegawai->id)
//                     ->where('status_aktif', 'aktif')
//                     ->exists()) {
//                     throw ValidationException::withMessages([
//                         'karyawan' => ['Pegawai untuk data ini sudah ada dan Pegawai ini masih memiliki status karyawan aktif. Tidak dapat menambahkan karyawan baru.'],
//                     ]);
//                 }
//             }

//             // Update biodata lama
//             $existingBiodata->update([
//                 'negara_id' => $input['negara_id'],
//                 'provinsi_id' => $input['provinsi_id'],
//                 'kabupaten_id' => $input['kabupaten_id'],
//                 'kecamatan_id' => $input['kecamatan_id'],
//                 'jalan' => $input['jalan'],
//                 'kode_pos' => $input['kode_pos'],
//                 'nama' => $input['nama'],
//                 'no_passport' => $input['no_passport'],
//                 'tanggal_lahir' => Carbon::parse($input['tanggal_lahir']),
//                 'jenis_kelamin' => $input['jenis_kelamin'],
//                 'tempat_lahir' => $input['tempat_lahir'],
//                 'nik' => $input['nik'],
//                 'no_telepon' => $input['no_telepon'],
//                 'no_telepon_2' => $input['no_telepon_2'],
//                 'email' => $input['email'],
//                 'jenjang_pendidikan_terakhir' => $input['jenjang_pendidikan_terakhir'],
//                 'nama_pendidikan_terakhir' => $input['nama_pendidikan_terakhir'],
//                 'anak_keberapa' => $input['anak_keberapa'],
//                 'dari_saudara' => $input['dari_saudara'],
//                 'tinggal_bersama' => $input['tinggal_bersama'],
//                 'smartcard' => $input['smartcard'],
//                 'status' => 1,
//                 'wafat' => $input['wafat'],
//                 'created_by' => Auth::id(),
//                 'created_at' => now(),
//             ]);
//             $biodata = $existingBiodata;

//         } else {
//             // Insert biodata baru
//             $biodata = Biodata::create([
//                 'id' => Str::uuid(),
//                 'negara_id' => $input['negara_id'],
//                 'provinsi_id' => $input['provinsi_id'],
//                 'kabupaten_id' => $input['kabupaten_id'],
//                 'kecamatan_id' => $input['kecamatan_id'],
//                 'jalan' => $input['jalan'],
//                 'kode_pos' => $input['kode_pos'],
//                 'nama' => $input['nama'],
//                 'no_passport' => $input['no_passport'],
//                 'tanggal_lahir' => Carbon::parse($input['tanggal_lahir']),
//                 'jenis_kelamin' => $input['jenis_kelamin'],
//                 'tempat_lahir' => $input['tempat_lahir'],
//                 'nik' => $input['nik'],
//                 'no_telepon' => $input['no_telepon'],
//                 'no_telepon_2' => $input['no_telepon_2'],
//                 'email' => $input['email'],
//                 'jenjang_pendidikan_terakhir' => $input['jenjang_pendidikan_terakhir'],
//                 'nama_pendidikan_terakhir' => $input['nama_pendidikan_terakhir'],
//                 'anak_keberapa' => $input['anak_keberapa'],
//                 'dari_saudara' => $input['dari_saudara'],
//                 'tinggal_bersama' => $input['tinggal_bersama'],
//                 'smartcard' => $input['smartcard'],
//                 'status' => 1,
//                 'wafat' => $input['wafat'],
//                 'created_by' => Auth::id(),
//                 'created_at' => now(),
//             ]);
//         }

//         // Simpan keluarga jika ada no_kk
//         if (!empty($input['no_kk'])) {
//             Keluarga::updateOrCreate(
//                 ['id_biodata' => $biodata->id],
//                 [
//                     'no_kk' => $input['no_kk'],
//                     'status' => 1,
//                     'created_by' => Auth::id(),
//                 ]
//             );
//         }

//         // Simpan warga pesantren jika ada niup
//         if (!empty($input['niup'])) {
//             WargaPesantren::updateOrCreate(
//                 ['biodata_id' => $biodata->id],
//                 [
//                     'niup' => $input['niup'],
//                     'status' => 1,
//                     'created_by' => Auth::id(),
//                 ]
//             );
//         }

//         // Simpan berkas
//         if (!empty($input['berkas']) && is_array($input['berkas'])) {
//             foreach ($input['berkas'] as $item) {
//                 if (!($item['file_path'] instanceof UploadedFile)) {
//                     throw new \Exception('Berkas tidak valid');
//                 }

//                 $path = $item['file_path']->store('berkas', 'public');

//                 Berkas::create([
//                     'biodata_id' => $biodata->id,
//                     'jenis_berkas_id' => (int) $item['jenis_berkas_id'],
//                     'file_path' => Storage::url($path),
//                     'status' => true,
//                     'created_by' => Auth::id(),
//                 ]);
//             }
//         }

//         // Buat atau ambil pegawai
//         $pegawai = Pegawai::firstOrCreate(
//             ['biodata_id' => $biodata->id],
//             ['status_aktif' => 'aktif', 'created_by' => Auth::id()]
//         );

//         // Simpan karyawan
//         $karyawan = Karyawan::create([
//             'pegawai_id' => $pegawai->id,
//             'golongan_jabatan_id' => $input['golongan_jabatan_id'] ?? null,
//             'lembaga_id' => $input['lembaga_id'] ?? null,
//             'jabatan' => $input['jabatan'] ?? null,
//             'keterangan_jabatan' => $input['keterangan_jabatan'] ?? null,
//             'tanggal_mulai' => $input['tanggal_mulai'] ?? now(),
//             'status_aktif' => 'aktif',
//             'created_by' => Auth::id(),
//         ]);

//         DB::commit();

//         return [
//             'status' => true,
//             'message' => $isExisting
//                 ? 'NIK sudah terdaftar, data biodata diperbarui dan karyawan baru berhasil ditambahkan.'
//                 : 'Karyawan baru berhasil ditambahkan.',
//             'data' => $karyawan,
//         ];

//     } catch (ValidationException $e) {
//         DB::rollBack();
//         throw $e;
//     } catch (\Exception $e) {
//         DB::rollBack();
//         logger()->error('Error creating store: ' . $e->getMessage());

//         return [
//             'status' => false,
//             'message' => 'Gagal menyimpan data karyawan.',
//             'error' => env('APP_DEBUG') ? $e->getMessage() : null,
//         ];
//     }
// }

    // public function createStore(array $input)
    // {
    //     DB::beginTransaction();

    //     try {
    //         // Cek biodata berdasarkan NIK
    //         $existingBiodata = Biodata::where('nik', $input['nik'])->first();

    //         if ($existingBiodata) {
    //             $existingPegawai = Pegawai::where('biodata_id', $existingBiodata->id)->first();

    //             if ($existingPegawai) {
    //                 // Jika pegawai tidak aktif, aktifkan kembali
    //                 if ($existingPegawai->status_aktif !== 'aktif') {
    //                     $existingPegawai->update(['status_aktif' => 'aktif']);
                        
    //                     if (method_exists($existingPegawai, 'restore')) {
    //                         $existingPegawai->restore();
    //                     }
    //                 }

    //                 // Cek karyawan aktif
    //                 if (Karyawan::where('pegawai_id', $existingPegawai->id)
    //                     ->where('status_aktif', 'aktif')
    //                     ->exists()) {
    //                     throw ValidationException::withMessages([
    //                         'karyawan' => ['Pegawai ini masih memiliki status karyawan aktif. Tidak dapat menambahkan karyawan baru.'],
    //                     ]);
    //                 }
    //             }
    //         }

    //         // 1. Simpan Biodata
    //         $biodataData = [
    //             'id' => Str::uuid(),
    //             'negara_id' => $input['negara_id'],
    //             'provinsi_id' => $input['provinsi_id'],
    //             'kabupaten_id' => $input['kabupaten_id'],
    //             'kecamatan_id' => $input['kecamatan_id'],
    //             'jalan' => $input['jalan'],
    //             'kode_pos' => $input['kode_pos'],
    //             'nama' => $input['nama'],
    //             'no_passport' => $input['no_passport'],
    //             'tanggal_lahir' => Carbon::parse($input['tanggal_lahir']),
    //             'jenis_kelamin' => $input['jenis_kelamin'],
    //             'tempat_lahir' => $input['tempat_lahir'],
    //             'nik' => $input['nik'],
    //             'no_telepon' => $input['no_telepon'],
    //             'no_telepon_2' => $input['no_telepon_2'],
    //             'email' => $input['email'],
    //             'jenjang_pendidikan_terakhir' => $input['jenjang_pendidikan_terakhir'],
    //             'nama_pendidikan_terakhir' => $input['nama_pendidikan_terakhir'],
    //             'anak_keberapa'=> $input['anak_keberapa'],
    //             'dari_saudara' => $input['dari_saudara'],
    //             'tinggal_bersama' => $input['tinggal_bersama'],
    //             'smartcard' => $input['smartcard'],
    //             'status' => 1,
    //             'wafat' => $input['wafat'],
    //             'created_by' => Auth::id(),
    //             'created_at' => now(),
    //         ];

    //         $biodata = Biodata::create($biodataData);

    //         // 2. Simpan keluarga jika ada no_kk
    //         if (!empty($input['no_kk'])) {
    //             Keluarga::create([
    //                 'id_biodata' => $biodata->id,
    //                 'no_kk' => $input['no_kk'],
    //                 'status' => 1,
    //                 'created_by' => Auth::id(),
    //             ]);
    //         }

    //         // 3. Simpan warga pesantren jika ada niup
    //         if (!empty($input['niup'])) {
    //             WargaPesantren::create([
    //                 'biodata_id' => $biodata->id,
    //                 'niup' => $input['niup'],
    //                 'status' => 1,
    //                 'created_by' => Auth::id(),
    //             ]);
    //         }

    //         // 4. Simpan berkas
    //         if (!empty($input['berkas']) && is_array($input['berkas'])) {
    //             foreach ($input['berkas'] as $item) {
    //                 if (!($item['file_path'] instanceof UploadedFile)) {
    //                     throw new \Exception('Berkas tidak valid');
    //                 }
                    
    //                 $path = $item['file_path']->store('Karyawan', 'public');
                    
    //                 Berkas::create([
    //                     'biodata_id' => $biodata->id,
    //                     'jenis_berkas_id' => (int) $item['jenis_berkas_id'],
    //                     'file_path' => Storage::url($path),
    //                     'status' => true,
    //                     'created_by' => Auth::id(),
    //                 ]);
    //             }
    //         }

    //         // 5. Simpan Pegawai
    //         $pegawai = Pegawai::create([
    //             'biodata_id' => $biodata->id,
    //             'status_aktif' => 'aktif',
    //             'created_by' => Auth::id(),
    //         ]);

    //         // 6. Simpan Karyawan
    //         $karyawan = Karyawan::create([
    //             'pegawai_id' => $pegawai->id,
    //             'golongan_jabatan_id' => $input['golongan_jabatan_id'] ?? null,
    //             'lembaga_id' => $input['lembaga_id'] ?? null,
    //             'jabatan' => $input['jabatan'] ?? null,
    //             'keterangan_jabatan' => $input['keterangan_jabatan'] ?? null,
    //             'tanggal_mulai' => $input['tanggal_mulai'] ?? now(),
    //             'status_aktif' => 'aktif',
    //             'created_by' => Auth::id(),
    //         ]);

    //         DB::commit();

    //         return $karyawan;

    //     } catch (ValidationException $e) {
    //         DB::rollBack();
    //         throw $e; // Re-throw validation exception untuk ditangani oleh Laravel
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         logger()->error('Error creating store: '.$e->getMessage());
            
    //         return [
    //             'status' => false,
    //             'message' => 'Gagal menyimpan data karyawan.',
    //             'error' => env('APP_DEBUG') ? $e->getMessage() : null,
    //         ];
    //     }
    // }
    // public function createStore(array $input)
    // {
    //     try {
    //         DB::beginTransaction();

    //         // Cek biodata berdasarkan NIK
    //         $existingBiodata = Biodata::where('nik', $input['nik'])->first();

    //         if ($existingBiodata) {
    //             // Cek apakah biodata sudah punya pegawai
    //             $existingPegawai = Pegawai::where('biodata_id', $existingBiodata->id)->first();

    //             if ($existingPegawai) {
    //                 // Jika pegawai tidak aktif, aktifkan kembali
    //                 if ($existingPegawai->status_aktif !== 'aktif') {
    //                     $existingPegawai->status_aktif = 'aktif';

    //                     // Jika model menggunakan soft deletes, reset deleted_at
    //                     if (method_exists($existingPegawai, 'restore')) {
    //                         $existingPegawai->deleted_at = null;
    //                     }

    //                     $existingPegawai->save();
    //                 }

    //                 // Cek apakah pegawai sudah punya karyawan aktif
    //                 $activeKaryawan = Karyawan::where('pegawai_id', $existingPegawai->id)
    //                     ->where('status_aktif', 'aktif')
    //                     ->first();

    //                 if ($activeKaryawan) {
    //                     throw ValidationException::withMessages([
    //                         'karyawan' => ['Pegawai ini masih memiliki status karyawan aktif. Tidak dapat menambahkan karyawan baru.'],
    //                     ]);
    //                 }
    //             }
    //         }

    //         // 🧍 1. Simpan Biodata
    //         $biodata = Biodata::create([
    //             'id' => Str::uuid(),
    //             'negara_id' => $input['negara_id'],
    //             'provinsi_id' => $input['provinsi_id'],
    //             'kabupaten_id' => $input['kabupaten_id'],
    //             'kecamatan_id' => $input['kecamatan_id'],
    //             'jalan' => $input['jalan'],
    //             'kode_pos' => $input['kode_pos'],
    //             'nama' => $input['nama'],
    //             'no_passport' => $input['no_passport'],
    //             'tanggal_lahir' => Carbon::parse($input['tanggal_lahir']),
    //             'jenis_kelamin' => $input['jenis_kelamin'],
    //             'tempat_lahir' => $input['tempat_lahir'],
    //             'nik' => $input['nik'],
    //             'no_telepon' => $input['no_telepon'],
    //             'no_telepon_2' => $input['no_telepon_2'],
    //             'email' => $input['email'],
    //             'jenjang_pendidikan_terakhir' => $input['jenjang_pendidikan_terakhir'],
    //             'nama_pendidikan_terakhir' => $input['nama_pendidikan_terakhir'],
    //             'anak_keberapa'=> $input['anak_keberapa'],
    //             'dari_saudara' => $input['dari_saudara'],
    //             'tinggal_bersama' => $input['tinggal_bersama'],
    //             'smartcard' => $input['smartcard'],
    //             'status' => 1,
    //             'wafat' => $input['wafat'],
    //             'created_by' => Auth::id(),
    //             'created_at' => now(),
    //         ]);

    //         // 👪 2. Simpan data keluarga jika ada
    //         if (!empty($input['no_kk'])) {
    //             Keluarga::create([
    //                 'id_biodata' => $biodata->id,
    //                 'no_kk' => $input['no_kk'],
    //                 'status' => 1,
    //                 'created_by' => Auth::id(),
    //                 'created_at' => now(),
    //             ]);
    //         }

    //         // 🕌 3. Simpan warga pesantren jika ada
    //         if (!empty($input['niup'])) {
    //             WargaPesantren::create([
    //                 'biodata_id' => $biodata->id,
    //                 'niup' => $input['niup'],
    //                 'status' => 1,
    //                 'created_by' => Auth::id(),
    //                 'created_at' => now(),
    //             ]);
    //         }

    //         // 📁 4. Simpan berkas jika ada
    //         if (!empty($input['berkas']) && is_array($input['berkas'])) {
    //             foreach ($input['berkas'] as $item) {
    //                 if (!($item['file_path'] instanceof UploadedFile)) {
    //                     throw new \Exception('Berkas tidak valid');
    //                 }
    //                 $url = Storage::url($item['file_path']->store('Karyawan', 'public'));
    //                 Berkas::create([
    //                     'biodata_id'      => $biodata->id,
    //                     'jenis_berkas_id' => (int) $item['jenis_berkas_id'],
    //                     'file_path'       => $url,
    //                     'status'          => true,
    //                     'created_by'      => Auth::id(),
    //                     'created_at'      => now(),
    //                     'updated_at'      => now(),
    //                 ]);
    //             }
    //         }

    //         // 🧾 5. Simpan data Pegawai
    //         $pegawai = Pegawai::create([
    //             'biodata_id' => $biodata->id,
    //             'status_aktif' => 'aktif',
    //             'created_by' => Auth::id(),
    //             'created_at' => now(),
    //         ]);

    //         // 🧰 6. Simpan data Karyawan
    //         $karyawan = Karyawan::create([
    //             'pegawai_id' => $pegawai->id,
    //             'golongan_jabatan_id' => $input['golongan_jabatan_id'] ?? null,
    //             'lembaga_id' => $input['lembaga_id'] ?? null,
    //             'jabatan' => $input['jabatan'] ?? null,
    //             'keterangan_jabatan' => $input['keterangan_jabatan'] ?? null,
    //             'tanggal_mulai' => $input['tanggal_mulai'] ?? now(),
    //             'status_aktif' => 'aktif',
    //             'created_by' => Auth::id(),
    //         ]);

    //         DB::commit();

    //         return $karyawan;

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return [
    //             'status' => false,
    //             'message' => 'Terjadi kesalahan saat menyimpan data.',
    //             'error' => $e->getMessage(),
    //         ];
    //     }
    // }
}