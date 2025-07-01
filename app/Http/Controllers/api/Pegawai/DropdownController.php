<?php

namespace App\Http\Controllers\api\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Catatan_afektif;
use App\Models\Catatan_kognitif;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\Pegawai\GolonganJabatan;
use App\Models\Pegawai\KategoriGolongan;
use App\Models\Pegawai\Pengurus;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;

class DropdownController extends Controller
{
    public function menuWilayahBlokKamar()
    {
        $query = DB::table('wilayah as w')
            ->leftJoin('blok as b', function ($join) {
                $join->on('w.id', '=', 'b.wilayah_id')
                    ->where('b.status', true);
            })
            ->leftJoin('kamar as k', function ($join) {
                $join->on('b.id', '=', 'k.blok_id')
                    ->where('k.status', true);
            })
            ->select(
                'w.id as wilayah_id',
                'w.nama_wilayah',
                'w.kategori as kategori_wilayah',
                'b.id as blok_id',
                'b.wilayah_id',
                'b.nama_blok',
                'k.id as kamar_id',
                'k.blok_id',
                'k.nama_kamar',
                'k.kapasitas as kapasitas_kamar'
            )
            ->where('w.status', true)
            ->orderBy('w.id')
            ->get();

        $wilayahs = [];

        foreach ($query as $row) {
            // Inisialisasi wilayah
            if (! isset($wilayahs[$row->wilayah_id])) {
                $wilayahs[$row->wilayah_id] = [
                    'id' => $row->wilayah_id,
                    'nama_wilayah' => $row->nama_wilayah,
                    'kategori' => $row->kategori_wilayah ? strtolower($row->kategori_wilayah) : null,
                    'blok' => [],
                ];
            }

            // Inisialisasi blok jika ada & status true
            if (! is_null($row->blok_id) && ! isset($wilayahs[$row->wilayah_id]['blok'][$row->blok_id])) {
                $wilayahs[$row->wilayah_id]['blok'][$row->blok_id] = [
                    'id' => $row->blok_id,
                    'wilayah_id' => $row->wilayah_id,
                    'nama_blok' => $row->nama_blok,
                    'kamar' => [],
                ];
            }

            // Tambahkan kamar jika ada & status true
            if (! is_null($row->kamar_id)) {
                $jumlahPenghuni = \App\Models\DomisiliSantri::where('kamar_id', $row->kamar_id)
                    ->where('status', 'aktif')
                    ->count();

                $sisaSlot = ($row->kapasitas_kamar !== null)
                    ? max($row->kapasitas_kamar - $jumlahPenghuni, 0)
                    : null;

                $wilayahs[$row->wilayah_id]['blok'][$row->blok_id]['kamar'][] = [
                    'id' => $row->kamar_id,
                    'id_blok' => $row->blok_id,
                    'nama_kamar' => $row->nama_kamar,
                    'slot' => $sisaSlot, // slot tersisa
                    'kapasitas' => $row->kapasitas_kamar,
                    'penghuni' => $jumlahPenghuni,
                ];
            }
        }

        // Konversi dan urutkan berdasarkan abjad
        $result = [
            'wilayah' => array_values(array_map(function ($wilayah) {
                // Urutkan kamar berdasarkan nama_kamar
                foreach ($wilayah['blok'] as &$blok) {
                    usort($blok['kamar'], function ($a, $b) {
                        return strcmp($a['nama_kamar'], $b['nama_kamar']);
                    });
                }
                // Urutkan blok berdasarkan nama_blok
                usort($wilayah['blok'], function ($a, $b) {
                    return strcmp($a['nama_blok'], $b['nama_blok']);
                });
                return $wilayah;
            }, $wilayahs)),
        ];

        // Urutkan wilayah berdasarkan nama_wilayah
        usort($result['wilayah'], function ($a, $b) {
            return strcmp($a['nama_wilayah'], $b['nama_wilayah']);
        });

        return response()->json($result);
    }



    //     public function menuWilayahBlokKamar()
    //     {
    //     $query = DB::table('wilayah as w')
    //                 ->leftJoin('blok as b', 'w.id', '=', 'b.wilayah_id')
    //                 ->leftJoin('kamar as k', 'b.id', '=', 'k.blok_id')
    //                 ->select(
    //                     'w.id as wilayah_id',
    //                     'w.nama_wilayah',
    //                     'b.id as blok_id',
    //                     'b.wilayah_id',
    //                     'b.nama_blok',
    //                     'k.id as kamar_id',
    //                     'k.blok_id',
    //                     'k.nama_kamar'
    //                 )
    //                 ->orderBy('w.id')
    //                 ->get();

    //     $wilayahs = [];

    //     foreach ($query as $row) {
    //         // Inisialisasi wilayah
    //         if (!isset($wilayahs[$row->wilayah_id])) {
    //             $wilayahs[$row->wilayah_id] = [
    //                 'id' => $row->wilayah_id,
    //                 'nama_wilayah' => $row->nama_wilayah,
    //                 'blok' => [],
    //             ];
    //         }

    //         // Inisialisasi blok
    //         if (!is_null($row->blok_id) && !isset($wilayahs[$row->wilayah_id]['blok'][$row->blok_id])) {
    //             $wilayahs[$row->wilayah_id]['blok'][$row->blok_id] = [
    //                 'id' => $row->blok_id,
    //                 'wilayah_id' => $row->wilayah_id,
    //                 'nama_blok' => $row->nama_blok,
    //                 'kamar' => [],
    //             ];
    //         }

    //         // Tambahkan kamar jika ada
    //         if (!is_null($row->kamar_id)) {
    //             $wilayahs[$row->wilayah_id]['blok'][$row->blok_id]['kamar'][] = [
    //                 'id' => $row->kamar_id,
    //                 'id_blok' => $row->blok_id,
    //                 'nama_kamar' => $row->nama_kamar,
    //             ];
    //         }
    //     }

    //     // Konversi struktur nested menjadi array numerik (tanpa key ID sebagai key array)
    //     $result = [
    //         'wilayah' => array_values(array_map(function ($wilayah) {
    //             $wilayah['blok'] = array_values(array_map(function ($blok) {
    //                 $blok['kamar'] = array_values($blok['kamar']);
    //                 return $blok;
    //             }, $wilayah['blok']));
    //             return $wilayah;
    //         }, $wilayahs)),
    //     ];

    //     return response()->json($result);
    // }
    // public function menuNegaraProvinsiKabupatenKecamatan()
    // {
    //     $data = DB::table('negara as n')
    //         ->leftJoin('provinsi as p', 'n.id', '=', 'p.negara_id')
    //         ->leftJoin('kabupaten as kb', 'p.id', '=', 'kb.provinsi_id')
    //         ->leftJoin('kecamatan as kc', 'kb.id', '=', 'kc.kabupaten_id')
    //         ->select(
    //             'n.id as negara_id', 'n.nama_negara',
    //             'p.id as provinsi_id', 'p.negara_id', 'p.nama_provinsi',
    //             'kb.id as kabupaten_id', 'kb.provinsi_id', 'kb.nama_kabupaten',
    //             'kc.id as kecamatan_id', 'kc.kabupaten_id', 'kc.nama_kecamatan'
    //         )
    //         ->orderBy('n.id')
    //         ->get();

    //     $negara = [];

    //     foreach ($data as $row) {
    //         if (!isset($negara[$row->negara_id])) {
    //             $negara[$row->negara_id] = [
    //                 'id' => $row->negara_id,
    //                 'nama_negara' => $row->nama_negara,
    //                 'provinsi' => [],
    //             ];
    //         }

    //         if (!is_null($row->provinsi_id) && !isset($negara[$row->negara_id]['provinsi'][$row->provinsi_id])) {
    //             $negara[$row->negara_id]['provinsi'][$row->provinsi_id] = [
    //                 'id' => $row->provinsi_id,
    //                 'negara_id' => $row->negara_id,
    //                 'nama_provinsi' => $row->nama_provinsi,
    //                 'kabupaten' => [],
    //             ];
    //         }

    //         if (!is_null($row->kabupaten_id) && !isset($negara[$row->negara_id]['provinsi'][$row->provinsi_id]['kabupaten'][$row->kabupaten_id])) {
    //             $negara[$row->negara_id]['provinsi'][$row->provinsi_id]['kabupaten'][$row->kabupaten_id] = [
    //                 'id' => $row->kabupaten_id,
    //                 'provinsi_id' => $row->provinsi_id,
    //                 'nama_kabupaten' => $row->nama_kabupaten,
    //                 'kecamatan' => [],
    //             ];
    //         }

    //         if (!is_null($row->kecamatan_id)) {
    //             $negara[$row->negara_id]['provinsi'][$row->provinsi_id]['kabupaten'][$row->kabupaten_id]['kecamatan'][] = [
    //                 'id' => $row->kecamatan_id,
    //                 'kabupaten_id' => $row->kabupaten_id,
    //                 'nama_kecamatan' => $row->nama_kecamatan,
    //             ];
    //         }
    //     }

    //     $result = [
    //         'negara' => array_values(array_map(function ($negaraItem) {
    //             $negaraItem['provinsi'] = array_values(array_map(function ($provinsi) {
    //                 $provinsi['kabupaten'] = array_values(array_map(function ($kabupaten) {
    //                     $kabupaten['kecamatan'] = array_values($kabupaten['kecamatan']);
    //                     return $kabupaten;
    //                 }, $provinsi['kabupaten']));
    //                 return $provinsi;
    //             }, $negaraItem['provinsi']));
    //             return $negaraItem;
    //         }, $negara)),
    //     ];

    //     return response()->json($result);
    // }

    public function menuNegaraProvinsiKabupatenKecamatan()
    {
        $rows = DB::table('negara as n')
            ->leftJoin('provinsi as p', 'n.id', '=', 'p.negara_id')
            ->leftJoin('kabupaten as kb', 'p.id', '=', 'kb.provinsi_id')
            ->leftJoin('kecamatan as kc', 'kb.id', '=', 'kc.kabupaten_id')
            ->select(
                'n.id as negara_id',
                'n.nama_negara',
                'p.id as provinsi_id',
                'p.nama_provinsi',
                'kb.id as kabupaten_id',
                'kb.nama_kabupaten',
                'kc.id as kecamatan_id',
                'kc.nama_kecamatan'
            )
            ->orderBy('n.id')
            ->get();

        $result = [];

        foreach ($rows as $row) {
            // Negara
            $negara = &$result[$row->negara_id];
            if (! isset($negara)) {
                $negara = [
                    'id' => $row->negara_id,
                    'nama_negara' => $row->nama_negara,
                    'provinsi' => [],
                ];
            }

            // Provinsi
            if ($row->provinsi_id) {
                $provinsi = &$negara['provinsi'][$row->provinsi_id];
                if (! isset($provinsi)) {
                    $provinsi = [
                        'id' => $row->provinsi_id,
                        'nama_provinsi' => $row->nama_provinsi,
                        'kabupaten' => [],
                    ];
                }

                // Kabupaten
                if ($row->kabupaten_id) {
                    $kabupaten = &$provinsi['kabupaten'][$row->kabupaten_id];
                    if (! isset($kabupaten)) {
                        $kabupaten = [
                            'id' => $row->kabupaten_id,
                            'nama_kabupaten' => $row->nama_kabupaten,
                            'kecamatan' => [],
                        ];
                    }

                    // Kecamatan
                    if ($row->kecamatan_id) {
                        $kabupaten['kecamatan'][] = [
                            'id' => $row->kecamatan_id,
                            'nama_kecamatan' => $row->nama_kecamatan,
                        ];
                    }
                }
            }
        }

        // Ubah semua map (associative) menjadi array biasa
        $final = array_values(array_map(function ($negara) {
            $negara['provinsi'] = array_values(array_map(function ($provinsi) {
                $provinsi['kabupaten'] = array_values(array_map(function ($kabupaten) {
                    $kabupaten['kecamatan'] = array_values($kabupaten['kecamatan']);

                    return $kabupaten;
                }, $provinsi['kabupaten']));

                return $provinsi;
            }, $negara['provinsi']));

            return $negara;
        }, $result));

        return response()->json(['negara' => $final]);
    }

    public function menuLembagaJurusanKelasRombel()
    {
        $data = DB::table('lembaga as l')
            ->leftJoin('jurusan as j', 'l.id', '=', 'j.lembaga_id')
            ->leftJoin('kelas as k', 'j.id', '=', 'k.jurusan_id')
            ->leftJoin('rombel as r', 'k.id', '=', 'r.kelas_id')
            ->select(
                'l.id as lembaga_id',
                'l.nama_lembaga',
                'j.id as jurusan_id',
                'j.lembaga_id',
                'j.nama_jurusan',
                'k.id as kelas_id',
                'k.jurusan_id',
                'k.nama_kelas',
                'r.id as rombel_id',
                'r.kelas_id',
                'r.nama_rombel'
            )
            ->orderBy('l.id') // Urutkan berdasarkan lembaga.id
            ->get();

        $lembaga = [];

        foreach ($data as $row) {
            if (! isset($lembaga[$row->lembaga_id])) {
                $lembaga[$row->lembaga_id] = [
                    'id' => $row->lembaga_id,
                    'nama_lembaga' => $row->nama_lembaga,
                    'jurusan' => [],
                ];
            }

            if (! is_null($row->jurusan_id) && ! isset($lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id])) {
                $lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id] = [
                    'id' => $row->jurusan_id,
                    'lembaga_id' => $row->lembaga_id,
                    'nama_jurusan' => $row->nama_jurusan,
                    'kelas' => [],
                ];
            }

            if (! is_null($row->kelas_id) && ! isset($lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id]['kelas'][$row->kelas_id])) {
                $lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id]['kelas'][$row->kelas_id] = [
                    'id' => $row->kelas_id,
                    'jurusan_id' => $row->jurusan_id,
                    'nama_kelas' => $row->nama_kelas,
                    'rombel' => [],
                ];
            }

            if (! is_null($row->rombel_id)) {
                $lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id]['kelas'][$row->kelas_id]['rombel'][] = [
                    'id' => $row->rombel_id,
                    'kelas_id' => $row->kelas_id,
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
        // Ambil angkatan pelajar
        $angkatanPelajar = DB::table('angkatan')
            ->select('id', 'angkatan')
            ->where('kategori', 'pelajar')
            ->where('status', 1)
            ->orderByRaw('SUBSTRING(angkatan, -4) DESC')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'label' => $item->angkatan,
                ];
            });

        // Ambil angkatan santri
        $angkatanSantri = DB::table('angkatan')
            ->select('id', 'angkatan')
            ->where('kategori', 'santri')
            ->where('status', 1)
            ->orderByRaw('SUBSTRING(angkatan, -4) DESC')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'label' => $item->angkatan,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'pelajar' => $angkatanPelajar,
                'santri' => $angkatanSantri,
            ],
        ]);
    }

    public function menuKategoriGolonganAndGolongan()
    {
        $query = KategoriGolongan::leftJoin('golongan', 'kategori_golongan.id', '=', 'golongan.kategori_golongan_id')
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
            if (! isset($kategoriGolongans[$row->kategoriGolongan_id])) {
                $kategoriGolongans[$row->kategoriGolongan_id] = [
                    'id' => $row->kategoriGolongan_id,
                    'kategoriGolongan_nama' => $row->kategoriGolongan_nama,
                    'golongan' => [],
                ];
            }

            if (! is_null($row->Golongan_id)) {
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
    public function menuKategoriGolonganGabungan()
    {
        $query = KategoriGolongan::leftJoin('golongan', 'kategori_golongan.id', '=', 'golongan.kategori_golongan_id')
            ->select(
                'kategori_golongan.id as kategoriGolongan_id',
                'kategori_golongan.nama_kategori_golongan as kategoriGolongan_nama',
                'golongan.id as golongan_id',
                'golongan.nama_golongan as golongan_nama'
            )
            ->whereNotNull('golongan.id') // hanya ambil data yang punya golongan
            ->orderBy('kategori_golongan.id')
            ->get();

        $combinedOptions = [];

        foreach ($query as $row) {
            $combinedOptions[] = [
                'id' => $row->golongan_id,
                'GolonganNama' => "{$row->kategoriGolongan_nama} - {$row->golongan_nama}"
            ];
        }

        return response()->json([
            'combined' => $combinedOptions
        ]);
    }
    public function getPeriodeOptions()
    {
        $periodeAfektif = Catatan_afektif::Active()
            ->selectRaw("DATE_FORMAT(tanggal_buat, '%Y-%m') as periode")
            ->groupBy('periode')
            ->orderBy('periode', 'desc')
            ->get()
            ->pluck('periode');

        $periodeKognitif = Catatan_kognitif::Active()
            ->selectRaw("DATE_FORMAT(tanggal_buat, '%Y-%m') as periode")
            ->groupBy('periode')
            ->orderBy('periode', 'desc')
            ->get()
            ->pluck('periode');

        return response()->json([
            'afektif' => $periodeAfektif,
            'kognitif' => $periodeKognitif,
        ]);
    }

    public function getSatuanKerja()
    {
        $satuanKerja = Pengurus::active()
            ->select('satuan_kerja')
            ->groupBy('satuan_kerja')
            ->orderBy('satuan_kerja')
            ->pluck('satuan_kerja')
            ->values(); // reset index ke 0,1,2...

        // Bangun array dengan id + nama_satuan_kerja
        $result = $satuanKerja->map(function ($item, $index) {
            return [
                'id' => $index + 1,
                'nama_satuan_kerja' => $item,
            ];
        });

        return response()->json($result);
    }

    public function getGolonganJabatan()
    {
        $golonganJabatan = GolonganJabatan::select('id', 'nama_golongan_jabatan')
            ->where('status', true)
            ->orderBy('nama_golongan_jabatan')
            ->get();

        $result = $golonganJabatan
            ->unique('nama_golongan_jabatan') // ambil satu data per nama
            ->values() // reset indeks
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_golongan_jabatan' => $item->nama_golongan_jabatan,
                ];
            });

        return response()->json($result);
    }

    public function nameWaliasuh()
    {
        $wali = Wali_asuh::join('santri', 'santri.id', '=', 'wali_asuh.id_santri')
            ->join('biodata', 'biodata.id', '=', 'santri.biodata_id')
            ->where('wali_asuh.status', 1)
            ->select([
                'wali_asuh.id',
                'biodata.nama',
            ])
            ->distinct()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $wali,
        ]);
    }
    public function semester()
    {
        $semesters = Semester::where('status', true)
                        ->select('id', 'semester')
                        ->get();

        return response()->json($semesters);
    }
}
