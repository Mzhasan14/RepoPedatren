<?php

namespace App\Services\Pegawai;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengajarService
{
    public function basePengajarQuery(Request $request)
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

        return DB::table('pengajar')
            ->join('pegawai', function ($join) {
                $join->on('pegawai.id', '=', 'pengajar.pegawai_id')
                    ->where('pegawai.status_aktif', 'aktif')
                    ->whereNull('pegawai.deleted_at');
            })
            ->join('biodata as b', 'pegawai.biodata_id', '=', 'b.id')
            ->leftJoinSub($wpLast, 'wl', fn ($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('golongan as g', 'pengajar.golongan_id', '=', 'g.id')
            ->leftJoin('kategori_golongan as kg', 'g.kategori_golongan_id', '=', 'kg.id')
            ->leftJoinSub($fotoLast, 'fl', fn ($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            
            // Mata pelajaran dan jadwal
            ->leftJoin('mata_pelajaran', function ($join) {
                $join->on('mata_pelajaran.pengajar_id', '=', 'pengajar.id')
                ->where('mata_pelajaran.status', true);
            })
            ->leftJoin('lembaga as l', 'mata_pelajaran.lembaga_id', '=', 'l.id')
            ->leftJoin('jadwal_pelajaran', 'mata_pelajaran.id', '=', 'jadwal_pelajaran.mata_pelajaran_id')
            ->leftJoin('jam_pelajaran', 'jadwal_pelajaran.jam_pelajaran_id', '=', 'jam_pelajaran.id')

            ->whereNull('pengajar.tahun_akhir')
            ->where('pengajar.status_aktif', 'aktif');
    }
    public function getAllPengajar(Request $request)
    {
        $query = $this->basePengajarQuery($request);

        $fields = [
            'pegawai.biodata_id as biodata_uuid',
            'b.nama',
            'wp.niup',
            DB::raw('TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur'),

            // Mapel dan mengajar
            DB::raw("GROUP_CONCAT(DISTINCT mata_pelajaran.nama_mapel SEPARATOR ', ') AS daftar_mapel"),
            DB::raw("COUNT(DISTINCT mata_pelajaran.id) AS total_mapel"),
            DB::raw("CONCAT(
                TRUNCATE(SUM(TIME_TO_SEC(TIMEDIFF(jam_pelajaran.jam_selesai, jam_pelajaran.jam_mulai))) / 3600, 0), ' jam ',
                TRUNCATE((SUM(TIME_TO_SEC(TIMEDIFF(jam_pelajaran.jam_selesai, jam_pelajaran.jam_mulai))) % 3600) / 60, 0), ' menit'
            ) AS total_waktu_mengajar"),


            // Materi (dianggap sama dengan mapel)
            DB::raw("GROUP_CONCAT(DISTINCT mata_pelajaran.nama_mapel SEPARATOR ', ') AS daftar_materi"),
            DB::raw("COUNT(DISTINCT mata_pelajaran.id) AS total_materi"),

            // Lainnya
            DB::raw("CASE 
                WHEN TIMESTAMPDIFF(YEAR, pengajar.tahun_masuk, COALESCE(pengajar.tahun_akhir, CURDATE())) = 0 
                THEN CONCAT('Belum setahun sejak ', DATE_FORMAT(pengajar.tahun_masuk, '%Y-%m-%d'), ' sampai ', IF(pengajar.tahun_akhir IS NOT NULL, DATE_FORMAT(pengajar.tahun_akhir, '%Y-%m-%d'), 'saat ini'))
                ELSE CONCAT(TIMESTAMPDIFF(YEAR, pengajar.tahun_masuk, COALESCE(pengajar.tahun_akhir, CURDATE())), ' Tahun sejak ', DATE_FORMAT(pengajar.tahun_masuk, '%Y-%m-%d'), ' sampai ', IF(pengajar.tahun_akhir IS NOT NULL, DATE_FORMAT(pengajar.tahun_akhir, '%Y-%m-%d'), 'saat ini'))
            END AS masa_kerja"),

            'g.nama_golongan',
            'b.nama_pendidikan_terakhir',
            DB::raw("DATE_FORMAT(pengajar.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
            DB::raw("DATE_FORMAT(pengajar.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
            'l.nama_lembaga',
            DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
        ];

        return $query->select($fields)->groupBy(
            'pegawai.biodata_id',
            'b.nama',
            'wp.niup',
            'b.tanggal_lahir',
            'g.nama_golongan',
            'b.nama_pendidikan_terakhir',
            'pengajar.updated_at',
            'pengajar.created_at',
            'l.nama_lembaga',
            'pengajar.tahun_masuk',
            'pengajar.tahun_akhir'
        );
    }
    public function formatData($results)
    {
        return collect($results->items())->map(fn ($item) => [
            'biodata_id' => $item->biodata_uuid,
            'nama' => $item->nama,
            'niup' => $item->niup ?? '-',
            'umur' => $item->umur,
            'daftar_materi' => $item->daftar_materi ?? '-',
            'total_materi' => $item->total_materi ?? 0,
            'masa_kerja' => $item->masa_kerja ?? '-',
            'golongan' => $item->nama_golongan,
            'pendidikan_terakhir' => $item->nama_pendidikan_terakhir,
            'tgl_update' => Carbon::parse($item->tgl_update)->translatedFormat('d F Y H:i:s'),
            'tgl_input' => Carbon::parse($item->tgl_input)->translatedFormat('d F Y H:i:s'),
            'lembaga' => $item->nama_lembaga ?? '-',
            'foto_profil' => url($item->foto_profil),
        ]);
    }
    public function getExportPengajarQuery($fields, $request)
    {
        $query = $this->basePengajarQuery($request); // Sama seperti basePegawaiQuery

        if (in_array('no_kk', $fields)) {
            $query->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata');
        }

        if (in_array('jalan', $fields)) {
            $query->leftJoin('kecamatan as kec', 'kec.id', '=', 'b.kecamatan_id')
                ->leftJoin('kabupaten as kab', 'kab.id', '=', 'b.kabupaten_id')
                ->leftJoin('provinsi as prov', 'prov.id', '=', 'b.provinsi_id')
                ->leftJoin('negara as neg', 'neg.id', '=', 'b.negara_id');
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
                case 'jenis_kelamin':
                    $select[] = 'b.jenis_kelamin';
                    break;
                case 'tempat_lahir':
                case 'tanggal_lahir':
                    $select[] = "b.$field";
                    break;
                case 'no_kk':
                    $select[] = 'k.no_kk';
                    break;
                case 'jalan':
                    $select[] = 'b.jalan';
                    $select[] = 'kec.nama_kecamatan';
                    $select[] = 'kab.nama_kabupaten';
                    $select[] = 'prov.nama_provinsi';
                    $select[] = 'neg.nama_negara';
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
                case 'golongan':
                    $select[] = 'g.nama_golongan';
                    break;
                case 'jabatan':
                    $select[] = 'pengajar.jabatan';
                    break;
                case 'status_aktif':
                    $select[] = DB::raw("IF(pengajar.status_aktif = 1, 'Aktif', 'Nonaktif') as status_aktif");
                    break;
            }
        }

            return $query->select($select)->groupBy([
                'b.nama',
                'b.nik',
                'b.no_passport',
                'k.no_kk',
                'wp.niup',
                'b.jenis_kelamin',
                'b.tempat_lahir',
                'b.tanggal_lahir',
                'b.jenjang_pendidikan_terakhir',
                'b.nama_pendidikan_terakhir',
                'b.email',
                'b.no_telepon',
                'b.jalan',
                'kec.nama_kecamatan',
                'kab.nama_kabupaten',
                'prov.nama_provinsi',
                'neg.nama_negara',
                'l.nama_lembaga',
                'g.nama_golongan',
                'pengajar.jabatan',
                'pengajar.status_aktif',
                'pengajar.id'
    ]);

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
                        $data['NIK / Passport'] = ' ' . ($itemArr[$i++] ?? '');
                        break;
                    case 'niup':
                        $data['NIUP'] = ' ' . ($itemArr[$i++] ?? '');
                        break;
                    case 'tempat_lahir':
                        $data['Tempat Lahir'] = $itemArr[$i++] ?? '';
                        break;
                    case 'tanggal_lahir':
                        $tgl = $itemArr[$i++] ?? '';
                        $data['Tanggal Lahir'] = $tgl ? Carbon::parse($tgl)->translatedFormat('d F Y') : '';
                        break;
                    case 'jenis_kelamin':
                        $jk = $itemArr[$i++] ?? '';
                        $data['Jenis Kelamin'] = strtolower($jk) === 'l' ? 'Laki-laki' : (strtolower($jk) === 'p' ? 'Perempuan' : '');
                        break;
                    case 'no_kk':
                        $data['No KK'] = ' ' . ($itemArr[$i++] ?? '');
                        break;
                    case 'jalan':
                        $data['Jalan'] = $itemArr[$i++] ?? '';
                        $data['Kecamatan'] = $itemArr[$i++] ?? '';
                        $data['Kabupaten'] = $itemArr[$i++] ?? '';
                        $data['Provinsi'] = $itemArr[$i++] ?? '';
                        $data['Negara'] = $itemArr[$i++] ?? '';
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
        })->values();
    }
    public function getFieldExportHeadings($fields, $addNumber = false)
    {
        $labels = [
            'nama_lengkap' => 'Nama Lengkap',
            'nik' => 'NIK / Passport',
            'no_kk' => 'No KK',
            'niup' => 'NIUP',
            'jenis_kelamin' => 'Jenis Kelamin',
            'tempat_lahir' => 'Tempat Lahir',
            'tanggal_lahir' => 'Tanggal Lahir',
            'pendidikan_terakhir' => ['Jenjang Pendidikan Terakhir', 'Nama Pendidikan Terakhir'],
            'email' => 'Email',
            'no_telepon' => 'No Telepon',
            'lembaga' => 'Lembaga',
            'golongan' => 'Golongan',
            'jabatan' => 'Jabatan',
            'status_aktif' => 'Status Aktif',
            'jalan' => ['Jalan', 'Kecamatan', 'Kabupaten', 'Provinsi', 'Negara'],
        ];

        $headings = [];
        if ($addNumber) {
            $headings[] = 'No';
        }

        foreach ($fields as $field) {
            if (is_array($labels[$field] ?? null)) {
                foreach ($labels[$field] as $sub) {
                    $headings[] = $sub;
                }
            } else {
                $headings[] = $labels[$field] ?? ucwords(str_replace('_', ' ', $field));
            }
        }

        return $headings;
    }
}
