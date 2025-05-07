<?php

namespace App\Services\Pegawai;

use App\Models\Alamat\Kabupaten;
use App\Models\Alamat\Kecamatan;
use App\Models\Alamat\Negara;
use App\Models\Alamat\Provinsi;
use App\Models\Biodata;
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
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Lembaga;
use App\Models\Pendidikan\Rombel;
use App\Models\WargaPesantren;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


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
            return Pegawai::Active()
                            ->join('biodata as b','b.id','pegawai.biodata_id')
                            // join warga pesantren terakhir true (NIUP)
                            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
                            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id') 
                            // join pengajar yang hanya berstatus aktif                    
                            ->leftJoin('pengajar', function($join) {
                                $join->on('pengajar.pegawai_id', '=', 'pegawai.id')
                                     ->where('pengajar.status_aktif', 'aktif');
                            })
                            // join pengurus yang hanya berstatus aktif
                            ->leftJoin('pengurus', function($join) {
                                $join->on('pengurus.pegawai_id', '=', 'pegawai.id')
                                     ->where('pengurus.status_aktif', 'aktif');
                            })
                            // join karyawan yang hanya berstatus aktif
                            ->leftJoin('karyawan', function($join) {
                                $join->on('karyawan.pegawai_id', '=', 'pegawai.id')
                                     ->where('karyawan.status_aktif', 'aktif');
                            })
                            
                            // join berkas pas foto terakhir
                            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
                            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                            ->whereNull('pegawai.deleted_at')
                            ->select(
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
            "id" => $item->id,
            "nama" => $item->nama,
            "niup" => $item->niup ?? '-',
            "umur" => $item->umur,
            "status" => $item->status,
            "pendidikanTerkahir" => $item->pendidikanTerkahir,
            "pengurus" => $item->pengurus ? true : false,
            "karyawan" => $item->karyawan ? true : false,
            "pengajar" => $item->pengajar ? true : false,
            "foto_profil" => url($item->foto_profil)
        ]);
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $negara = Negara::create([
                'nama_negara' => $data['negara'],
                'created_by' => 1,
                'status' => 1,
                'created_at' => Carbon::now(),
            ]);
            $provinsi = Provinsi::create([
                'negara_id' => $negara->id,
                'nama_provinsi' => $data['provinsi'],
                'created_by' => 1,
                'status' => 1,
                'created_at' => Carbon::now(),
            ]);
            $kabupaten = Kabupaten::create([
                'provinsi_id' => $provinsi->id,
                'nama_kabupaten' => $data['kabupaten'],
                'created_by' => 1,
                'status' => 1,
                'created_at' => Carbon::now(),
            ]);
            $kecamatan = Kecamatan::create([
                'kabupaten_id' => $kabupaten->id,
                'nama_kecamatan' => $data['kecamatan'],
                'created_by' => 1,
                'status' => 1,
                'created_at' => Carbon::now(),
            ]);
            //  Biodata
            $biodata = Biodata::create([
                'negara_id' => $negara->id,
                'provinsi_id' => $provinsi->id,
                'kabupaten_id' => $kabupaten->id,
                'kecamatan_id' => $kecamatan->id,
                'jalan' => $data['jalan'],
                'kode_pos' => $data['kode_pos'],
                'nama' => $data['nama'],
                'no_passport' => $data['no_passport'], // diperbaiki
                'tanggal_lahir' => Carbon::parse($data['tanggal_lahir']),
                'jenis_kelamin' => $data['jenis_kelamin'],
                'tempat_lahir' => $data['tempat_lahir'],
                'nik' => $data['nik'],
                'no_telepon' => $data['no_telepon'],
                'no_telepon_2' => $data['no_telepon_2'],
                'email' => $data['email'],
                'jenjang_pendidikan_terakhir' => $data['jenjang_pendidikan_terakhir'],
                'nama_pendidikan_terakhir' => $data['nama_pendidikan_terakhir'],
                'anak_keberapa'=> $data['anak_keberapa'],
                'dari_saudara' => $data['dari_saudara'],
                'tinggal_bersama' => $data['tinggal_bersama'],
                'smartcard' => $data['smartcard'],
                'status' => 1,
                'wafat' => $data['wafat'],
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ]);

            if (!empty($data['no_kk'])) {
                Keluarga::create([
                    'id_biodata' => $biodata->id,
                    'no_kk' => $data['no_kk'],
                    'status' => 1,
                    'created_by' => 1,
                    'created_at' => now(),
                ]);
            }
        
            if (!empty($data['niup'])) {
                 WargaPesantren::create([
                    'biodata_id' => $biodata->id,
                    'niup' => $data['niup'],
                    'status' => 1,
                    'created_by' => 1,
                    'created_at' => now(),
                ]);
            }
    
            // 2. Opsional Lembaga
            $lembaga = null;
            if (!empty($data['nama_lembaga_pegawai'])) {
                $lembaga = Lembaga::create([
                    'nama_lembaga' => $data['nama_lembaga_pegawai'],
                    'created_by' => 1,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }
    
            // 3. Opsional Jurusan
            $jurusan = null;
            if (!empty($data['nama_jurusan']) && $lembaga) {
                $jurusan = Jurusan::create([
                    'lembaga_id' => $lembaga->id,
                    'nama_jurusan' => $data['nama_jurusan'],
                    'created_by' => 1,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }
    
            // 4. Opsional Kelas
            $kelas = null;
            if (!empty($data['nama_kelas']) && $jurusan) {
                $kelas = Kelas::create([
                    'jurusan_id' => $jurusan->id,
                    'nama_kelas' => $data['nama_kelas'],
                    'created_by' => 1,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }
    
            // 5. Opsional Rombel
            $rombel = null;
            if (!empty($data['nama_rombel']) && $kelas) {
                $rombel = Rombel::create([
                    'kelas_id' => $kelas->id,
                    'nama_rombel' => $data['nama_rombel'],
                    'gender_rombel' => $data['gender_rombel'], // WAJIB, karena nullable(false)
                    'created_by' => 1,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }
    
            // 6. Pegawai
            $pegawai = Pegawai::create([
                'id' => Str::uuid(),
                'biodata_id' => $biodata->id,
                'lembaga_id' => $lembaga?->id,
                'kelas_id' => $kelas?->id,
                'jurusan_id' => $jurusan?->id, // diperbaiki dari 'jurusan' ke 'jurusan_id'
                'rombel_id' => $rombel?->id,
                'status_aktif' => $data['status_aktif'],
                'created_by' => 1,
                'created_at' => Carbon::now(),
            ]);

            $GolonganJabatan = null;
            if(!empty($data['nama_golongan_jabatan_karyawan'])){
                $GolonganJabatan = GolonganJabatan::create([
                    'nama_golongan_jabatan' => $data['nama_golongan_jabatan_karyawan'],
                    'created_by' => 1,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }

            $lembagaKaryawan = null;
            if(!empty($data['nama_lembaga_karyawan'])){
                $lembagaKaryawan = Lembaga::create([
                    'nama_lembaga' => $data['nama_lembaga_karyawan'],
                    'created_by' => 1,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }

            $karyawan = null;
            if(!empty($data['karyawan'])){
                $karyawan = Karyawan::create([
                    'id' => Str::uuid(),
                    'pegawai_id' => $pegawai->id,
                    'golongan_jabatan_id' => $GolonganJabatan?->id, //Bisa Null
                    'lembaga_id' => $lembagaKaryawan?->id, //Bisa Null
                    'jabatan' => $data['jabatan'],
                    'status_aktif' => $data['status_aktif'],
                    'created_by' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }

            if(!empty($data['keterangan_jabatan'])){
                RiwayatJabatanKaryawan::create([
                    'karyawan_id' => $karyawan->id,
                    'keterangan_jabatan' => $data['keterangan_jabatan'],
                    'tanggal_mulai' => Carbon::parse($data['tanggal_mulai']),
                    'status' => 1,
                    'created_by' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }

            $kategoriGolongan = null;
            if(!empty($data['nama_kategori_golongan'])){
                $kategoriGolongan = KategoriGolongan::create([
                    'nama_kategori_golongan' => $data['nama_kategori_golongan'],
                    'created_by' => 1,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }

            $golonganPengajar = null;
            if(!empty($data['nama_golongan'])){
                $golonganPengajar = Golongan::create([
                    'nama_golongan' => $data['nama_golongan'],
                    'kategori_golongan_id' => $kategoriGolongan->id,
                    'created_by' => 1,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }
            $lembagaPengurus = null;
            if(!empty($data['nama_lembaga_pengurus'])){
                $lembagaPengurus = Lembaga::create([
                    'nama_lembaga' => $data['nama_lembaga_pengurus'],
                    'created_by' => 1,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }

            $pengajar = null;
            if(!empty($data['pengajar'])){
                $pengajar = Pengajar::create([
                    'id' => Str::uuid(),
                    'pegawai_id' => $pegawai->id,
                    'lembaga_id' => $lembagaPengurus?->id, //Bisa Null
                    'golongan_id' => $golonganPengajar?->id, //Bisa Null
                    'jabatan' => $data['jabatan'],
                    'status_aktif' => $data['status_aktif'],
                    'tahun_masuk' => Carbon::parse($data['tahun_masuk']),
                    'created_by' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }

            if (!empty($data['materi_ajar']) && is_array($data['materi_ajar'])) {
                foreach ($data['materi_ajar'] as $materi) {
                    MateriAjar::create([
                        'pengajar_id' => $pengajar->id,
                        'nama_materi' => $materi['nama_materi'],
                        'jumlah_menit' => $materi['jumlah_menit'],
                        'created_by' => 1,
                        'status' => 1,
                        'created_at' => now(),
                    ]);
                }
            }
            
            

            $GolonganJabatanPengurus = null;
            if(!empty($data['nama_golongan_jabatan_pengurus'])){
                $GolonganJabatanPengurus = GolonganJabatan::create([
                    'nama_golongan_jabatan' => $data['nama_golongan_jabatan_pengurus'],
                    'created_by' => 1,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }

            if(!empty($data['pengurus'])){
                Pengurus::create([
                    'id' => Str::uuid(),
                    'pegawai_id' => $pegawai->id,
                    'golongan_jabatan_id' => $GolonganJabatanPengurus?->id, //Bisa Null
                    'jabatan' => $data['jabatan'],
                    'satuan_kerja' => $data['satuan_kerja'],
                    'keterangan_jabatan' => $data['keterangan_jabatan'],
                    'tanggal_mulai' => Carbon::parse($data['tanggal_mulai']),
                    'status_aktif' => $data['status_aktif'],
                    'created_by' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }

            DB::commit();

            return $pegawai;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating pegawai: ' . $e->getMessage());
            throw $e;  // Melemparkan exception agar ditangani di controller
        }
    }
}
    

