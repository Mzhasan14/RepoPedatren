<?php

namespace App\Services\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PelajarService
{
    public function basePelajarQuery(Request $request)
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

        $query = DB::table('biodata as b')
            ->join('pendidikan AS pd', fn ($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
            ->leftJoin('jurusan AS j', 'pd.jurusan_id', '=', 'j.id')
            ->leftJoin('kelas AS kls', 'pd.kelas_id', '=', 'kls.id')
            ->leftJoin('rombel AS r', 'pd.rombel_id', '=', 'r.id')
            ->leftJoin('santri AS s', fn ($j) => $j->on('b.id', '=', 's.biodata_id')->where('s.status', 'aktif'))
            ->leftjoin('domisili_santri AS ds', fn ($join) => $join->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'))
            ->leftJoin('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
            ->leftJoinSub($fotoLast, 'fl', fn ($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn ($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->where(fn ($q) => $q->whereNull('b.deleted_at')->whereNull('pd.deleted_at'));

        return $query;
    }

    public function getAllPelajar(Request $request, $fields = null)
    {
        $query = $this->basePelajarQuery($request);

        $fields = $fields ?? [
            'b.id as biodata_id',
            'pd.no_induk',
            'b.nama',
            'l.nama_lembaga',
            'j.nama_jurusan',
            'kls.nama_kelas',
            'r.nama_rombel',
            'w.nama_wilayah',
            'kb.nama_kabupaten AS kota_asal',
            'pd.created_at',
            // ambil updated_at terbaru antar pd, pd, ds
            DB::raw('
                GREATEST(
                    pd.updated_at,
                    COALESCE(pd.updated_at, pd.updated_at),
                    COALESCE(ds.updated_at, pd.updated_at)
                ) AS updated_at
            '),
            'pd.status',
            DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
        ];

        return $query->select($fields);
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn ($item) => [
            'biodata_id' => $item->biodata_id,
            'no_induk' => $item->no_induk,
            'nama' => $item->nama,
            'lembaga' => $item->nama_lembaga,
            'jurusan' => $item->nama_jurusan,
            'kelas' => $item->nama_kelas ?? '-',
            'rombel' => $item->nama_rombel ?? '-',
            'wilayah' => $item->nama_wilayah ?? '-',
            'kota_asal' => $item->kota_asal,
            'tgl_update' => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            'tgl_input' => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            'status' => $item->status ?? '-',
            'foto_profil' => url($item->foto_profil),
        ]);
    }

    public function getExportPelajarQuery($fields, $request)
    {
        $query = $this->basePelajarQuery($request);

        // JOIN dinamis, alias semua pakai "2"
        if (in_array('no_kk', $fields)) {
            $query->leftJoin('keluarga as k', 'k.id_biodata', '=', 'b.id');
        }
        if (in_array('alamat', $fields)) {
            $query->leftJoin('kecamatan as kc2', 'b.kecamatan_id', '=', 'kc2.id');
            $query->leftJoin('kabupaten as kb2', 'b.kabupaten_id', '=', 'kb2.id');
            $query->leftJoin('provinsi as pv2', 'b.provinsi_id', '=', 'pv2.id');
            $query->leftJoin('negara as ng2', 'b.negara_id', '=', 'ng2.id');
        }
        if (in_array('domisili_santri', $fields)) {
            $query->leftJoin('domisili_santri AS ds2', fn ($join) => $join->on('s.id', '=', 'ds2.santri_id')->where('ds2.status', 'aktif'));
            $query->leftJoin('wilayah as w2', 'ds2.wilayah_id', '=', 'w2.id');
            $query->leftJoin('blok as bl2', 'ds2.blok_id', '=', 'bl2.id');
            $query->leftJoin('kamar as km2', 'ds2.kamar_id', '=', 'km2.id');
        }
        if (in_array('angkatan_santri', $fields)) {
            $query->leftJoin('angkatan as as2', 's.angkatan_id', '=', 'as2.id');
        }
        if (in_array('angkatan_pelajar', $fields)) {
            $query->leftJoin('angkatan as ap2', 'pd.angkatan_id', '=', 'ap2.id');
        }
        if (in_array('pendidikan', $fields)) {
            $query->leftJoin('lembaga AS l2', 'pd.lembaga_id', '=', 'l2.id');
            $query->leftJoin('jurusan AS j2', 'pd.jurusan_id', '=', 'j2.id');
            $query->leftJoin('kelas AS kls2', 'pd.kelas_id', '=', 'kls2.id');
            $query->leftJoin('rombel AS r2', 'pd.rombel_id', '=', 'r2.id');
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
        if (in_array('ayah_kandung', $fields)) {
            $subAyah = DB::table('keluarga as k1')
                ->select('k1.no_kk', 'otw2.id_biodata as id_biodata_ayah')
                ->join('orang_tua_wali as otw2', 'otw2.id_biodata', '=', 'k1.id_biodata')
                ->join('hubungan_keluarga as hk2', function ($join) {
                    $join->on('otw2.id_hubungan_keluarga', '=', 'hk2.id')
                        ->where('hk2.nama_status', '=', 'ayah kandung');
                });
            $query->leftJoinSub($subAyah, 'ayah2', function ($join) {
                $join->on('k.no_kk', '=', 'ayah2.no_kk');
            });
            $query->leftJoin('biodata as b_ayah2', 'ayah2.id_biodata_ayah', '=', 'b_ayah2.id');
        }

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
                case 'domisili_santri':
                    $select[] = 'w2.nama_wilayah as dom_wilayah';
                    $select[] = 'bl2.nama_blok as dom_blok';
                    $select[] = 'km2.nama_kamar as dom_kamar';
                    break;
                case 'angkatan_santri':
                    $select[] = 'as2.angkatan as angkatan_santri';
                    break;
                case 'angkatan_pelajar':
                    $select[] = 'ap2.angkatan as angkatan_pelajar';
                    break;
                case 'pendidikan':
                    $select[] = 'pd.no_induk';
                    $select[] = 'l2.nama_lembaga as lembaga';
                    $select[] = 'j2.nama_jurusan as jurusan';
                    $select[] = 'kls2.nama_kelas as kelas';
                    $select[] = 'r2.nama_rombel as rombel';
                    break;
                case 'status':
                    $select[] = DB::raw(
                        "CASE 
                            WHEN s.status = 'aktif' AND pd.status = 'aktif' THEN 'santri-pelajar'
                            WHEN s.status = 'aktif' THEN 'santri'
                            WHEN pd.status = 'aktif' THEN 'pelajar'
                            ELSE ''
                        END as status"
                    );
                    break;
                case 'ibu_kandung':
                    $select[] = 'b_ibu2.nama as nama_ibu';
                    break;
                case 'ayah_kandung':
                    $select[] = 'b_ayah2.nama as nama_ayah';
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
                        $data['NIS'] = ' '.($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'no_kk':
                        $data['No. KK'] = ' '.($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'nik':
                        $data['NIK'] = ' '.($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'niup':
                        $data['NIUP'] = ' '.($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'anak_ke':
                        $data['Anak ke'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'jumlah_saudara':
                        $data['Jumlah Saudara'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'alamat':
                        $data['Jalan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kecamatan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kabupaten'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Provinsi'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Negara'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'domisili_santri':
                        $data['Wilayah'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Blok'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kamar'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'angkatan_santri':
                        $data['Angkatan Santri'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'angkatan_pelajar':
                        $data['Angkatan Pelajar'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'pendidikan':
                        $data['No. Induk'] = ' '.($itemArr[array_keys($itemArr)[$i++]] ?? '');
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
            'alamat' => ['Jalan', 'Kecamatan', 'Kabupaten', 'Provinsi', 'Negara'],
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
                    foreach ($map[$field] as $h) {
                        $headings[] = $h;
                    }
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
