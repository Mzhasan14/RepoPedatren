<?php

namespace App\Services\Pegawai;

use App\Models\Pegawai\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengurusService
{
    public function basePengurusQuery(Request $request)
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

        $query = DB::table('pengurus')
            // Golongan jabatan aktif
            ->leftJoin('golongan_jabatan as g', function ($join) {
                $join->on('pengurus.golongan_jabatan_id', '=', 'g.id')
                    ->where('g.status', true);
            })
            // Pegawai aktif
            ->join('pegawai', function ($join) {
                $join->on('pengurus.pegawai_id', '=', 'pegawai.id')
                    ->where('pegawai.status_aktif', 'aktif')
                    ->whereNull('pegawai.deleted_at');
            })
            ->join('biodata as b', 'pegawai.biodata_id', '=', 'b.id')
            // Warga pesantren aktif terakhir
            ->leftJoinSub($wpLast, 'wl', fn ($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            // Foto terakhir
            ->leftJoinSub($fotoLast, 'fl', fn ($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->whereNull('pengurus.tanggal_akhir')
            ->where('pengurus.status_aktif', 'aktif');

        return $query;
    }
    public function getAllPengurus(Request $request)
    {
        try {
            $query = $this->basePengurusQuery($request);

            $fields = [
                'pegawai.biodata_id as biodata_uuid',
                'b.nama',
                'b.nik',
                'wp.niup',
                'pengurus.keterangan_jabatan as jabatan',
                DB::raw('TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur'),
                'pengurus.satuan_kerja',
                'pengurus.jabatan as jenis',
                'g.nama_golongan_jabatan as nama_golongan',
                'b.nama_pendidikan_terakhir as pendidikan_terakhir',
                DB::raw("DATE_FORMAT(pengurus.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
                DB::raw("DATE_FORMAT(pengurus.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
                DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
            ];

            return $query->select($fields)->groupBy(
                'pegawai.biodata_id',
                'b.nama',
                'b.nik',
                'wp.niup',
                'pengurus.keterangan_jabatan',
                'b.tanggal_lahir',
                'pengurus.satuan_kerja',
                'pengurus.jabatan',
                'g.nama_golongan_jabatan',
                'b.nama_pendidikan_terakhir',
                'pengurus.updated_at',
                'pengurus.created_at'
            );
        } catch (\Exception $e) {
            Log::error('Error fetching data Pengurus: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data Pengurus',
                'code' => 500,
            ], 500);
        }
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn ($item) => [
            'biodata_id' => $item->biodata_uuid,
            'nama' => $item->nama,
            'nik' => $item->nik,
            'niup' => $item->niup ?? '-',
            'jabatan' => $item->jabatan,
            'umur' => $item->umur,
            'satuan_kerja' => $item->satuan_kerja ?? '-',
            'jenis_jabatan' => $item->jenis,
            'golongan' => $item->nama_golongan,
            'pendidikan_terakhir' => $item->pendidikan_terakhir,
            'tgl_update' => $item->tgl_update ?? '-',
            'tgl_input' => $item->tgl_input,
            'foto_profil' => url($item->foto_profil),
        ]);
    }
    public function getExportQuery($fields, $request)
    {
        $query = $this->basePengurusQuery($request);

        if (in_array('no_kk', $fields)) {
            $query->leftJoin('keluarga as k', 'k.id_biodata', '=', 'b.id');
        }

        if (in_array('niup', $fields)) {
            $query->leftJoin('warga_pesantren as wp2', function ($join) {
                $join->on('wp2.biodata_id', '=', 'b.id')->where('wp2.status', true);
            });
        }

        if (in_array('alamat', $fields)) {
            $query->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id');
            $query->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id');
            $query->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id');
        }

        if (in_array('golongan_jabatan', $fields)) {
            $query->leftJoin('golongan_jabatan as gj', 'gj.id', '=', 'pengurus.golongan_jabatan_id');
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
                case 'no_kk':
                    $select[] = 'k.no_kk';
                    break;
                case 'niup':
                    $select[] = DB::raw("COALESCE(wp2.niup, '-') as niup");
                    break;
                case 'jenis_kelamin':
                    $select[] = 'b.jenis_kelamin';
                    break;
                case 'tempat_tanggal_lahir':
                    $select[] = 'b.tempat_lahir';
                    $select[] = 'b.tanggal_lahir';
                    break;
                case 'alamat':
                    $select[] = 'b.jalan';
                    $select[] = 'kc.nama_kecamatan as kecamatan';
                    $select[] = 'kb.nama_kabupaten as kabupaten';
                    $select[] = 'pv.nama_provinsi as provinsi';
                    break;
                case 'pendidikan_terakhir':
                    $select[] = 'b.jenjang_pendidikan_terakhir';
                    $select[] = 'b.nama_pendidikan_terakhir';
                    break;
                case 'email':
                    $select[] = 'b.email';
                    break;
                case 'no_hp':
                    $select[] = 'b.no_telepon';
                    break;
                case 'satuan_kerja':
                    $select[] = 'pengurus.satuan_kerja';
                    break;
                case 'golongan_jabatan':
                    $select[] = 'gj.nama_golongan_jabatan';
                    break;
                case 'jabatan':
                    $select[] = 'pengurus.jabatan';
                    break;
                case 'keterangan_jabatan':
                    $select[] = 'pengurus.keterangan_jabatan';
                    break;
                case 'status_aktif':
                    $select[] = DB::raw("IF(pengurus.status_aktif = 1, 'Aktif', 'Nonaktif') as status_aktif");
                    break;
            }
        }

        return $query->select($select);
    }

    public function formatDataExport($results, $fields, $addNumber = false)
    {
        return collect($results)->map(function ($item, $idx) use ($fields, $addNumber) {
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
                        $data['Tanggal Lahir'] = $tgl ? Carbon::parse($tgl)->translatedFormat('d F Y') : '';
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
                        break;
                    case 'pendidikan_terakhir':
                        $data['Jenjang Pendidikan Terakhir'] = $itemArr[$i++] ?? '';
                        $data['Nama Pendidikan Terakhir'] = $itemArr[$i++] ?? '';
                        break;
                    case 'email':
                        $data['Email'] = $itemArr[$i++] ?? '';
                        break;
                    case 'no_hp':
                        $data['No. HP'] = $itemArr[$i++] ?? '';
                        break;
                    case 'satuan_kerja':
                        $data['Satuan Kerja'] = $itemArr[$i++] ?? '';
                        break;
                    case 'golongan_jabatan':
                        $data['Golongan Jabatan'] = $itemArr[$i++] ?? '';
                        break;
                    case 'jabatan':
                        $data['Jabatan'] = $itemArr[$i++] ?? '';
                        break;
                    case 'keterangan_jabatan':
                        $data['Keterangan Jabatan'] = $itemArr[$i++] ?? '';
                        break;
                    case 'status_aktif':
                        $data['Status Aktif'] = $itemArr[$i++] ?? '';
                        break;
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
            'niup' => 'NIUP',
            'jenis_kelamin' => 'Jenis Kelamin',
            'no_kk' => 'No. KK',
            'email' => 'Email',
            'no_hp' => 'No. HP',
            'satuan_kerja' => 'Satuan Kerja',
            'golongan_jabatan' => 'Golongan Jabatan',
            'jabatan' => 'Jabatan',
            'keterangan_jabatan' => 'Keterangan Jabatan',
            'status_aktif' => 'Status Aktif',
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
