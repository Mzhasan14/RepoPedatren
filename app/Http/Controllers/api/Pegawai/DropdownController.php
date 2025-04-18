<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Alamat\Kecamatan;
use App\Models\Catatan_afektif;
use App\Models\Kewilayahan\Kamar;
use App\Models\Pegawai\KategoriGolongan;
use App\Models\Pegawai\Pengajar;
use App\Models\Pelajar;
use App\Models\Pendidikan\Rombel;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DropdownController extends Controller
{
    public function menuWilayahBlokKamar()
    {
        $query = Kamar::rightJoin('blok as b', 'kamar.id_blok', '=', 'b.id')
                    ->rightJoin('wilayah as w', 'b.id_wilayah', '=', 'w.id')
                    ->select(
                            'w.id as wilayah_id',
                            'w.nama_wilayah',
                            'b.id as blok_id',
                            'b.id_wilayah',
                            'b.nama_blok',
                            'kamar.id as kamar_id',
                            'kamar.id_blok',
                            'kamar.nama_kamar'
                        )
                    ->orderBy('w.id')
                    ->get();

        $wilayahs = [];

        foreach ($query as $row) {
            if (!isset($wilayahs[$row->wilayah_id])) {
                $wilayahs[$row->wilayah_id] = [
                    'id' => $row->wilayah_id,
                    'nama_wilayah' => $row->nama_wilayah,
                    'blok' => [],
                ];
            }

            if (!is_null($row->blok_id) && !isset($wilayahs[$row->wilayah_id]['blok'][$row->blok_id])) {
                $wilayahs[$row->wilayah_id]['blok'][$row->blok_id] = [
                    'id' => $row->blok_id,
                    'id_wilayah' => $row->id_wilayah,
                    'nama_blok' => $row->nama_blok,
                    'kamar' => [],
                ];
            }

            if (!is_null($row->kamar_id)) {
                $wilayahs[$row->wilayah_id]['blok'][$row->blok_id]['kamar'][] = [
                    'id' => $row->kamar_id,
                    'id_blok' => $row->id_blok,
                    'nama_kamar' => $row->nama_kamar,
                ];
            }
        }

        $result = [
            'wilayah' => array_values(array_map(function ($wilayah) {
                $wilayah['blok'] = array_values($wilayah['blok']);
                return $wilayah;
            }, $wilayahs)),
        ];

        return response()->json($result);
    }
    public function menuNegaraProvinsiKabupatenKecamatan()
    {
        $data = Kecamatan::rightJoin('kabupaten as kb', 'kecamatan.id_kabupaten', '=', 'kb.id')
                            ->rightJoin('provinsi as p', 'kb.id_provinsi', '=', 'p.id')
                            ->rightJoin('negara as n', 'p.id_negara', '=', 'n.id')
            ->select(
                'n.id as negara_id', 'n.nama_negara',
                'p.id as provinsi_id', 'p.id_negara', 'p.nama_provinsi',
                'kb.id as kabupaten_id', 'kb.id_provinsi', 'kb.nama_kabupaten',
                'kecamatan.id as kecamatan_id', 'kecamatan.id_kabupaten', 'kecamatan.nama_kecamatan'
            )
            ->orderBy('n.id') // Urutkan berdasarkan kecamatan.id
            ->get();
    
        $negara = [];
    
        foreach ($data as $row) {
            if (!isset($negara[$row->negara_id])) {
                $negara[$row->negara_id] = [
                    'id' => $row->negara_id,
                    'nama_negara' => $row->nama_negara,
                    'provinsi' => [],
                ];
            }
    
            if (!is_null($row->provinsi_id) && !isset($negara[$row->negara_id]['provinsi'][$row->provinsi_id])) {
                $negara[$row->negara_id]['provinsi'][$row->provinsi_id] = [
                    'id' => $row->provinsi_id,
                    'id_negara' => $row->id_negara,
                    'nama_provinsi' => $row->nama_provinsi,
                    'kabupaten' => [],
                ];
            }
    
            if (!is_null($row->kabupaten_id) && !isset($negara[$row->negara_id]['provinsi'][$row->provinsi_id]['kabupaten'][$row->kabupaten_id])) {
                $negara[$row->negara_id]['provinsi'][$row->provinsi_id]['kabupaten'][$row->kabupaten_id] = [
                    'id' => $row->kabupaten_id,
                    'id_provinsi' => $row->id_provinsi,
                    'nama_kabupaten' => $row->nama_kabupaten,
                    'kecamatan' => [],
                ];
            }
    
            if (!is_null($row->kecamatan_id)) {
                $negara[$row->negara_id]['provinsi'][$row->provinsi_id]['kabupaten'][$row->kabupaten_id]['kecamatan'][] = [
                    'id' => $row->kecamatan_id,
                    'id_kabupaten' => $row->id_kabupaten,
                    'nama_kecamatan' => $row->nama_kecamatan,
                ];
            }
        }
    
        $result = [
            'negara' => array_values(array_map(function ($negaraItem) {
                $negaraItem['provinsi'] = array_values(array_map(function ($provinsi) {
                    $provinsi['kabupaten'] = array_values(array_map(function ($kabupaten) {
                        $kabupaten['kecamatan'] = array_values($kabupaten['kecamatan']);
                        return $kabupaten;
                    }, $provinsi['kabupaten']));
                    return $provinsi;
                }, $negaraItem['provinsi']));
                return $negaraItem;
            }, $negara)),
        ];
    
        return response()->json($result);
        }
        public function menuLembagaJurusanKelasRombel()
        {
            $data = Rombel::rightJoin('kelas as k', 'rombel.id_kelas', '=', 'k.id')
            ->rightJoin('jurusan as j', 'k.id_jurusan', '=', 'j.id')
            ->rightJoin('lembaga as l', 'j.id_lembaga', '=', 'l.id')
                ->select(
                    'l.id as lembaga_id',
                    'l.nama_lembaga',
                    'j.id as jurusan_id',
                    'j.id_lembaga',
                    'j.nama_jurusan',
                    'k.id as kelas_id',
                    'k.id_jurusan',
                    'k.nama_kelas',
                    'rombel.id as rombel_id',
                    'rombel.id_kelas',
                    'rombel.nama_rombel'
                )
                ->orderBy('l.id') // Urutkan berdasarkan lembaga.id
                ->get();
    
            $lembaga = [];
    
            foreach ($data as $row) {
                if (!isset($lembaga[$row->lembaga_id])) {
                    $lembaga[$row->lembaga_id] = [
                        'id' => $row->lembaga_id,
                        'nama_lembaga' => $row->nama_lembaga,
                        'jurusan' => [],
                    ];
                }
    
                if (!is_null($row->jurusan_id) && !isset($lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id])) {
                    $lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id] = [
                        'id' => $row->jurusan_id,
                        'id_lembaga' => $row->id_lembaga,
                        'nama_jurusan' => $row->nama_jurusan,
                        'kelas' => [],
                    ];
                }
    
                if (!is_null($row->kelas_id) && !isset($lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id]['kelas'][$row->kelas_id])) {
                    $lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id]['kelas'][$row->kelas_id] = [
                        'id' => $row->kelas_id,
                        'id_jurusan' => $row->id_jurusan,
                        'nama_kelas' => $row->nama_kelas,
                        'rombel' => [],
                    ];
                }
    
                if (!is_null($row->rombel_id)) {
                    $lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id]['kelas'][$row->kelas_id]['rombel'][] = [
                        'id' => $row->rombel_id,
                        'id_kelas' => $row->id_kelas,
                        'nama_rombel' => $row->nama_rombel,
                    ];
                }
            }
    
            $result = [
                'lembaga' => array_values(array_map(function ($lembagaItem) {
                    $lembagaItem['jurusan'] = array_values(array_map(function ($jurusan) {
                        $jurusan['kelas'] = array_values(array_map(function ($kelas) {
                            $kelas['rombel'] = array_values($kelas['rombel']);
                            return $kelas;
                        }, $jurusan['kelas']));
                        return $jurusan;
                    }, $lembagaItem['jurusan']));
                    return $lembagaItem;
                }, $lembaga)),
            ];
    
            return response()->json($result);
        }
     
        public function getAngkatan()
        {
            // Ambil angkatan masuk pelajar
            $angkatanMasukPelajar = Pelajar::selectRaw('YEAR(tanggal_masuk_pelajar) as tahun')
                ->groupBy('tahun')
                ->orderBy('tahun')
                ->get()
                ->map(function ($item) {
                    return [
                        'tahun' => $item->tahun,
                        'label' => 'Masuk Tahun ' . $item->tahun
                    ];
                });
    
            // Ambil angkatan masuk santri
            $angkatanMasukSantri = Santri::selectRaw('YEAR(tanggal_masuk_santri) as tahun')
                ->groupBy('tahun')
                ->orderBy('tahun')
                ->get()
                ->map(function ($item) {
                    return [
                        'tahun' => $item->tahun,
                        'label' => 'Masuk Tahun ' . $item->tahun
                    ];
                });
    
            // Ambil angkatan keluar pelajar
            $angkatanKeluarPelajar = Pelajar::selectRaw('YEAR(tanggal_keluar_pelajar) as tahun')
                ->whereNotNull('tanggal_keluar_pelajar')
                ->groupBy('tahun')
                ->orderBy('tahun')
                ->get()
                ->map(function ($item) {
                    return [
                        'tahun' => $item->tahun,
                        'label' => 'Keluar Tahun ' . $item->tahun
                    ];
                });
    
            // Ambil angkatan keluar santri
            $angkatanKeluarSantri =Santri::selectRaw('YEAR(tanggal_keluar_santri) as tahun')
                ->whereNotNull('tanggal_keluar_santri')
                ->groupBy('tahun')
                ->orderBy('tahun')
                ->get()
                ->map(function ($item) {
                    return [
                        'tahun' => $item->tahun,
                        'label' => 'Keluar Tahun ' . $item->tahun
                    ];
                });
    
            // Format response JSON
            return response()->json([
                'status' => 'success',
                'data' => [
                    'angkatan_masuk' => [
                        'pelajar' => $angkatanMasukPelajar,
                        'santri' => $angkatanMasukSantri
                    ],
                    'angkatan_keluar' => [
                        'pelajar' => $angkatanKeluarPelajar,
                        'santri' => $angkatanKeluarSantri
                    ]
                ]
            ]);
        }

        public function menuKategoriGolonganAndGolongan()
        {
            $query = KategoriGolongan::leftJoin('golongan','kategori_golongan.id','golongan.id_kategori_golongan')
                                    ->select(
                                        'kategori_golongan.id as kategoriGolongan_id',
                                        'kategori_golongan.nama_kategori_golongan as kategoriGolongan_nama',
                                        'golongan.id as Golongan_id',
                                        'golongan.nama_golongan as GolonganNama',
                                    )
                                    ->orderBy(
                                        'kategori_golongan.id'
                                    )
                                    ->get();
            $kategoriGolongans = [];
    
            foreach ($query as $row) {
                if (!isset($kategoriGolongans[$row->kategoriGolongan_id])) {
                    $kategoriGolongans[$row->kategoriGolongan_id] = [
                        'id' => $row->kategoriGolongan_id,
                        'kategoriGolongan_nama' => $row->kategoriGolongan_nama,
                        'golongan' => [], 
                    ];
                }
        
                if (!is_null($row->Golongan_id)) {
                    $kategoriGolongans[$row->kategoriGolongan_id]['golongan'][] = [
                        'id' => $row->Golongan_id,
                        'kategoriGolongan_id' => $row->kategoriGolongan_id,
                        'GolonganNama' => $row->GolonganNama,
                    ];
                }
        }
        $result = [
            'kategori_golongan' => array_values($kategoriGolongans), 
        ];
    
        return response()->json($result);
    }


    // Dropdown untuk Pengajar!!
    public function menuMateriAjar()
    {
        $query = Pengajar::leftJoin('materi_ajar', 'pengajar.id', '=', 'materi_ajar.id_pengajar')
                        ->select(
                            DB::raw('COUNT(DISTINCT materi_ajar.id) as total_materi')
                            )
                        ->groupBy('pengajar.id')
                        ->get();

    $materiAjar1 = $query->where('total_materi', 1)->count();
    $materiAjarLebihDari1 = $query->where('total_materi', '>', 1)->count();

    $result = [
        [
            'label' => 'Materi Ajar 1',
            'jumlah pengajar' => $materiAjar1
        ],
        [
            'label' => 'Materi Ajar Lebih dari 1',
            'jumlah pengajar' => $materiAjarLebihDari1
        ]
    ];

    return response()->json([
            'data' => $result
        ]);
    }

    public function getPeriodeOptions()
{
    $periodes = Catatan_afektif::Active()
        ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as periode")
        ->groupBy('periode')
        ->orderBy('periode', 'desc')
        ->get()
        ->pluck('periode');

    // Tambahkan opsi "Semua"
    $result = collect(['Semua'])->merge($periodes);

    return response()->json($result);
}
    // public function menuJenisKelamin($tipe)
    // {
    //     // Cek tipe entitas yang diminta
    //     $relasiTabel = match ($tipe) {
    //         'pengajar' => 'pengajar',
    //         'pengurus' => 'pengurus',
    //         'anak_pegawai' => 'anak_pegawai',
    //         'karyawan' => 'karyawan',
    //         'pegawai' => null, // Pegawai tidak perlu join dengan tabel lain
    //         default => null
    //     };
    
    //     if ($tipe === 'pegawai') {
    //         // Query untuk pegawai langsung
    //         $query = DB::table('pegawai')
    //             ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
    //             ->select(
    //                 'biodata.jenis_kelamin',
    //                 DB::raw('COUNT(DISTINCT pegawai.id) as jumlah_pegawai')
    //             )
    //             ->groupBy('biodata.jenis_kelamin')
    //             ->get();
    //     } elseif ($relasiTabel) {
    //         // Query untuk pengajar, pengurus, anak pegawai, dan karyawan
    //         $query = DB::table($relasiTabel)
    //             ->join('pegawai', "$relasiTabel.id_pegawai", '=', 'pegawai.id')
    //             ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
    //             ->select(
    //                 'biodata.jenis_kelamin',
    //                 DB::raw('COUNT(DISTINCT pegawai.id) as jumlah_pegawai')
    //             )
    //             ->groupBy('biodata.jenis_kelamin')
    //             ->get();
    //     } else {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Tipe relasi tidak valid'
    //         ], 400);
    //     }
    //     // Format hasil
    //     $result = $query->map(function ($item) {
    //         return [
    //             'jenis kelamin' => $item->jenis_kelamin === 'l' ? 'laki-laki' : 
    //                               ($item->jenis_kelamin === 'p' ? 'perempuan' : 'Tidak Diketahui'),
    //             'jumlah' => $item->jumlah_pegawai
    //         ];
    //     });
    
    //     return response()->json([
    //         'data' => $result
    //     ]);
    // }
}