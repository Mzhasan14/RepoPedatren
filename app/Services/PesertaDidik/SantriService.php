<?php

namespace App\Services\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SantriService
{
    public function getAllSantri(Request $request)
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

        // 3) Subquery: warga_pesantren terakhir per biodata (status = true)
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        return DB::table('santri AS s')
            ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
            ->leftjoin('domisili_santri AS ds', fn($join) => $join->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'))
            ->leftjoin('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
            ->leftjoin('blok AS bl', 'ds.blok_id', '=', 'bl.id')
            ->leftjoin('kamar AS km', 'ds.kamar_id', '=', 'km.id')
            ->leftjoin('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->where('s.status', 'aktif')
            ->where(fn($q) => $q->whereNull('b.deleted_at')
                ->whereNull('s.deleted_at'))
            ->select([
                'b.id as biodata_id',
                's.id',
                's.nis',
                'b.nama',
                'wp.niup',
                'km.nama_kamar',
                'bl.nama_blok',
                'l.nama_lembaga',
                'w.nama_wilayah',
                DB::raw('YEAR(s.tanggal_masuk) as angkatan'),
                'kb.nama_kabupaten AS kota_asal',
                's.created_at',
                // ambil updated_at terbaru antar s, pd, ds
                DB::raw("
                   GREATEST(
                       s.updated_at,
                       COALESCE(pd.updated_at, s.updated_at),
                       COALESCE(ds.updated_at, s.updated_at)
                   ) AS updated_at
               "),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ]);
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            "biodata_id" => $item->biodata_id,
            "id" => $item->id,
            "nis" => $item->nis,
            "nama" => $item->nama,
            "niup" => $item->niup ?? '-',
            "lembaga" => $item->nama_lembaga ?? '-',
            "wilayah" => $item->nama_wilayah ?? '-',
            "blok" => $item->nama_blok ?? '-',
            "kamar" => $item->nama_kamar ?? '-',
            "angkatan" => $item->angkatan,
            "kota_asal" => $item->kota_asal,
            "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            "foto_profil" => url($item->foto_profil)
        ]);
    }

    public function getExportSantriQuery($fields, $request)
    {
        // 3) Subquery: warga_pesantren terakhir per biodata (status = true)
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        $query = DB::table('santri as s')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftjoin('angkatan as as', 's.angkatan_id', '=', 'as.id')
            ->leftjoin('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
            ->leftjoin('angkatan as ap', 'pd.angkatan_id', '=', 'ap.id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftjoin('keluarga as k', 'k.id_biodata', 'b.id')
            ->where('s.status', 'aktif')
            ->where(fn($q) => $q->whereNull('b.deleted_at')->whereNull('s.deleted_at'));

        // Untuk multi-field (alamat, domisili_santri, pendidikan), lakukan join di awal, 
        // lalu tambahkan field pada saat loop, supaya urutan terjaga.

        // JOIN alamat jika field ada
        if (in_array('alamat', $fields)) {
            $query->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id');
            $query->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id');
            $query->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id');
            $query->leftJoin('negara as ng', 'b.negara_id', '=', 'ng.id');
        }

        // JOIN domisili santri jika field ada
        if (in_array('domisili_santri', $fields)) {
            $query->leftjoin('domisili_santri AS ds', fn($join) => $join->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'));
            $query->leftJoin('wilayah as w', 'ds.wilayah_id', '=', 'w.id');
            $query->leftJoin('blok as bl', 'ds.blok_id', '=', 'bl.id');
            $query->leftJoin('kamar as km', 'ds.kamar_id', '=', 'km.id');
        }

        // JOIN pendidikan jika field ada
        if (in_array('pendidikan', $fields)) {
            $query->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id');
            $query->leftJoin('jurusan AS j', 'pd.jurusan_id', '=', 'j.id');
            $query->leftJoin('kelas AS kls', 'pd.kelas_id', '=', 'kls.id');
            $query->leftJoin('rombel AS r', 'pd.rombel_id', '=', 'r.id');
        }

        // Join ibu kandung jika field ada
        if (in_array('ibu_kandung', $fields)) {
            $subIbu = DB::table('keluarga as k1')
                ->select('k1.no_kk', 'otw.id_biodata as id_biodata_ibu')
                ->join('orang_tua_wali as otw', 'otw.id_biodata', '=', 'k1.id_biodata')
                ->join('hubungan_keluarga as hk', function ($join) {
                    $join->on('otw.id_hubungan_keluarga', '=', 'hk.id')
                        ->where('hk.nama_status', '=', 'ibu kandung');
                });
            $query->leftJoinSub($subIbu, 'ibu', function ($join) {
                $join->on('k.no_kk', '=', 'ibu.no_kk');
            });
            $query->leftJoin('biodata as b_ibu', 'ibu.id_biodata_ibu', '=', 'b_ibu.id');
        }

        // Join ayah kandung jika field ada
        if (in_array('ayah_kandung', $fields)) {
            $subAyah = DB::table('keluarga as k1')
                ->select('k1.no_kk', 'otw.id_biodata as id_biodata_ayah')
                ->join('orang_tua_wali as otw', 'otw.id_biodata', '=', 'k1.id_biodata')
                ->join('hubungan_keluarga as hk', function ($join) {
                    $join->on('otw.id_hubungan_keluarga', '=', 'hk.id')
                        ->where('hk.nama_status', '=', 'ayah kandung');
                });
            $query->leftJoinSub($subAyah, 'ayah', function ($join) {
                $join->on('k.no_kk', '=', 'ayah.no_kk');
            });
            $query->leftJoin('biodata as b_ayah', 'ayah.id_biodata_ayah', '=', 'b_ayah.id');
        }

        // -------------------------------
        // Fix utama: $select[] hanya diisi dari $fields
        // -------------------------------

        $select = [];

        foreach ($fields as $field) {
            switch ($field) {
                case 'nama':
                    $select[] = 'b.nama';
                    break;
                case 'tempat_tanggal_lahir':
                    $select[] = 'b.tempat_lahir';
                    $select[] = 'b.tanggal_lahir';
                    break;
                case 'jenis_kelamin':
                    $select[] = 'b.jenis_kelamin';
                    break;
                case 'nis':
                    $select[] = 's.nis';
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
                    $select[] = 'b.anak_ke';
                    break;
                case 'jumlah_saudara':
                    $select[] = 'b.jumlah_saudara';
                    break;
                case 'alamat':
                    $select[] = 'b.jalan';
                    $select[] = 'kc.nama_kecamatan';
                    $select[] = 'kb.nama_kabupaten';
                    $select[] = 'pv.nama_provinsi';
                    $select[] = 'ng.nama_negara';
                    break;
                case 'domisili_santri':
                    $select[] = 'w.nama_wilayah as dom_wilayah';
                    $select[] = 'bl.nama_blok as dom_blok';
                    $select[] = 'km.nama_kamar as dom_kamar';
                    break;
                case 'angkatan_santri':
                    $select[] = 'as.angkatan as angkatan_santri';
                    break;
                case 'angkatan_pelajar':
                    $select[] = 'ap.angkatan as angkatan_pelajar';
                    break;
                case 'pendidikan':
                    $select[] = 'pd.no_induk';
                    $select[] = 'l.nama_lembaga as lembaga';
                    $select[] = 'j.nama_jurusan as jurusan';
                    $select[] = 'kls.nama_kelas as kelas';
                    $select[] = 'r.nama_rombel as rombel';
                    break;
                case 'status':
                    $select[] = 's.status';
                    break;
                case 'ibu_kandung':
                    $select[] = 'b_ibu.nama as nama_ibu';
                    break;
                case 'ayah_kandung':
                    $select[] = 'b_ayah.nama as nama_ayah';
                    break;
            }
        }

        $query->select($select);

        return $query;
    }


    public function formatDataExport($results, $fields, $addNumber = false)
    {
        return collect($results)->values()->map(function ($item, $idx) use ($fields, $addNumber) {
            $data = [];
            if ($addNumber) {
                $data['No'] = $idx + 1;
            }
            $itemArr = (array) $item; // convert to array to support index based access for multi-fields

            $i = 0; // index pointer untuk multi-field
            foreach ($fields as $field) {
                switch ($field) {
                    case 'nama':
                        $data['Nama'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'tempat_tanggal_lahir':
                        $data['Tempat Lahir'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $tgl = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Tanggal Lahir'] = $tgl ? \Carbon\Carbon::parse($tgl)->translatedFormat('d F Y') : '';
                        break;
                    case 'jenis_kelamin':
                        $jk = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        if (strtolower($jk) === 'l') {
                            $data['Jenis Kelamin'] = 'Laki-laki';
                        } elseif (strtolower($jk) === 'p') {
                            $data['Jenis Kelamin'] = 'Perempuan';
                        } else {
                            $data['Jenis Kelamin'] = '';
                        }
                        break;
                    case 'nis':
                        $data['NIS'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
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
                    case 'domisili_santri':
                        $data['Wilayah'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Blok']    = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kamar']   = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'angkatan_santri':
                        $data['Angkatan Santri'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'angkatan_pelajar':
                        $data['Angkatan Pelajar'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'pendidikan':
                        $data['No. Induk'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        $data['Lembaga'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Jurusan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kelas'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Rombel'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'status':
                        $data['Status'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'ibu_kandung':
                        $data['Ibu Kandung'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'ayah_kandung':
                        $data['Ayah Kandung'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
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
            'tempat_tanggal_lahir' => ['Tempat Lahir', 'Tanggal Lahir'],
            'jenis_kelamin' => 'Jenis Kelamin',
            'nis' => 'NIS',
            'no_kk' => 'No. KK',
            'nik' => 'NIK',
            'niup' => 'NIUP',
            'anak_ke' => 'Anak ke',
            'jumlah_saudara' => 'Jumlah Saudara',
            'alamat'   => ['Jalan', 'Kecamatan', 'Kabupaten', 'Provinsi', 'Negara'],
            'domisili_santri' => ['Wilayah', 'Blok', 'Kamar'],
            'angkatan_santri' => 'Angkatan Santri',
            'angkatan_pelajar' => 'Angkatan Pelajar',
            'pendidikan' => ['No. Induk', 'Lembaga', 'Jurusan', 'Kelas', 'Rombel'],
            'status' => 'Status',
            'ibu_kandung' => 'Ibu Kandung',
            'ayah_kandung' => 'Ayah Kandung',
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
