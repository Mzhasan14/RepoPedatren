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
use App\Models\Pegawai\WaliKelas;
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
                            ->select(
                                'pegawai.biodata_id as biodata_uuid',
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
            "id" => $item->biodata_uuid,
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
                'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                'status' => 1,
                'created_at' => Carbon::now(),
            ]);
            $provinsi = Provinsi::create([
                'negara_id' => $negara->id,
                'nama_provinsi' => $data['provinsi'],
                'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                'status' => 1,
                'created_at' => Carbon::now(),
            ]);
            $kabupaten = Kabupaten::create([
                'provinsi_id' => $provinsi->id,
                'nama_kabupaten' => $data['kabupaten'],
                'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                'status' => 1,
                'created_at' => Carbon::now(),
            ]);
            $kecamatan = Kecamatan::create([
                'kabupaten_id' => $kabupaten->id,
                'nama_kecamatan' => $data['kecamatan'],
                'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                'status' => 1,
                'created_at' => Carbon::now(),
            ]);
            //  Biodata
            $biodata = Biodata::create([
                'id' => Str::uuid(),
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
                'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                'created_at' => Carbon::now(),
            ]);

            if (!empty($data['no_kk'])) {
                Keluarga::create([
                    'id_biodata' => $biodata->id,
                    'no_kk' => $data['no_kk'],
                    'status' => 1,
                    'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                    'created_at' => now(),
                ]);
            }
        
            if (!empty($data['niup'])) {
                 WargaPesantren::create([
                    'biodata_id' => $biodata->id,
                    'niup' => $data['niup'],
                    'status' => 1,
                    'created_by' => 1,  // Ganti dengan ID pengguna yang sesuai login
                    'created_at' => now(),
                ]);
            }

            $pegawai = Pegawai::create([
                'biodata_id' => $biodata->id,
                'status_aktif' => 'aktif',
                'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                'created_at' => Carbon::now(),
            ]);

            $GolonganJabatan = null;
            if(!empty($data['nama_golongan_jabatan_karyawan'])){
                $GolonganJabatan = GolonganJabatan::create([
                    'nama_golongan_jabatan' => $data['nama_golongan_jabatan_karyawan'],
                    'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }

            $lembagaKaryawan = null;
            if(!empty($data['nama_lembaga_karyawan'])){
                $lembagaKaryawan = Lembaga::create([
                    'nama_lembaga' => $data['nama_lembaga_karyawan'],
                    'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }

            $karyawan = null;
            if(!empty($data['karyawan'])){
                 Karyawan::create([
                    'pegawai_id' => $pegawai->id,
                    'golongan_jabatan_id' => $GolonganJabatan?->id, //Bisa Null
                    'lembaga_id' => $lembagaKaryawan?->id, //Bisa Null
                    'jabatan' => $data['jabatan_karyawan'],
                    'keterangan_jabatan' => $data['keterangan_jabatan_karyawan'],
                    'tanggal_mulai' => Carbon::parse($data['tanggal_mulai_karyawan']),
                    'status_aktif' => 'aktif',
                    'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                    'created_at' => Carbon::now(),
                ]);
            }

            $kategoriGolongan = null;
            if(!empty($data['nama_kategori_golongan'])){
                $kategoriGolongan = KategoriGolongan::create([
                    'nama_kategori_golongan' => $data['nama_kategori_golongan'],
                    'created_by' => 1,
                    'status' => 1, // Ganti dengan ID pengguna yang sesuai login
                    'created_at' => Carbon::now(),
                ]);
            }

            $golonganPengajar = null;
            if(!empty($data['nama_golongan'])){
                $golonganPengajar = Golongan::create([
                    'nama_golongan' => $data['nama_golongan'],
                    'kategori_golongan_id' => $kategoriGolongan->id,
                    'created_by' => 1,
                    'status' => 1, // Ganti dengan ID pengguna yang sesuai login
                    'created_at' => Carbon::now(),
                ]);
            }
            $lembagaPengajar = null;
            if(!empty($data['nama_lembaga_pengajar'])){
                $lembagaPengajar = Lembaga::create([
                    'nama_lembaga' => $data['nama_lembaga_pengajar'],
                    'created_by' => 1,
                    'status' => 1, // Ganti dengan ID pengguna yang sesuai login
                    'created_at' => Carbon::now(),
                ]);
            }

            $pengajar = null;
            if(!empty($data['pengajar'])){
                $pengajar = Pengajar::create([
                    'pegawai_id' => $pegawai->id,
                    'lembaga_id' => $lembagaPengajar?->id, //Bisa Null
                    'golongan_id' => $golonganPengajar?->id, //Bisa Null
                    'jabatan' => $data['jabatan_pengajar'],
                    'status_aktif' => 'aktif',
                    'tahun_masuk' => Carbon::parse($data['tahun_masuk_pengajar']),
                    'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                    'created_at' => Carbon::now(),
                ]);
            }

            if (!empty($data['materi_ajar']) && is_array($data['materi_ajar'])) {
                foreach ($data['materi_ajar'] as $materi) {
                    MateriAjar::create([
                        'pengajar_id' => $pengajar->id,
                        'nama_materi' => $materi['nama_materi'],
                        'jumlah_menit' => $materi['jumlah_menit'],
                        'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                        'status' => 1,
                        'created_at' => now(),
                    ]);
                }
            }
            
            

            $GolonganJabatanPengurus = null;
            if(!empty($data['nama_golongan_jabatan_pengurus'])){
                $GolonganJabatanPengurus = GolonganJabatan::create([
                    'nama_golongan_jabatan' => $data['nama_golongan_jabatan_pengurus'],
                    'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }

            $pengurus = null;
            if(!empty($data['pengurus'])){
                Pengurus::create([
                    'pegawai_id' => $pegawai->id,
                    'golongan_jabatan_id' => $GolonganJabatanPengurus?->id, //Bisa Null
                    'jabatan' => $data['jabatan_pengurus'],
                    'satuan_kerja' => $data['satuan_kerja_pengurus'],
                    'keterangan_jabatan' => $data['keterangan_jabatan_pengurus'],
                    'tanggal_mulai' => Carbon::parse($data['tanggal_mulai_pengurus']),
                    'status_aktif' => 'aktif',
                    'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                    'created_at' => Carbon::now(),
                ]);
            }


            $lembagaWaliKelas = null;
            if(!empty($data['nama_lembaga_wali_kelas'])){
                $lembagaWaliKelas = Lembaga::create([
                    'nama_lembaga' => $data['nama_lembaga_wali_kelas'],
                    'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }
            $jurusan = null;
            if(!empty($data['nama_jurusan_wali_kelas'])){
                $jurusan = Jurusan::create([
                    'lembaga_id' => $lembagaWaliKelas?->id, //Bisa Null
                    'nama_jurusan' => $data['nama_jurusan_wali_kelas'],
                    'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }
            $kelas = null;
            if(!empty($data['nama_kelas_wali_kelas'])){
                $kelas = Kelas::create([
                    'jurusan_id' => $jurusan?->id, //Bisa Null
                    'nama_kelas' => $data['nama_kelas_wali_kelas'],
                    'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }
            $rombel = null;
            if(!empty($data['nama_rombel_wali_kelas'])){
                $rombel = Rombel::create([
                    'kelas_id' => $kelas?->id, //Bisa Null
                    'nama_rombel' => $data['nama_rombel_wali_kelas'],
                    'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
                    'status' => 1,
                    'created_at' => Carbon::now(),
                ]);
            }

            $walikelas = null;
            if(!empty($data['wali_kelas'])){
                WaliKelas::create([
                    'pegawai_id' => $pegawai->id,
                    'lembaga_id' => $lembagaWaliKelas?->id, //Bisa Null
                    'jurusan_id' => $jurusan?->id, //Bisa Null
                    'kelas_id' => $kelas?->id, //Bisa Null
                    'rombel_id' => $rombel?->id, //Bisa Null
                    'jumlah_murid' => $data['jumlah_murid_wali_kelas'],
                    'status_aktif' => 'aktif',
                    'created_by' => 1, // Ganti dengan ID pengguna yang sesuai login
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
    

