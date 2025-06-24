<?php

namespace App\Services\Pegawai;

use App\Models\Berkas;
use App\Models\Biodata;
use App\Models\Keluarga;
use App\Models\Pegawai\JadwalPelajaran;
use App\Models\Pegawai\JamPelajaran;
use App\Models\Pegawai\Karyawan;
use App\Models\Pegawai\MataPelajaran;
use App\Models\Pegawai\MateriAjar;
use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\Pengajar;
use App\Models\Pegawai\Pengurus;
use App\Models\Pegawai\WaliKelas;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Lembaga;
use App\Models\WargaPesantren;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PegawaiService
{
    public function basePegawaiQuery(Request $request)
    {
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        $pengajarAktif = DB::table('pengajar')
            ->select('pegawai_id', DB::raw('MAX(id) as id'))
            ->where('status_aktif', 'aktif')
            ->whereNull('tahun_akhir')
            ->groupBy('pegawai_id');

        $karyawanAktif = DB::table('karyawan')
            ->select('pegawai_id', DB::raw('MAX(id) as id'))
            ->where('status_aktif', 'aktif')
            ->whereNull('tanggal_selesai')
            ->groupBy('pegawai_id');

        $pengurusAktif = DB::table('pengurus')
            ->select('pegawai_id', DB::raw('MAX(id) as id'))
            ->where('status_aktif', 'aktif')
            ->whereNull('tanggal_akhir')
            ->groupBy('pegawai_id');

        $query = DB::table('pegawai')
            ->join('biodata as b', 'b.id', 'pegawai.biodata_id')
            ->leftJoinSub($wpLast, 'wl', fn ($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoinSub($pengajarAktif, 'pa', fn ($j) => $j->on('pegawai.id', '=', 'pa.pegawai_id'))
            ->leftJoin('pengajar', 'pengajar.id', '=', 'pa.id')
            ->leftJoinSub($karyawanAktif, 'ka', fn ($j) => $j->on('pegawai.id', '=', 'ka.pegawai_id'))
            ->leftJoin('karyawan', 'karyawan.id', '=', 'ka.id')
            ->leftJoinSub($pengurusAktif, 'pg', fn ($j) => $j->on('pegawai.id', '=', 'pg.pegawai_id'))
            ->leftJoin('pengurus', 'pengurus.id', '=', 'pg.id')
            ->leftJoin('wali_kelas', function ($join) {
                $join->on('pegawai.id', '=', 'wali_kelas.pegawai_id')
                    ->where('wali_kelas.status_aktif', 'aktif')
                    ->whereNull('wali_kelas.periode_akhir');
            })
            ->leftJoinSub($fotoLast, 'fl', fn ($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->whereNull('pegawai.deleted_at')
            ->where('pegawai.status_aktif', 'aktif');

        return $query;
    }

    public function getAllPegawai(Request $request, $fields = null)
    {
        try {
            $query = $this->basePegawaiQuery($request);

            $fields = $fields ?? [
                'pegawai.biodata_id as biodata_uuid',
                'b.nama',
                'wp.niup',
                'pengurus.id as pengurus',
                'karyawan.id as karyawan',
                'pengajar.id as pengajar',
                DB::raw('TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur'),
                DB::raw("TRIM(BOTH ', ' FROM CONCAT_WS(', ', 
                            GROUP_CONCAT(DISTINCT CASE WHEN pengajar.id IS NOT NULL THEN 'Pengajar' END SEPARATOR ', '),
                            GROUP_CONCAT(DISTINCT CASE WHEN karyawan.id IS NOT NULL THEN 'Karyawan' END SEPARATOR ', '),
                            GROUP_CONCAT(DISTINCT CASE WHEN pengurus.id IS NOT NULL THEN 'Pengurus' END SEPARATOR ', ')
                        )) as status"),
                'b.nama_pendidikan_terakhir as pendidikanTerkahir',
                DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil"),
            ];

            return $query
                ->select($fields)
                ->groupBy(
                    'pegawai.biodata_id',
                    'b.nama',
                    'wp.niup',
                    'pengurus.id',
                    'karyawan.id',
                    'pengajar.id',
                    'b.tanggal_lahir',
                    'b.nama_pendidikan_terakhir'
                )
                ->distinct();
        } catch (\Exception $e) {
            Log::error('Error fetching data pegawai: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data pegawai',
                'code' => 500,
            ], 500);
        }
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn ($item) => [
            'biodata_id' => $item->biodata_uuid,
            'nama' => $item->nama,
            'niup' => $item->niup ?? '-',
            'umur' => $item->umur,
            'status' => $item->status,
            'pendidikanTerkahir' => $item->pendidikanTerkahir,
            'pengurus' => $item->pengurus ? true : false,
            'karyawan' => $item->karyawan ? true : false,
            'pengajar' => $item->pengajar ? true : false,
            'foto_profil' => $item->foto_profil
            ? asset($item->foto_profil)
            : null,
        ]);
    }

    public function store(array $input)
    {
        DB::beginTransaction();

        try {
            $isExisting = false;
            $resultData = [];

            // Cek apakah NIK sudah terdaftar
            $existingBiodata = Biodata::where('nik', $input['nik'])->first();

            if ($existingBiodata) {
                $isExisting = true;

                // Cek apakah sudah ada pegawai aktif
                $existingPegawai = Pegawai::where('biodata_id', $existingBiodata->id)->where('status_aktif', 'aktif')->first();

                if ($existingPegawai) {
                    return [
                        'status' => false,
                        'message' => 'Pegawai untuk biodata ini sudah ada dengan status aktif. Silahkan cek kembali di fitur Pegawai.',
                        'data' => ['pegawai' => $existingPegawai],
                    ];
                }

                $pegawaiNonaktif = Pegawai::where('biodata_id', $existingBiodata->id)->latest()->first();

                // Otomatis nonaktifkan role jika masih aktif
                if ($pegawaiNonaktif) {
                    $roleTables = [
                        'karyawan' => Karyawan::class,
                        'pengajar' => Pengajar::class,
                        'pengurus' => Pengurus::class,
                        'wali_kelas' => WaliKelas::class,
                    ];

                    foreach ($roleTables as $key => $model) {
                        if (! empty($input[$key])) {
                            $role = $model::where('pegawai_id', $pegawaiNonaktif->id)
                                ->where('status_aktif', 'aktif')
                                ->first();

                            if ($role) {
                                $dataUpdate = ['status_aktif' => 'tidak aktif'];

                                // Tambah tanggal selesai / akhir sesuai role
                                switch ($key) {
                                    case 'karyawan':
                                        $dataUpdate['tanggal_selesai'] = now();
                                        break;
                                    case 'pengajar':
                                        $dataUpdate['tahun_akhir'] = now();
                                        // Materi ajar juga harus diupdate
                                        MateriAjar::where('pengajar_id', $role->id)
                                            ->where('status_aktif', 'aktif')
                                            ->update([
                                                'status_aktif' => 'tidak aktif',
                                                'tahun_akhir' => now(),
                                                'updated_at' => now(),
                                            ]);
                                        break;
                                    case 'pengurus':
                                        $dataUpdate['tanggal_akhir'] = now();
                                        break;
                                    case 'wali_kelas':
                                        $dataUpdate['periode_akhir'] = now();
                                        break;
                                }

                                $role->update($dataUpdate);
                            }
                        }
                    }
                }

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

            // Simpan keluarga jika ada
            if (! empty($input['no_kk'])) {
                Keluarga::create([
                    'id_biodata' => $biodata->id,
                    'no_kk' => $input['no_kk'],
                    'status' => 1,
                    'created_by' => Auth::id(),
                ]);
            }

            // Simpan warga pesantren jika ada
            if (! empty($input['niup'])) {
                WargaPesantren::create([
                    'biodata_id' => $biodata->id,
                    'niup' => $input['niup'],
                    'status' => 1,
                    'created_by' => Auth::id(),
                ]);
            }

            // Simpan berkas
            if (! empty($input['berkas']) && is_array($input['berkas'])) {
                foreach ($input['berkas'] as $item) {
                    if (! ($item['file_path'] instanceof UploadedFile)) {
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

            // Buat pegawai baru
            $pegawai = Pegawai::create([
                'biodata_id' => $biodata->id,
                'status_aktif' => 'aktif',
                'created_by' => Auth::id(),
            ]);

            // Simpan karyawan
            if (! empty($input['karyawan'])) {
                $resultData['karyawan'] = Karyawan::create([
                    'pegawai_id' => $pegawai->id,
                    'golongan_jabatan_id' => $input['golongan_jabatan_id_karyawan'] ?? null,
                    'lembaga_id' => $input['lembaga_id_karyawan'] ?? null,
                    'jabatan' => $input['jabatan_karyawan'] ?? null,
                    'keterangan_jabatan' => $input['keterangan_jabatan_karyawan'] ?? null,
                    'tanggal_mulai' => $input['tanggal_mulai_karyawan'] ?? now(),
                    'status_aktif' => 'aktif',
                    'created_by' => Auth::id(),
                ]);
            }

            // Simpan pengajar dan materi ajar
            if (! empty($input['pengajar'])) {
                $pengajar = Pengajar::create([
                    'pegawai_id'   => $pegawai->id,
                    'golongan_id'  => $input['golongan_id_pengajar'] ?? null,
                    'lembaga_id'   => $input['lembaga_id_pengajar'] ?? null,
                    'jabatan'      => $input['jabatan_pengajar'] ?? null,
                    'tahun_masuk'  => $input['tanggal_mulai_pengajar'] ?? now(),
                    'status_aktif' => 'aktif',
                    'created_by'   => Auth::id(),
                ]);

                $resultData['pengajar'] = $pengajar;

                // Validasi & simpan mata pelajaran
                foreach ($input['mata_pelajaran'] ?? [] as $mapel) {
                    // Cek apakah kode_mapel sudah aktif digunakan
                    $mapelAktif = MataPelajaran::where('kode_mapel', $mapel['kode_mapel'])
                        ->where('status', true)
                        ->first();

                    if ($mapelAktif) {
                        throw new \Exception('Kode mata pelajaran ' . $mapel['kode_mapel'] . ' sudah digunakan untuk mata pelajaran "' . $mapelAktif->nama_mapel . '".');
                    }

                    MataPelajaran::create([
                        'kode_mapel'   => $mapel['kode_mapel'],
                        'nama_mapel'   => $mapel['nama_mapel'] ?? '(tidak diketahui)',
                        'pengajar_id'  => $pengajar->id,
                        'status'       => true,
                        'created_by'   => Auth::id(),
                    ]);
                }
            }

            // Simpan pengurus
            if (! empty($input['pengurus'])) {
                $resultData['pengurus'] = Pengurus::create([
                    'pegawai_id' => $pegawai->id,
                    'golongan_jabatan_id' => $input['golongan_jabatan_id_pengurus'] ?? null,
                    'jabatan' => $input['jabatan_pengurus'] ?? null,
                    'satuan_kerja' => $input['satuan_kerja_pengurus'] ?? null,
                    'keterangan_jabatan' => $input['keterangan_jabatan_pengurus'] ?? null,
                    'tanggal_mulai' => $input['tanggal_mulai_pengurus'] ?? now(),
                    'status_aktif' => 'aktif',
                    'created_by' => Auth::id(),
                ]);
            }

            // Simpan wali kelas
            if (! empty($input['wali_kelas'])) {
                $resultData['wali_kelas'] = WaliKelas::create([
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
            }

            DB::commit();

            return [
                'status' => true,
                'message' => $isExisting
                    ? 'Pegawai baru berhasil ditambahkan untuk biodata yang sudah terdaftar.'
                    : 'Pegawai baru berhasil ditambahkan.',
                'data' => array_merge(['pegawai' => $pegawai], $resultData),
            ];

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error Membuat Data pegawai: '.$e->getMessage());

            return [
                'status' => false,
                'message' => 'Gagal menyimpan data pegawai.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null,
            ];
        }
    }
    public function getExportPegawaiQuery($fields, $request)
    {
        $query = $this->basePegawaiQuery($request);

        if (in_array('no_kk', $fields)) {
            $query->leftJoin('keluarga as k', 'k.id_biodata', '=', 'b.id');
        }

        if (in_array('niup', $fields)) {
            $query->leftJoin('warga_pesantren as wp2', 'b.id', '=', 'wp2.biodata_id');
        }

        if (in_array('status_aktif', $fields)) {
            $query->leftJoin('pengajar as pr', 'pegawai.id', '=', 'pr.pegawai_id');
            $query->leftJoin('karyawan as kr', 'pegawai.id', '=', 'kr.pegawai_id');
            $query->leftJoin('pengurus as ps', 'pegawai.id', '=', 'ps.pegawai_id');
        }

        if (in_array('alamat', $fields)) {
            $query->leftJoin('kecamatan as kc2', 'b.kecamatan_id', '=', 'kc2.id');
            $query->leftJoin('kabupaten as kb2', 'b.kabupaten_id', '=', 'kb2.id');
            $query->leftJoin('provinsi as pv2', 'b.provinsi_id', '=', 'pv2.id');
            $query->leftJoin('negara as ng2', 'b.negara_id', '=', 'ng2.id');
        }

        $select = [];
        foreach ($fields as $field) {
            switch ($field) {
                case 'nama_lengkap':
                    $select[] = 'b.nama as nama_lengkap';
                    break;
                case 'nik':
                    $select[] = DB::raw('COALESCE(b.nik, b.no_passport) as nik');
                    break;
                case 'niup':
                    $select[] = 'wp2.niup';
                    break;
                case 'tempat_tanggal_lahir':
                    $select[] = 'b.tempat_lahir';
                    $select[] = 'b.tanggal_lahir';
                    break;
                case 'jenis_kelamin':
                    $select[] = 'b.jenis_kelamin';
                    break;
                case 'no_kk':
                    $select[] = 'k.no_kk';
                    break;
                case 'alamat':
                    $select[] = 'b.jalan';
                    $select[] = 'kc2.nama_kecamatan as kecamatan';
                    $select[] = 'kb2.nama_kabupaten as kabupaten';
                    $select[] = 'pv2.nama_provinsi as provinsi';
                    $select[] = 'ng2.nama_negara as negara';
                    break;
                case 'status_aktif':
                    $select[] = DB::raw("
                        CASE
                            WHEN pr.status_aktif = 'aktif' THEN 'Pengajar'
                            WHEN kr.status_aktif = 'aktif' THEN 'Karyawan'
                            WHEN ps.status_aktif = 'aktif' THEN 'Pengurus'
                            ELSE '-'
                        END as status_aktif
                    ");
                    break;
                case 'pendidikan_terakhir':
                    $select[] = 'b.jenjang_pendidikan_terakhir';
                    $select[] = 'b.nama_pendidikan_terakhir';
                    break;
            }
        }

        return $query->select($select);
    }

    public function formatDataExport($results, $fields, $addNumber = false)
    {
        return collect($results)->values()->map(function ($item, $idx) use ($fields, $addNumber) {
            $data = [];
            if ($addNumber) {
                $data['No'] = $idx + 1;
            }

            $itemArr = array_values((array) $item);
            $i = 0;

            foreach ($fields as $field) {
                switch ($field) {
                    case 'nama_lengkap':
                        $data['Nama Lengkap'] = $itemArr[$i++] ?? '';
                        break;
                    case 'nik':
                        $data['NIK'] = ' ' . ($itemArr[$i++] ?? '');
                        break;
                    case 'niup':
                        $data['NIUP'] = ' ' . ($itemArr[$i++] ?? '');
                        break;
                    case 'tempat_tanggal_lahir':
                        $data['Tempat Lahir'] = $itemArr[$i++] ?? '';
                        $tgl = $itemArr[$i++] ?? '';
                        $data['Tanggal Lahir'] = $tgl ? \Carbon\Carbon::parse($tgl)->translatedFormat('d F Y') : '';
                        break;
                    case 'jenis_kelamin':
                        $jk = $itemArr[$i++] ?? '';
                        $data['Jenis Kelamin'] = strtolower($jk) === 'l' ? 'Laki-laki' : (strtolower($jk) === 'p' ? 'Perempuan' : '');
                        break;
                    case 'no_kk':
                        $data['No. KK'] = ' ' . ($itemArr[$i++] ?? '');
                        break;
                    case 'alamat':
                        $data['Jalan'] = $itemArr[$i++] ?? '';
                        $data['Kecamatan'] = $itemArr[$i++] ?? '';
                        $data['Kabupaten'] = $itemArr[$i++] ?? '';
                        $data['Provinsi'] = $itemArr[$i++] ?? '';
                        $data['Negara'] = $itemArr[$i++] ?? '';
                        break;
                    case 'status_aktif':
                        $data['Status Aktif'] = $itemArr[$i++] ?? '';
                        break;
                    case 'pendidikan_terakhir':
                        $data['Jenjang Pendidikan Terakhir'] = $itemArr[$i++] ?? '';
                        $data['Nama Pendidikan Terakhir'] = $itemArr[$i++] ?? '';
                        break;
                    default:
                        $data[ucwords(str_replace('_', ' ', $field))] = $itemArr[$i++] ?? '';
                }
            }

            return $data;
        });
    }

    public function getFieldExportHeadings($fields, $addNumber = false)
    {
        $labels = [
            'nama_lengkap' => 'Nama Lengkap',
            'nik' => 'NIK / Passport',
            'no_kk' => 'No. KK',
            'niup' => 'NIUP',
            'jenis_kelamin' => 'Jenis Kelamin',
            'status_aktif' => 'Status Kepegawaian',
        ];

        $headings = [];
        if ($addNumber) {
            $headings[] = 'No';
        }

        foreach ($fields as $field) {
            switch ($field) {
                case 'tempat_tanggal_lahir':
                    $headings[] = 'Tempat Lahir';
                    $headings[] = 'Tanggal Lahir';
                    break;
                case 'alamat':
                    $headings[] = 'Jalan';
                    $headings[] = 'Kecamatan';
                    $headings[] = 'Kabupaten';
                    $headings[] = 'Provinsi';
                    $headings[] = 'Negara';
                    break;
                case 'pendidikan_terakhir':
                    $headings[] = 'Jenjang Pendidikan Terakhir';
                    $headings[] = 'Nama Pendidikan Terakhir';
                    break;
                default:
                    $headings[] = $labels[$field] ?? ucwords(str_replace('_', ' ', $field));
            }
        }

        return $headings;
    }
}
