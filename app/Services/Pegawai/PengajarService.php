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
            ->leftJoin('lembaga as l', 'pengajar.lembaga_id', '=', 'l.id')
            ->leftJoin('golongan as g', 'pengajar.golongan_id', '=', 'g.id')
            ->leftJoin('kategori_golongan as kg', 'g.kategori_golongan_id', '=', 'kg.id')
            ->leftJoinSub($fotoLast, 'fl', fn ($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')

            // Mata pelajaran dan jadwal
            ->leftJoin('mata_pelajaran', function ($join) {
                $join->on('mata_pelajaran.pengajar_id', '=', 'pengajar.id')
                    ->where('mata_pelajaran.status', true);
            })
            ->leftJoin('jadwal_pelajaran', 'mata_pelajaran.id', '=', 'jadwal_pelajaran.mata_pelajaran_id')
            ->leftJoin('jam_pelajaran', 'jadwal_pelajaran.jam_pelajaran_id', '=', 'jam_pelajaran.id')

            ->whereNull('pengajar.tahun_akhir')
            ->where('pengajar.status_aktif', 'aktif');
    }
    public function getAllPengajar(Request $request)
    {
        $query = $this->basePengajarQuery($request);

        $fields = [
            'pengajar.id as pengajar_id',
            'pegawai.biodata_id as biodata_uuid',
            'b.nama',
            'wp.niup',
            DB::raw('TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur'),

            // Gabungan mapel dan totalnya
            DB::raw("GROUP_CONCAT(DISTINCT mata_pelajaran.nama_mapel SEPARATOR ', ') as daftar_materi"),
            DB::raw("COUNT(DISTINCT mata_pelajaran.id) AS total_materi"),

            // Masa kerja
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
            'pengajar.id',
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
            'pengajar_id' => $item->pengajar_id,
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
        $query = $this->basePengajarQuery($request);

        $select = [];
        $groupBy = [];

        foreach ($fields as $field) {
            switch ($field) {
                case 'nama_lengkap':
                    $select[] = 'b.nama as nama_lengkap';
                    $groupBy[] = 'b.nama';
                    break;

                case 'nik':
                    $select[] = DB::raw('COALESCE(b.nik, b.no_passport) as nik');
                    $groupBy[] = 'b.nik';
                    $groupBy[] = 'b.no_passport';
                    break;

                case 'niup':
                    $select[] = 'wp.niup';
                    $groupBy[] = 'wp.niup';
                    break;

                case 'jenis_kelamin':
                    $select[] = 'b.jenis_kelamin';
                    $groupBy[] = 'b.jenis_kelamin';
                    break;

                case 'tempat_lahir':
                case 'tanggal_lahir':
                    $select[] = "b.$field";
                    $groupBy[] = "b.$field";
                    break;

                case 'no_kk':
                    $select[] = 'k.no_kk';
                    $groupBy[] = 'k.no_kk';
                    $query->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata');
                    break;

                case 'jalan':
                    $select[] = 'b.jalan';
                    $select[] = 'kec.nama_kecamatan';
                    $select[] = 'kab.nama_kabupaten';
                    $select[] = 'prov.nama_provinsi';
                    $select[] = 'neg.nama_negara';

                    $groupBy[] = 'b.jalan';
                    $groupBy[] = 'kec.nama_kecamatan';
                    $groupBy[] = 'kab.nama_kabupaten';
                    $groupBy[] = 'prov.nama_provinsi';
                    $groupBy[] = 'neg.nama_negara';

                    $query->leftJoin('kecamatan as kec', 'kec.id', '=', 'b.kecamatan_id')
                        ->leftJoin('kabupaten as kab', 'kab.id', '=', 'b.kabupaten_id')
                        ->leftJoin('provinsi as prov', 'prov.id', '=', 'b.provinsi_id')
                        ->leftJoin('negara as neg', 'neg.id', '=', 'b.negara_id');
                    break;

                case 'pendidikan_terakhir':
                    $select[] = 'b.jenjang_pendidikan_terakhir as jenjang_pendidikan_terakhir';
                    $select[] = 'b.nama_pendidikan_terakhir as nama_pendidikan_terakhir';

                    $groupBy[] = 'b.jenjang_pendidikan_terakhir';
                    $groupBy[] = 'b.nama_pendidikan_terakhir';
                    break;

                case 'email':
                case 'no_telepon':
                    $select[] = "b.$field";
                    $groupBy[] = "b.$field";
                    break;

                case 'lembaga':
                    $select[] = 'l.nama_lembaga';
                    $groupBy[] = 'l.nama_lembaga';
                    break;

                case 'golongan':
                    $select[] = 'g.nama_golongan';
                    $groupBy[] = 'g.nama_golongan';
                    break;

                case 'jabatan':
                    $select[] = 'pengajar.jabatan';
                    $groupBy[] = 'pengajar.jabatan';
                    break;
                case 'tanggal_mulai':
                    $select[] = 'pengajar.tahun_masuk as tanggal_mulai';
                    $groupBy[] = 'pengajar.tahun_masuk';
                    break;
            }
        }

        // Tambahkan ID sebagai groupBy default
        $groupBy[] = 'pengajar.id';

        return $query->select($select)->groupBy($groupBy);
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
                        $alamat = [
                            $itemArr[$i++] ?? '', // jalan
                            $itemArr[$i++] ?? '', // kecamatan
                            $itemArr[$i++] ?? '', // kabupaten
                            $itemArr[$i++] ?? '', // provinsi
                            $itemArr[$i++] ?? '', // negara
                        ];
                        $data['Alamat'] = implode(', ', array_filter($alamat, fn($val) => trim($val) !== ''));
                        break;
                    case 'pendidikan_terakhir':
                        $data['Jenjang Pendidikan Terakhir'] = $itemArr[$i++] ?? '';
                        $data['Nama Pendidikan Terakhir'] = $itemArr[$i++] ?? '';
                        break;
                    case 'tanggal_mulai':
                        $tgl = $itemArr[$i++] ?? '';
                        $data['Tanggal Mulai'] = $tgl ? Carbon::parse($tgl)->format('d-m-Y') : '';
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
            'jalan' => 'Alamat',
            'tanggal_mulai' => 'Tanggal Mulai',
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
