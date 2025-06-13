<?php

namespace App\Services\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Reader\Xls\RC4;

class AlumniService
{
    public function baseAlumniQuery(Request $request)
    {
        // 1) Sub‐query: tanggal_keluar riwayat_pendidikan alumni terakhir per santri
        $rpLast = DB::table('riwayat_pendidikan')
            ->select('biodata_id', DB::raw('MAX(tanggal_keluar) AS max_tanggal_keluar'))
            ->where('status', 'lulus')
            ->groupBy('biodata_id');

        // 2) Sub‐query: santri alumni terakhir
        $santriLast = DB::table('santri')
            ->select('id', DB::raw('MAX(id) AS last_id'))
            ->where('status', 'alumni')
            ->groupBy('id');

        // 3) Ambil ID untuk jenis berkas "Pas foto"
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        // 4) Subquery: foto terakhir per biodata
        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        // 5) Subquery: warga_pesantren terakhir per biodata (status = true)
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        $query = DB::table('biodata as b')
            ->leftJoin('santri AS s', fn($j) => $j->on('b.id', '=', 's.biodata_id')->where('s.status', 'alumni'))
            ->leftJoinSub($rpLast, 'lr', fn($j) => $j->on('lr.biodata_id', '=', 'b.id'))
            ->leftjoin('riwayat_pendidikan as rp', fn($j) => $j->on('rp.biodata_id', '=', 'lr.biodata_id')->on('rp.tanggal_keluar', '=', 'lr.max_tanggal_keluar'))
            ->leftJoin('lembaga as l', 'rp.lembaga_id', '=', 'l.id')
            ->leftJoinSub($santriLast, 'ld', fn($j) => $j->on('ld.id', '=', 's.id'))
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->where(fn($q) => $q->where('s.status', 'alumni')
                ->orWhere('rp.status', 'lulus'))
            ->where(fn($q) => $q->whereNull('b.deleted_at')
                ->whereNull('s.deleted_at')
                ->whereNull('rp.deleted_at'));

        return $query;
    }

    public function getAllalumni(Request $request, $fields = null)
    {
        $query = $this->baseAlumniQuery($request);

        $fields = $fields ?? [
            'b.id as biodata_id',
            'wp.niup',
            'b.nama',
            DB::raw('YEAR(rp.tanggal_keluar)  AS tahun_keluar_pelajar'),
            DB::raw('YEAR(s.tanggal_masuk)  AS tahun_masuk_santri'),
            DB::raw('YEAR(s.tanggal_keluar) AS tahun_keluar_santri'),
            'l.nama_lembaga',
            'kb.nama_kabupaten AS kota_asal',
            'b.created_at',
            'rp.status',
            DB::raw("
                GREATEST(
                    b.updated_at,
                    COALESCE(rp.updated_at, b.updated_at)
                ) AS updated_at
            "),
            DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
        ];

        return $query->select($fields);
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            "biodata_id" => $item->biodata_id,
            "nama" => $item->nama,
            "niup" => $item->niup ?? '-',
            "lembaga" => $item->nama_lembaga ?? '-',
            "tahun_keluar_pendidikan" => $item->tahun_keluar_pelajar ?? '-',
            "tahun_masuk_santri" => $item->tahun_masuk_santri ?? '-',
            "tahun_keluar_santri" => $item->tahun_keluar_santri ?? '-',
            "kota_asal" => $item->kota_asal,
            "tgl_update"       => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            "tgl_input"        => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            "status"           => $item->status,
            "foto_profil" => url($item->foto_profil)
        ]);
    }

    public function getExportAlumniQuery($fields, $request)
    {
        $query = $this->baseAlumniQuery($request);

        if (in_array('alamat', $fields)) {
            $query->leftJoin('kecamatan as kc2', 'b.kecamatan_id', '=', 'kc2.id');
            $query->leftJoin('kabupaten as kb2', 'b.kabupaten_id', '=', 'kb2.id');
            $query->leftJoin('provinsi as pv2', 'b.provinsi_id', '=', 'pv2.id');
            $query->leftJoin('negara as ng2', 'b.negara_id', '=', 'ng2.id');
        }
        if (in_array('angkatan_santri', $fields)) {
            $query->leftJoin('angkatan as as2', 's.angkatan_id', '=', 'as2.id');
        }
        if (in_array('angkatan_pelajar', $fields)) {
            $query->leftJoin('angkatan as ap2', 'rp.angkatan_id', '=', 'ap2.id');
        }
        if (in_array('jurusan', $fields)) {
            $query->leftJoin('jurusan AS j2', 'rp.jurusan_id', '=', 'j2.id');
        }
        if (in_array('kelas', $fields)) {
            $query->leftJoin('kelas AS kls2', 'rp.kelas_id', '=', 'kls2.id');
        }
        if (in_array('rombel', $fields)) {
            $query->leftJoin('rombel AS r2', 'rp.rombel_id', '=', 'r2.id');
        }
        if (in_array('ibu_kandung', $fields)) {
            $subIbu = DB::table('keluarga as k1')
                ->select('k1.no_kk', 'otw2.id_biodata as id_biodata_ibu')
                ->join('orang_tua_wali as otw2', 'otw2.id_biodata', '=', 'k1.id_biodata')
                ->join('hubungan_keluarga as hk2', function ($join) {
                    $join->on('otw2.id_hubungan_keluarga', '=', 'hk2.id')
                        ->where('hk2.nama_status', '=', 'ibu kandung');
                });
            $query->leftJoinSub($subIbu, 'ibu2', function ($join) {
                $join->on('k.no_kk', '=', 'ibu2.no_kk');
            });
            $query->leftJoin('biodata as b_ibu2', 'ibu2.id_biodata_ibu', '=', 'b_ibu2.id');
        }

        // Mapping select sesuai $fields, sesuaikan alias yang baru!
        $select = [];
        foreach ($fields as $field) {
            switch ($field) {
                case 'nama':
                    $select[] = 'b.nama';
                    break;
                case 'tempat_lahir':
                    $select[] = 'b.tempat_lahir';
                    break;
                case 'tanggal_lahir':
                    $select[] = 'b.tanggal_lahir';
                    break;
                case 'jenis_kelamin':
                    $select[] = 'b.jenis_kelamin';
                    break;
                case 'nis':
                    $select[] = 's.nis';
                    break;
                case 'no_induk':
                    $select[] = 'rp.no_induk';
                    break;
                case 'lembaga':
                    $select[] = 'l.nama_lembaga as lembaga';
                    break;
                case 'jurusan':
                    $select[] = 'j2.nama_jurusan as jurusan';
                    break;
                case 'kelas':
                    $select[] = 'kls2.nama_kelas as kelas';
                    break;
                case 'rombel':
                    $select[] = 'r2.nama_rombel as rombel';
                    break;
                case 'no_kk':
                    $select[] = 'k.no_kk';
                    break;
                case 'nik':
                    $select[] = DB::raw('COALESCE(b.nik, b.no_passport) as nik');
                    break;
                case 'niup':
                    $select[] = 'wp.niup';
                    break;
                case 'anak_ke':
                    $select[] = 'b.anak_keberapa';
                    break;
                case 'jumlah_saudara':
                    $select[] = 'b.dari_saudara';
                    break;
                case 'alamat':
                    $select[] = 'b.jalan';
                    $select[] = 'kc2.nama_kecamatan';
                    $select[] = 'kb2.nama_kabupaten';
                    $select[] = 'pv2.nama_provinsi';
                    $select[] = 'ng2.nama_negara';
                    break;
                case 'angkatan_santri':
                    $select[] = 'as2.angkatan as angkatan_santri';
                    break;
                case 'angkatan_pelajar':
                    $select[] = 'ap2.angkatan as angkatan_pelajar';
                    break;
                case 'tahun_keluar_santri':
                    $select[] = DB::raw('YEAR(s.tanggal_keluar) as tahun_keluar_santri');
                    break;
                case 'tahun_keluar_pelajar':
                    $select[] = DB::raw('YEAR(rp.tanggal_keluar) as tahun_keluar_pelajar');
                    break;
                case 'status':
                    $select[] = DB::raw(
                        "CASE 
                            WHEN s.status = 'aktif' AND rp.status = 'aktif' THEN 'santri-pelajar'
                            WHEN s.status = 'aktif' THEN 'santri'
                            WHEN rp.status = 'aktif' THEN 'pelajar'
                            ELSE ''
                        END as status"
                    );
                    break;
                case 'ibu_kandung':
                    $select[] = 'b_ibu2.nama as nama_ibu';
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
            $itemArr = (array) $item;
            $i = 0; // pointer index hasil select (array order)

            foreach ($fields as $field) {
                switch ($field) {
                    case 'nama':
                        $data['Nama'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'tempat_lahir':
                        $data['Tempat Lahir'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'tanggal_lahir':
                        $tgl = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Tanggal Lahir'] = $tgl ? \Carbon\Carbon::parse($tgl)->translatedFormat('d F Y') : '';
                        break;
                    case 'jenis_kelamin':
                        $jk = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        if (strtolower($jk) === 'l') $data['Jenis Kelamin'] = 'Laki-laki';
                        elseif (strtolower($jk) === 'p') $data['Jenis Kelamin'] = 'Perempuan';
                        else $data['Jenis Kelamin'] = '';
                        break;
                    case 'nis':
                        $data['NIS'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'no_induk':
                        $data['No. Induk'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'lembaga':
                        $data['Lembaga'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'jurusan':
                        $data['Jurusan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'kelas':
                        $data['Kelas'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'rombel':
                        $data['Rombel'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'no_kk':
                        $data['No. KK'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'nik':
                        $data['NIK'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'niup':
                        $data['NIUP'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'anak_ke':
                        $data['Anak ke'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'jumlah_saudara':
                        $data['Jumlah Saudara'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'alamat':
                        $data['Jalan']     = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kecamatan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kabupaten'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Provinsi']  = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Negara']    = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'angkatan_santri':
                        $data['Angkatan Santri'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'angkatan_pelajar':
                        $data['Angkatan Pelajar'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'tahun_keluar_santri':
                        $data['Tahun Keluar Santri'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'tahun_keluar_pelajar':
                        $data['Tahun Keluar Pelajar'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'status':
                        $data['Status'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'ibu_kandung':
                        $data['Ibu Kandung'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    default:
                        $data[$field] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                }
            }
            return $data;
        })->values();
    }

    public function getFieldExportHeadings($fields, $addNumber = false)
    {
        $map = [
            'nama' => 'Nama',
            'tempat_lahir' => 'Tempat Lahir',
            'tanggal_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
            'nis' => 'NIS',
            'no_induk' => 'No. Induk',
            'lembaga' => 'Lembaga',
            'jurusan' => 'Jurusan',
            'kelas' => 'Kelas',
            'rombel' => 'Rombel',
            'no_kk' => 'No. KK',
            'nik' => 'NIK',
            'niup' => 'NIUP',
            'anak_ke' => 'Anak ke',
            'jumlah_saudara' => 'Jumlah Saudara',
            'alamat'   => ['Jalan', 'Kecamatan', 'Kabupaten', 'Provinsi', 'Negara'],
            'angkatan_santri' => 'Angkatan Santri',
            'angkatan_pelajar' => 'Angkatan Pelajar',
            'tahun_keluar_santri' => 'Tahun Keluar Santri',
            'tahun_keluar_pelajar' => 'Tahun Keluar Pelajar',
            'status' => 'Status',
            'ibu_kandung' => 'Ibu Kandung',
        ];
        $headings = [];
        foreach ($fields as $field) {
            if (isset($map[$field])) {
                if (is_array($map[$field])) {
                    foreach ($map[$field] as $h) $headings[] = $h;
                } else {
                    $headings[] = $map[$field];
                }
            } else {
                $headings[] = $field;
            }
        }
        if ($addNumber) {
            array_unshift($headings, 'No');
        }
        return $headings;
    }
}
