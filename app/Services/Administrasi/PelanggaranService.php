<?php

namespace App\Services\Administrasi;

use App\Models\BerkasPelanggaran;
use App\Models\Pelanggaran;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PelanggaranService
{
    public function basePelanggaranQuery(Request $request)
    {
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        return DB::table('pelanggaran as pl')
            ->join('santri as s', 'pl.santri_id', '=', 's.id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftjoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
            ->leftjoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftjoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
            ->leftjoin('domisili_santri as ds', fn($j) => $j->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'))
            ->leftjoin('wilayah as w', 'ds.wilayah_id', '=', 'w.id')
            ->leftjoin('blok as bl', 'ds.blok_id', '=', 'bl.id')
            ->leftjoin('kamar as km', 'ds.kamar_id', '=', 'km.id')
            ->leftjoin('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
            ->leftJoin('lembaga as l', 'pd.lembaga_id', '=', 'l.id')
            ->leftJoin('users as pencatat', 'pl.created_by', '=', 'pencatat.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id');
    }

    public function getAllPelanggaran(Request $request, $fields = null)
    {
        $query = $this->basePelanggaranQuery($request);

        $fields = $fields ?? [
            'pl.id',
            'b.nama',
            'pv.nama_provinsi',
            'kb.nama_kabupaten',
            'kc.nama_kecamatan',
            'w.nama_wilayah',
            'bl.nama_blok',
            'km.nama_kamar',
            'l.nama_lembaga',
            'pl.status_pelanggaran',
            'pl.jenis_pelanggaran',
            'pl.jenis_putusan',
            'pl.diproses_mahkamah',
            'pl.keterangan',
            'pl.created_at',
            DB::raw("COALESCE(pencatat.name, '(AutoSystem)') as pencatat"),
            DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
        ];
        // JOIN default, ex: domisili, pendidikan, users

        return $query->select($fields);
    }

    public function formatData($results)
    {
        return collect($results->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'nama_santri' => $item->nama,
                'provinsi' => $item->nama_provinsi ?? '-',
                'kabupaten' => $item->nama_kabupaten ?? '-',
                'kecamatan' => $item->nama_kecamatan ?? '-',
                'wilayah' => $item->nama_wilayah ?? '-',
                'blok' => $item->nama_blok ?? '-',
                'kamar' => $item->nama_kamar ?? '-',
                'lembaga' => $item->nama_lembaga ?? '-',
                'status_pelanggaran' => $item->status_pelanggaran,
                'jenis_pelanggaran' => $item->jenis_pelanggaran,
                'jenis_putusan' => $item->jenis_putusan,
                'diproses_mahkamah' => (bool) $item->diproses_mahkamah,
                'keterangan' => $item->keterangan ?? '-',
                'pencatat' => $item->pencatat,
                'foto_profil' => url($item->foto_profil),
                'tgl_input' => Carbon::parse($item->created_at)
                    ->translatedFormat('d F Y H:i:s'),
            ];
        });
    }

    public function index(string $bioId): array
    {
        $pelanggaran = Pelanggaran::with('santri.biodata:id')
            ->whereHas('santri.biodata', fn($q) => $q->where('id', $bioId))
            ->latest()
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'status_pelanggaran' => $item->status_pelanggaran,
                'jenis_putusan' => $item->jenis_putusan,
                'jenis_pelanggaran' => $item->jenis_pelanggaran,
                'diproses_mahkamah' => $item->diproses_mahkamah,
                'keterangan' => $item->keterangan,
                'created_at' => $item->created_at->toDateTimeString(),
            ]);

        return ['status' => true, 'data' => $pelanggaran];
    }

    public function store(array $data, string $bioId): array
    {
        return DB::transaction(function () use ($data, $bioId) {
            $santri = Santri::where('biodata_id', $bioId)->latest()->first();

            if (! $santri) {
                return ['status' => false, 'message' => 'Santri tidak ditemukan untuk biodata ini'];
            }

            $pelanggaran = Pelanggaran::create([
                'santri_id' => $santri->id,
                'status_pelanggaran' => $data['status_pelanggaran'],
                'jenis_putusan' => $data['jenis_putusan'],
                'jenis_pelanggaran' => $data['jenis_pelanggaran'],
                'diproses_mahkamah' => $data['diproses_mahkamah'],
                'keterangan' => $data['keterangan'],
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ['status' => true, 'data' => $pelanggaran];
        });
    }

    public function show($id): array
    {
        $pelanggaran = Pelanggaran::find($id);

        if (! $pelanggaran) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return [
            'status' => true,
            'data' => [
                'id' => $pelanggaran->id,
                'status_pelanggaran' => $pelanggaran->status_pelanggaran,
                'jenis_putusan' => $pelanggaran->jenis_putusan,
                'jenis_pelanggaran' => $pelanggaran->jenis_pelanggaran,
                'diproses_mahkamah' => $pelanggaran->diproses_mahkamah,
                'keterangan' => $pelanggaran->keterangan,
            ],
        ];
    }

    public function update(array $data, string $id): array
    {
        return DB::transaction(function () use ($data, $id) {
            $pelanggaran = Pelanggaran::find($id);

            if (! $pelanggaran) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            $pelanggaran->update([
                'status_pelanggaran' => $data['status_pelanggaran'],
                'jenis_putusan' => $data['jenis_putusan'],
                'jenis_pelanggaran' => $data['jenis_pelanggaran'],
                'diproses_mahkamah' => $data['diproses_mahkamah'],
                'keterangan' => $data['keterangan'],
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            return ['status' => true, 'data' => $pelanggaran];
        });
    }

    public function addBerkasPelanggaran(array $data, int $id)
    {
        $pelanggaran = Pelanggaran::find($id);
        if (! $pelanggaran) {
            return [
                'status' => false,
                'message' => 'Pelanggaran tidak ditemukan',
            ];
        }

        $url = Storage::url($data['file_path']->store('berkas_pelanggaran', 'public'));

        $berkas = BerkasPelanggaran::create([
            'pelanggaran_id' => $id,
            'santri_id' => $pelanggaran->santri_id,
            'file_path' => $url,
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'status' => true,
            'data' => $berkas,
        ];
    }

    public function getExportPelanggaranQuery($fields, $request)
    {
        $query = $this->basePelanggaranQuery($request);

        // Join dinamis sesuai kebutuhan export
        if (in_array('wilayah', $fields) || in_array('blok', $fields) || in_array('kamar', $fields)) {
            $query->leftJoin('domisili_santri as ds2', fn($j) => $j->on('s.id', '=', 'ds2.santri_id')->where('ds2.status', 'aktif'))
                ->leftJoin('wilayah AS w2', 'ds2.wilayah_id', '=', 'w2.id')
                ->leftJoin('blok AS bl2', 'ds2.blok_id', '=', 'bl2.id')
                ->leftJoin('kamar AS km2', 'ds2.kamar_id', '=', 'km2.id');
        }
        if (
            in_array('lembaga', $fields) ||
            in_array('jurusan', $fields) ||
            in_array('kelas', $fields) ||
            in_array('rombel', $fields)
        ) {
            $query->leftJoin('pendidikan AS pd2', fn($j) => $j->on('b.id', '=', 'pd2.biodata_id')->where('pd2.status', 'aktif'))
                ->leftJoin('lembaga AS l2', 'pd2.lembaga_id', '=', 'l2.id')
                ->leftJoin('jurusan as j2', 'pd2.jurusan_id', '=', 'j2.id')
                ->leftJoin('kelas as kls2', 'pd2.kelas_id', '=', 'kls2.id')
                ->leftJoin('rombel as r2', 'pd2.rombel_id', '=', 'r2.id');
        }
        if (in_array('pencatat', $fields)) {
            $query->leftJoin('users as user_pencatat2', 'pl.created_by', '=', 'user_pencatat2.id');
        }

        $select = [];
        foreach ($fields as $field) {
            switch ($field) {
                case 'nama_santri':
                    $select[] = 'b.nama as nama_santri';
                    break;
                case 'nis':
                    $select[] = 's.nis';
                    break;
                case 'jenis_kelamin':
                    $select[] = 'b.jenis_kelamin';
                    break;
                case 'wilayah':
                    $select[] = 'w2.nama_wilayah';
                    break;
                case 'blok':
                    $select[] = 'bl2.nama_blok';
                    break;
                case 'kamar':
                    $select[] = 'km2.nama_kamar';
                    break;
                case 'lembaga':
                    $select[] = 'l2.nama_lembaga';
                    break;
                case 'jurusan':
                    $select[] = 'j2.nama_jurusan';
                    break;
                case 'kelas':
                    $select[] = 'kls2.nama_kelas';
                    break;
                case 'rombel':
                    $select[] = 'r2.nama_rombel';
                    break;
                case 'status_pelanggaran':
                    $select[] = 'pl.status_pelanggaran';
                    break;
                case 'jenis_pelanggaran':
                    $select[] = 'pl.jenis_pelanggaran';
                    break;
                case 'jenis_putusan':
                    $select[] = 'pl.jenis_putusan';
                    break;
                case 'diproses_mahkamah':
                    $select[] = 'pl.diproses_mahkamah';
                    break;
                case 'keterangan':
                    $select[] = 'pl.keterangan';
                    break;
                case 'pencatat':
                    $select[] = DB::raw("COALESCE(user_pencatat2.name, '(AutoSystem)') as pencatat");
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
            $i = 0;

            foreach ($fields as $field) {
                switch ($field) {
                    case 'nama_santri':
                        $data['Nama Santri'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'nis':
                        $data['NIS'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'jenis_kelamin':
                        $jk = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Jenis Kelamin'] = $jk === 'l' ? 'Laki-laki' : ($jk === 'p' ? 'Perempuan' : $jk);
                        break;
                    case 'wilayah':
                        $data['Wilayah'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'blok':
                        $data['Blok'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'kamar':
                        $data['Kamar'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
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
                    case 'status_pelanggaran':
                        $status = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Status Pelanggaran'] = match ($status) {
                            'proses' => 'Diproses',
                            'selesai' => 'Selesai',
                            default => $status,
                        };
                        break;
                    case 'jenis_pelanggaran':
                        $data['Jenis Pelanggaran'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'jenis_putusan':
                        $data['Jenis Putusan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'diproses_mahkamah':
                        $val = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Diproses Mahkamah'] = $val == 1 ? 'Ya' : ($val == 0 ? 'Tidak' : $val);
                        break;
                    case 'pencatat':
                        $data['Pencatat'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'keterangan':
                        $data['Keterangan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                }
            }
            return $data;
        })->values();
    }

    public function getFieldExportHeadings($fields, $addNumber = false)
    {
        $map = [
            'nama_santri' => 'Nama Santri',
            'nis' => 'NIS',
            'jenis_kelamin' => 'Jenis Kelamin',
            'wilayah' => 'Wilayah',
            'blok' => 'Blok',
            'kamar' => 'Kamar',
            'lembaga' => 'Lembaga',
            'jurusan' => 'Jurusan',
            'kelas' => 'Kelas',
            'rombel' => 'Rombel',
            'status_pelanggaran' => 'Status Pelanggaran',
            'jenis_pelanggaran' => 'Jenis Pelanggaran',
            'jenis_putusan' => 'Jenis Putusan',
            'diproses_mahkamah' => 'Diproses Mahkamah',
            'pencatat' => 'Pencatat',
            'keterangan' => 'Keterangan',
        ];
        $headings = [];
        foreach ($fields as $f) {
            $headings[] = $map[$f] ?? $f;
        }
        if ($addNumber) {
            array_unshift($headings, 'No');
        }

        return $headings;
    }

    // public function getExportPelanggaranQuery($fields, $request)
    // {
    //     $query = $this->basePelanggaranQuery($request);

    //     // Join tambahan jika diperlukan berdasarkan field
    //     if (in_array('wilayah', $fields) || in_array('blok', $fields) || in_array('kamar', $fields)) {
    //         $query->leftJoin('domisili_santri as ds2', fn($j) => $j->on('s.id', '=', 'ds2.santri_id')->where('ds2.status', 'aktif'))
    //             ->leftJoin('wilayah AS w2', 'ds2.wilayah_id', '=', 'w2.id')
    //             ->leftJoin('blok AS bl2', 'ds2.blok_id', '=', 'bl2.id')
    //             ->leftJoin('kamar AS km2', 'ds2.kamar_id', '=', 'km2.id');
    //     }
    //     if (in_array('lembaga', $fields)) {
    //         $query->leftJoin('pendidikan AS pd2', fn($j) => $j->on('b.id', '=', 'pd2.biodata_id')->where('pd2.status', 'aktif'))
    //             ->leftJoin('lembaga AS l2', 'pd2.lembaga_id', '=', 'l2.id');
    //     }
    //     if (in_array('provinsi', $fields) || in_array('kabupaten', $fields) || in_array('kecamatan', $fields)) {
    //         $query->leftJoin('provinsi as pv2', 'b.provinsi_id', '=', 'pv2.id')
    //             ->leftJoin('kabupaten as kb2', 'b.kabupaten_id', '=', 'kb2.id')
    //             ->leftJoin('kecamatan as kc2', 'b.kecamatan_id', '=', 'kc2.id');
    //     }
    //     if (in_array('pencatat', $fields)) {
    //         $query->leftJoin('users as pencatat2', 'pl.created_by', '=', 'pencatat2.id');
    //     }

    //     $select = [];
    //     foreach ($fields as $field) {
    //         switch ($field) {
    //             case 'nama_santri':
    //                 $select[] = 'b.nama';
    //                 break;
    //             case 'provinsi':
    //                 $select[] = 'pv2.nama_provinsi';
    //                 break;
    //             case 'kabupaten':
    //                 $select[] = 'kb2.nama_kabupaten';
    //                 break;
    //             case 'kecamatan':
    //                 $select[] = 'kc2.nama_kecamatan';
    //                 break;
    //             case 'wilayah':
    //                 $select[] = 'w2.nama_wilayah';
    //                 break;
    //             case 'blok':
    //                 $select[] = 'bl2.nama_blok';
    //                 break;
    //             case 'kamar':
    //                 $select[] = 'km2.nama_kamar';
    //                 break;
    //             case 'lembaga':
    //                 $select[] = 'l2.nama_lembaga';
    //                 break;
    //             case 'status_pelanggaran':
    //                 $select[] = 'pl.status_pelanggaran';
    //                 break;
    //             case 'jenis_pelanggaran':
    //                 $select[] = 'pl.jenis_pelanggaran';
    //                 break;
    //             case 'jenis_putusan':
    //                 $select[] = 'pl.jenis_putusan';
    //                 break;
    //             case 'diproses_mahkamah':
    //                 $select[] = 'pl.diproses_mahkamah';
    //                 break;
    //             case 'keterangan':
    //                 $select[] = 'pl.keterangan';
    //                 break;
    //             case 'pencatat':
    //                 $select[] = DB::raw("COALESCE(pencatat2.name, '(AutoSystem)') as pencatat");
    //                 break;
    //         }
    //     }

    //     return $query->select($select);
    // }

    // public function formatDataExportPelanggaran($results, $fields, $addNumber = false)
    // {
    //     return collect($results)->values()->map(function ($item, $idx) use ($fields, $addNumber) {
    //         $data = [];
    //         if ($addNumber) {
    //             $data['No'] = $idx + 1;
    //         }
    //         $itemArr = (array) $item;
    //         $i = 0;
    //         foreach ($fields as $field) {
    //             $value = $itemArr[array_keys($itemArr)[$i++]] ?? '';
    //             switch ($field) {
    //                 case 'nama':
    //                     $data['Nama'] = $value;
    //                     break;
    //                 case 'provinsi':
    //                     $data['Provinsi'] = $value;
    //                     break;
    //                 case 'kabupaten':
    //                     $data['Kabupaten'] = $value;
    //                     break;
    //                 case 'kecamatan':
    //                     $data['Kecamatan'] = $value;
    //                     break;
    //                 case 'wilayah':
    //                     $data['Wilayah'] = $value;
    //                     break;
    //                 case 'blok':
    //                     $data['Blok'] = $value;
    //                     break;
    //                 case 'kamar':
    //                     $data['Kamar'] = $value;
    //                     break;
    //                 case 'lembaga':
    //                     $data['Lembaga'] = $value;
    //                     break;
    //                 case 'status_pelanggaran':
    //                     $data['Status Pelanggaran'] = $value;
    //                     break;
    //                 case 'jenis_pelanggaran':
    //                     $data['Jenis Pelanggaran'] = $value;
    //                     break;
    //                 case 'jenis_putusan':
    //                     $data['Jenis Putusan'] = $value;
    //                     break;
    //                 case 'diproses_mahkamah':
    //                     $data['Diproses Mahkamah'] = $value ? 'Ya' : 'Tidak';
    //                     break;
    //                 case 'keterangan':
    //                     $data['Keterangan'] = $value;
    //                     break;
    //                 case 'pencatat':
    //                     $data['Pencatat'] = $value;
    //                     break;
    //             }
    //         }
    //         return $data;
    //     });
    // }

    // public function getFieldExportHeadingsPelanggaran($fields, $addNumber = false)
    // {
    //     $map = [
    //         'nama' => 'Nama',
    //         'provinsi' => 'Provinsi',
    //         'kabupaten' => 'Kabupaten',
    //         'kecamatan' => 'Kecamatan',
    //         'wilayah' => 'Wilayah',
    //         'blok' => 'Blok',
    //         'kamar' => 'Kamar',
    //         'lembaga' => 'Lembaga',
    //         'status_pelanggaran' => 'Status Pelanggaran',
    //         'jenis_pelanggaran' => 'Jenis Pelanggaran',
    //         'jenis_putusan' => 'Jenis Putusan',
    //         'diproses_mahkamah' => 'Diproses Mahkamah',
    //         'keterangan' => 'Keterangan',
    //         'pencatat' => 'Pencatat',
    //     ];
    //     $headings = [];
    //     foreach ($fields as $f) {
    //         $headings[] = $map[$f] ?? $f;
    //     }
    //     if ($addNumber) {
    //         array_unshift($headings, 'No');
    //     }
    //     return $headings;
    // }
}
