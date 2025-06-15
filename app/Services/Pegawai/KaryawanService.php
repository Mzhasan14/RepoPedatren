<?php

namespace App\Services\Pegawai;

use App\Models\Berkas;
use App\Models\Biodata;
use App\Models\Pegawai\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KaryawanService
{
    public function baseKaryawanQuery(Request $request)
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

        return DB::table('karyawan')
            ->join('pegawai', function ($join) {
                $join->on('pegawai.id', '=', 'karyawan.pegawai_id')
                    ->where('pegawai.status_aktif', 'aktif')
                    ->whereNull('pegawai.deleted_at');
            })
            ->join('biodata as b', 'b.id', '=', 'pegawai.biodata_id')
            ->leftJoin('golongan_jabatan as g', function ($join) {
                $join->on('karyawan.golongan_jabatan_id', '=', 'g.id')
                    ->where('g.status', true);
            })
            ->leftJoinSub($wpLast, 'wl', fn ($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoinSub($fotoLast, 'fl', fn ($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoin('lembaga as l', 'l.id', '=', 'karyawan.lembaga_id')
            ->whereNull('karyawan.deleted_at')
            ->where('karyawan.status_aktif', 'aktif');
    }
    public function getAllKaryawan(Request $request)
    {
        try {
            $query = $this->baseKaryawanQuery($request);

            $fields = [
                'pegawai.biodata_id as biodata_uuid',
                'b.nama',
                'wp.niup',
                'b.nik',
                DB::raw('TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur'),
                'karyawan.keterangan_jabatan as KeteranganJabatan',
                'l.nama_lembaga',
                'karyawan.jabatan',
                'g.nama_golongan_jabatan as nama_golongan',
                'b.nama_pendidikan_terakhir as pendidikanTerakhir',
                DB::raw("DATE_FORMAT(karyawan.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
                DB::raw("DATE_FORMAT(karyawan.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
                DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
            ];

            return $query->select($fields)->groupBy(
                'pegawai.biodata_id',
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
                'karyawan.created_at'
            );
        } catch (\Exception $e) {
            Log::error('Error fetching data karyawan: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data karyawan',
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
            'nik' => $item->nik,
            'umur' => $item->umur,
            'Keterangan_jabatan' => $item->KeteranganJabatan,
            'lembaga' => $item->nama_lembaga,
            'jenis_jabatan' => $item->jabatan,
            'golongan' => $item->nama_golongan,
            'pendidikanTerakhir' => $item->pendidikanTerakhir,
            'tgl_update' => $item->tgl_update,
            'tgl_input' => $item->tgl_input,
            'foto_profil' => url($item->foto_profil),
        ]);
    }
    public function getExportKaryawanQuery($fields, $request)
    {
        $query = $this->baseKaryawanQuery($request);

        if (in_array('no_kk', $fields)) {
            $query->leftJoin('keluarga as k', 'k.id_biodata', '=', 'b.id');
        }

        if (in_array('alamat', $fields)) {
            $query->leftJoin('kecamatan as kc2', 'b.kecamatan_id', '=', 'kc2.id')
                ->leftJoin('kabupaten as kb2', 'b.kabupaten_id', '=', 'kb2.id')
                ->leftJoin('provinsi as pv2', 'b.provinsi_id', '=', 'pv2.id')
                ->leftJoin('negara as ng2', 'b.negara_id', '=', 'ng2.id');
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
                    $select[] = 'wp.niup';
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
                case 'pendidikan_terakhir':
                    $select[] = 'b.jenjang_pendidikan_terakhir as jenjang_pendidikan_terakhir';
                    $select[] = 'b.nama_pendidikan_terakhir as nama_pendidikan_terakhir';
                    break;
                case 'email':
                case 'no_telepon':
                    $select[] = "b.$field";
                    break;
                case 'lembaga':
                    $select[] = 'l.nama_lembaga';
                    break;
                case 'golongan_jabatan':
                    $select[] = 'g.nama_golongan_jabatan';
                    break;
                case 'jabatan':
                    $select[] = 'karyawan.jabatan';
                    break;
                case 'keterangan_jabatan':
                    $select[] = 'karyawan.keterangan_jabatan';
                    break;
                case 'tanggal_mulai':
                    $select[] = 'karyawan.tanggal_mulai';
                    break;
                case 'tanggal_selesai':
                    $select[] = 'karyawan.tanggal_selesai';
                    break;
                case 'status_aktif':
                    $select[] = DB::raw("CASE WHEN pegawai.status_aktif = 'aktif' THEN 'Aktif' ELSE 'Nonaktif' END as status_aktif");
                    break;
            }
        }

        return $query->select($select)->distinct();

    }
    public function formatDataExport($data, $fields, $addNumber = true)
    {
        return $data->map(function ($row, $index) use ($fields, $addNumber) {
            $formatted = [];

            if ($addNumber) {
                $formatted[] = $index + 1;
            }

            foreach ($fields as $field) {
                switch ($field) {
                    case 'tanggal_mulai':
                    case 'tanggal_selesai':
                        $formatted[] = optional($row->{$field})->format('d-m-Y');
                        break;

                    case 'tempat_tanggal_lahir':
                        $tempat  = $row->tempat_lahir ?? '-';
                        $tanggal = $row->tanggal_lahir ? Carbon::parse($row->tanggal_lahir)->format('d-m-Y') : '-';
                        $formatted[] = "$tempat, $tanggal";
                        break;

                    case 'alamat':
                        $alamat = [
                            $row->jalan ?? '-',
                            $row->kecamatan ?? '-',
                            $row->kabupaten ?? '-',
                            $row->provinsi ?? '-',
                            $row->negara ?? '-',
                        ];
                        $formatted[] = implode(', ', array_filter($alamat));
                        break;

                    case 'jenis_kelamin':
                        $formatted[] = $row->jenis_kelamin === 'l' ? 'Laki-laki' :
                                    ($row->jenis_kelamin === 'p' ? 'Perempuan' : $row->jenis_kelamin);
                        break;

                    case 'pendidikan_terakhir':
                        $jenjang = $row->jenjang_pendidikan_terakhir ?? '-';
                        $nama    = $row->nama_pendidikan_terakhir ?? '-';
                        $formatted[] = $jenjang . ' - ' . $nama;
                        break;

                    default:
                        $formatted[] = $row->{$field} ?? '-';
                        break;
                }
            }

            return $formatted;
        });
    }
    public function getFieldExportHeadings($fields, $addNumber = true)
    {
        $labelMap = [
            'no_kk'                => 'No KK',
            'nik'                 => 'NIK / Passport',
            'niup'                => 'NIUP',
            'nama_lengkap'        => 'Nama Lengkap',
            'tempat_tanggal_lahir'=> 'Tempat, Tanggal Lahir',
            'jenis_kelamin'       => 'Jenis Kelamin',
            'alamat'              => 'Alamat',
            'pendidikan_terakhir' => 'Pendidikan Terakhir',
            'email'               => 'Email',
            'no_telepon'          => 'No Telepon',
            'lembaga'             => 'Lembaga',
            'golongan_jabatan'    => 'Golongan Jabatan',
            'jabatan'             => 'Jabatan',
            'keterangan_jabatan'  => 'Keterangan Jabatan',
            'tanggal_mulai'       => 'Tanggal Mulai',
            'tanggal_selesai'     => 'Tanggal Selesai',
            'status_aktif'        => 'Status Aktif',
        ];

        $headings = [];

        if ($addNumber) {
            $headings[] = 'No';
        }

        foreach ($fields as $field) {
            $headings[] = $labelMap[$field] ?? ucfirst(str_replace('_', ' ', $field));
        }

        return $headings;
    }
}
