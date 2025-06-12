<?php

namespace App\Services\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BersaudaraService
{
    public function baseBersaudaraQuery(Request $request)
    {
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        // Foto terakhir
        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        // Warga pesantren aktif terakhir
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        // Ambil semua no_kk yang memiliki lebih dari 1 santri aktif
        $noKkBersaudara = DB::table('biodata as b')
            ->leftjoin('santri as s', 's.biodata_id', '=', 'b.id')
            ->join('keluarga AS k', 'k.id_biodata', '=', 's.biodata_id')
            ->leftJoin('pendidikan AS pd', 'b.id', '=', 'pd.biodata_id')
            ->where(fn($q) => $q->where('s.status', 'aktif')->orWhere('pd.status', 'aktif'))
            ->whereNull('s.deleted_at')
            ->groupBy('k.no_kk')
            ->havingRaw('COUNT(k.id_biodata) > 1')
            ->pluck('k.no_kk');

        // Orang tua per KK
        $parents = DB::table('orang_tua_wali AS otw')
            ->join('keluarga AS k2', 'k2.id_biodata', '=', 'otw.id_biodata')
            ->join('biodata AS b2', 'b2.id', '=', 'otw.id_biodata')
            ->join('hubungan_keluarga AS hk', 'hk.id', '=', 'otw.id_hubungan_keluarga')
            ->select([
                'k2.no_kk',
                DB::raw("MAX(CASE WHEN hk.nama_status IN ('ibu kandung', 'ibu sambung') THEN b2.nama END) AS nama_ibu"),
                DB::raw("MAX(CASE WHEN hk.nama_status IN ('ayah kandung', 'ayah sambung') THEN b2.nama END) AS nama_ayah"),
            ])
            ->groupBy('k2.no_kk');

        return DB::table('biodata as b')
            ->leftJoin('santri AS s', fn($j) => $j->on('b.id', '=', 's.biodata_id')->where('s.status', 'aktif'))
            ->join('keluarga AS k', 'k.id_biodata', '=', 'b.id')
            ->whereIn('k.no_kk', $noKkBersaudara)
            ->leftJoinSub($parents, 'parents', fn($j) => $j->on('k.no_kk', '=', 'parents.no_kk'))
            ->leftJoin('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
            ->leftJoin('domisili_santri AS ds', fn($join) => $join->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'))
            ->leftJoin('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->where(fn($q) => $q->where('s.status', 'aktif')
                ->orWhere('pd.status', 'aktif'))
            ->whereNull('b.deleted_at')
            ->whereNull('s.deleted_at');
    }

    // Query untuk LIST (select default)
    public function getAllBersaudara(Request $request, $fields = null)
    {
        $query = $this->baseBersaudaraQuery($request);

        // SELECT default jika tidak dikasih field custom
        $fields = $fields ?? [
            'b.id as biodata_id',
            's.id',
            DB::raw("COALESCE(b.nik, b.no_passport) AS identitas"),
            'k.no_kk',
            'b.nama',
            'wp.niup',
            'l.nama_lembaga',
            'w.nama_wilayah',
            'br.file_path AS foto_profil',
            'kb.nama_kabupaten AS kota_asal',
            's.created_at',
            DB::raw("GREATEST(
                s.updated_at,
                COALESCE(pd.updated_at, s.updated_at),
                COALESCE(ds.updated_at, s.updated_at)
            ) AS updated_at"),
            DB::raw("COALESCE(parents.nama_ibu, 'Tidak Diketahui') AS nama_ibu"),
            DB::raw("COALESCE(parents.nama_ayah, 'Tidak Diketahui') AS nama_ayah"),
        ];

        return $query->select($fields);
    }


    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            "biodata_id" => $item->biodata_id,
            "id" => $item->id,
            "nik_nopassport"   => $item->identitas,
            "no_kk"             => $item->no_kk,
            "nama"             => $item->nama,
            "niup"             => $item->niup ?? '-',
            "lembaga"          => $item->nama_lembaga ?? '-',
            "wilayah"          => $item->nama_wilayah ?? '-',
            "kota_asal"        => $item->kota_asal,
            "ibu_kandung"      => $item->nama_ibu,
            "ayah_kandung"     => $item->nama_ayah,
            "tgl_update"       => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            "tgl_input"        => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            "foto_profil"      => url($item->foto_profil),
        ]);
    }

    // Query untuk EXPORT, join dan select dinamis sesuai field
    public function getExportBersaudaraQuery($fields, $request)
    {
        $query = $this->baseBersaudaraQuery($request);

        // Join dinamis: tambah join sesuai field export, alias dikasih angka semua
        if (in_array('alamat', $fields)) {
            $query->leftJoin('kecamatan as kc2', 'b.kecamatan_id', '=', 'kc2.id');
            $query->leftJoin('kabupaten as kb2', 'b.kabupaten_id', '=', 'kb2.id');
            $query->leftJoin('provinsi as pv2', 'b.provinsi_id', '=', 'pv2.id');
            $query->leftJoin('negara as ng2', 'b.negara_id', '=', 'ng2.id');
        }
        if (in_array('domisili_santri', $fields)) {
            $query->leftJoin('blok as bl2', 'ds.blok_id', '=', 'bl2.id');
            $query->leftJoin('kamar as km2', 'ds.kamar_id', '=', 'km2.id');
        }
        if (in_array('angkatan_santri', $fields)) {
            $query->leftJoin('angkatan as as2', 's.angkatan_id', '=', 'as2.id');
        }
        if (in_array('angkatan_pelajar', $fields)) {
            $query->leftJoin('angkatan as ap2', 'pd.angkatan_id', '=', 'ap2.id');
        }
        if (in_array('jurusan', $fields)) {
            $query->leftJoin('jurusan AS j2', 'pd.jurusan_id', '=', 'j2.id');
        }
        if (in_array('kelas', $fields)) {
            $query->leftJoin('kelas AS kls2', 'pd.kelas_id', '=', 'kls2.id');
        }
        if (in_array('rombel', $fields)) {
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
                    $select[] = 'pd.no_induk';
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
                case 'domisili_santri':
                    $select[] = 'w.nama_wilayah as dom_wilayah';
                    $select[] = 'bl2.nama_blok as dom_blok';
                    $select[] = 'km2.nama_kamar as dom_kamar';
                    break;
                case 'angkatan_santri':
                    $select[] = 'as2.angkatan as angkatan_santri';
                    break;
                case 'angkatan_pelajar':
                    $select[] = 'ap2.angkatan as angkatan_pelajar';
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
                    case 'domisili_santri':
                        $data['Wilayah Domisili'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Blok Domisili']    = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kamar Domisili']   = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'angkatan_santri':
                        $data['Angkatan Santri'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'angkatan_pelajar':
                        $data['Angkatan Pelajar'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
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
            'domisili_santri' => ['Wilayah Domisili', 'Blok Domisili', 'Kamar Domisili'],
            'angkatan_santri' => 'Angkatan Santri',
            'angkatan_pelajar' => 'Angkatan Pelajar',
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
