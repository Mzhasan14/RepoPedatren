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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\select;

class DropdownController extends Controller
{
    public function menuWilayahBlokKamar()
    {
        $wilayahList = DB::table('wilayah')
            ->where('status', 1)
            ->orderBy('nama_wilayah')
            ->get();

        $wilayahs = [];
        foreach ($wilayahList as $w) {
            $wilayahs[$w->id] = [
                'id' => $w->id,
                'nama_wilayah' => $w->nama_wilayah,
                'kategori' => $w->kategori ? strtolower($w->kategori) : null,
                'blok' => [],
            ];
        }

        // Ambil blok + kamar
        $data = DB::table('blok as b')
            ->rightJoin('wilayah as w', 'b.wilayah_id', '=', 'w.id')
            ->leftJoin('kamar as k', function ($join) {
                $join->on('k.blok_id', '=', 'b.id')
                    ->where('k.status', 1);
            })
            ->select(
                'w.id as wilayah_id',
                'w.nama_wilayah',
                'w.kategori as kategori_wilayah',
                'b.id as blok_id',
                'b.nama_blok',
                'k.id as kamar_id',
                'k.nama_kamar',
                'k.kapasitas as kapasitas_kamar'
            )
            ->where('w.status', 1)
            ->where(function ($q) {
                $q->whereNull('b.id')
                    ->orWhere('b.status', 1);
            })
            ->orderBy('w.nama_wilayah')
            ->orderBy('b.nama_blok')
            ->orderBy('k.nama_kamar')
            ->get();

        foreach ($data as $row) {
            // Skip jika wilayah tidak ada di inisialisasi
            if (!isset($wilayahs[$row->wilayah_id])) {
                continue;
            }

            // Tambahkan blok
            if (!is_null($row->blok_id) && !isset($wilayahs[$row->wilayah_id]['blok'][$row->blok_id])) {
                $wilayahs[$row->wilayah_id]['blok'][$row->blok_id] = [
                    'id' => $row->blok_id,
                    'wilayah_id' => $row->wilayah_id,
                    'nama_blok' => $row->nama_blok,
                    'kamar' => [],
                ];
            }

            // Tambahkan kamar
            if (!is_null($row->kamar_id)) {
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
                    'slot' => $sisaSlot,
                    'kapasitas' => $row->kapasitas_kamar,
                    'penghuni' => $jumlahPenghuni,
                ];
            }
        }

        // Rapikan array index
        $result = array_map(function ($item) {
            $item['blok'] = array_values($item['blok']);
            foreach ($item['blok'] as &$blok) {
                $blok['kamar'] = array_values($blok['kamar']);
            }
            return $item;
        }, array_values($wilayahs));

        return response()->json([
            'message' => 'Sukses ambil data',
            'wilayah' => $result,
        ]);
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
        $lembagaList = DB::table('lembaga')->where('status', 1)->get();

        $lembaga = [];
        foreach ($lembagaList as $l) {
            $lembaga[$l->id] = [
                'id' => $l->id,
                'nama_lembaga' => $l->nama_lembaga,
                'jurusan' => [],
            ];
        }

        $data = DB::table('jurusan')
            ->select(
                'lembaga.id as lembaga_id',
                'lembaga.nama_lembaga',
                'jurusan.id as jurusan_id',
                'jurusan.nama_jurusan',
                'kelas.id as kelas_id',
                'kelas.nama_kelas',
                'rombel.id as rombel_id',
                'rombel.nama_rombel'
            )
            ->rightJoin('lembaga', 'jurusan.lembaga_id', '=', 'lembaga.id')
            ->leftJoin('kelas', function ($join) {
                $join->on('kelas.jurusan_id', '=', 'jurusan.id')
                    ->where('kelas.status', 1);
            })
            ->leftJoin('rombel', function ($join) {
                $join->on('rombel.kelas_id', '=', 'kelas.id')
                    ->where('rombel.status', 1);
            })
            ->where('lembaga.status', 1)
            ->where(function ($query) {
                $query->whereNull('jurusan.id')
                    ->orWhere('jurusan.status', 1);
            })
            ->orderBy('lembaga.nama_lembaga')
            ->orderBy('jurusan.nama_jurusan')
            ->orderBy('kelas.nama_kelas')
            ->orderBy('rombel.nama_rombel')
            ->get();

        foreach ($data as $row) {
            // Skip jika lembaga tidak terdaftar (harusnya tidak terjadi)
            if (! isset($lembaga[$row->lembaga_id])) {
                continue;
            }

            // Tambahkan jurusan jika ada dan belum dimasukkan
            if (! is_null($row->jurusan_id) && ! isset($lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id])) {
                $lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id] = [
                    'id' => $row->jurusan_id,
                    'nama_jurusan' => $row->nama_jurusan,
                    'kelas' => [],
                ];
            }

            // Tambahkan kelas jika ada dan belum dimasukkan
            if (! is_null($row->kelas_id) && ! isset($lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id]['kelas'][$row->kelas_id])) {
                $lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id]['kelas'][$row->kelas_id] = [
                    'id' => $row->kelas_id,
                    'nama_kelas' => $row->nama_kelas,
                    'rombel' => [],
                ];
            }

            // Tambahkan rombel jika ada
            if (! is_null($row->rombel_id)) {
                $lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id]['kelas'][$row->kelas_id]['rombel'][] = [
                    'id' => $row->rombel_id,
                    'nama_rombel' => $row->nama_rombel,
                ];
            }
        }

        // Hapus indeks angka pada array jurusan dan kelas
        $result = array_map(function ($item) {
            $item['jurusan'] = array_values($item['jurusan']);
            foreach ($item['jurusan'] as &$jurusan) {
                $jurusan['kelas'] = array_values($jurusan['kelas']);
                foreach ($jurusan['kelas'] as &$kelas) {
                    $kelas['rombel'] = array_values($kelas['rombel']);
                }
            }
            return $item;
        }, array_values($lembaga));

        return response()->json([
            'message' => 'Sukses ambil data',
            'lembaga' => $result,
        ]);
    }

    public function getAngkatan()
    {
        // Ambil angkatan pelajar
        $angkatanPelajar = DB::table('angkatan')
            ->select('id', 'angkatan')
            ->where('kategori', 'pelajar')
            ->where('status', 1)
            ->whereNull('deleted_at')
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
            ->whereNull('deleted_at')
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
    public function anakasuhcatatan(Request $request)
    {
        $user = $request->user();

        $query = DB::table('anak_asuh as aa')
            ->join('grup_wali_asuh as g', 'aa.grup_wali_asuh_id', '=', 'g.id')
            ->leftJoin('santri as s', 'aa.id_santri', '=', 's.id')
            ->leftJoin('biodata as b', 'b.id', '=', 's.biodata_id')
            ->leftJoin('pendidikan AS pd', function ($j) {
                $j->on('b.id', '=', 'pd.biodata_id')
                    ->where('pd.status', 'aktif');
            })
            ->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
            ->leftJoin('domisili_santri AS ds', function ($join) {
                $join->on('s.id', '=', 'ds.santri_id')
                    ->where('ds.status', 'aktif');
            })
            ->leftJoin('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
            ->leftJoin('kamar AS kk', 'ds.kamar_id', '=', 'kk.id')
            ->select([
                'aa.id',
                'b.nama',
                'l.nama_lembaga',
                'w.nama_wilayah',
                'kk.nama_kamar',
                's.nis'
            ]);

        // Filter role wali_asuh
        if ($user->hasRole('wali_asuh')) {
            // Ambil ID wali_asuh dari user login
            $waliAsuhId = DB::table('wali_asuh as wa')
                ->join('santri as s', 's.id', '=', 'wa.id_santri')
                ->join('biodata as b', 'b.id', '=', 's.biodata_id')
                ->join('users as u', 'u.biodata_id', '=', 'b.id')
                ->where('u.id', $user->id)
                ->value('wa.id');

            // Filter anak_asuh yang tergabung di grup milik wali_asuh ini
            $query->where('g.wali_asuh_id', $waliAsuhId);
        }

        $data = $query->get();

        return response()->json($data);
    }

    public function hubungkanwaliasuh()
    {
        $query = DB::table('santri as s')
            ->leftJoin('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftJoin('pendidikan AS pd', function ($j) {
                $j->on('b.id', '=', 'pd.biodata_id')
                    ->where('pd.status', 'aktif');
            })
            ->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
            ->leftJoin('domisili_santri AS ds', function ($join) {
                $join->on('s.id', '=', 'ds.santri_id')
                    ->where('ds.status', 'aktif');
            })
            ->leftJoin('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
            ->leftJoin('kamar AS kk', 'ds.kamar_id', '=', 'kk.id')
            ->leftJoin('blok AS bk', 'ds.blok_id', '=', 'bk.id')

            // hanya cek relasi anak_asuh yang masih aktif
            ->leftJoin('anak_asuh as aa', function ($join) {
                $join->on('s.id', '=', 'aa.id_santri')
                    ->where('aa.status', true);
            })
            // hanya cek relasi wali_asuh yang masih aktif
            ->leftJoin('wali_asuh as wa', function ($join) {
                $join->on('s.id', '=', 'wa.id_santri')
                    ->where('wa.status', true);
            })

            // tampilkan santri yang tidak sedang jadi anak asuh/wali asuh aktif
            ->whereNull('aa.id_santri')
            ->whereNull('wa.id_santri')
            ->where('s.status', 'aktif')
            ->select([
                's.id',
                'b.nama',
                'b.jenis_kelamin',
                'l.nama_lembaga',
                's.nis',
                'w.nama_wilayah',
                'kk.nama_kamar',
                'bk.nama_blok'
            ])
            ->get();

        return response()->json($query);
    }

    public function dropdownWaliAsuh(Request $request)
    {
        $query = DB::table('wali_asuh as ws')
            ->join('grup_wali_asuh as gw', function ($join) {
                $join->on('gw.wali_asuh_id', '=', 'ws.id')
                    ->where('gw.status', true); // hanya grup aktif
            })
            ->join('santri as s', 's.id', '=', 'ws.id_santri')
            ->join('biodata as b', 'b.id', '=', 's.biodata_id')
            ->leftJoin(
                'domisili_santri AS ds',
                fn($j) =>
                $j->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif')
            )
            ->leftJoin('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
            ->leftJoin('blok AS bl', 'ds.blok_id', '=', 'bl.id')
            ->leftJoin('kamar AS km', 'ds.kamar_id', '=', 'km.id')
            ->where('ws.status', true) // hanya wali asuh aktif
            ->select([
                'ws.id',
                's.nis',
                'b.nama',
                'w.nama_wilayah',
                'bl.nama_blok',
                'km.nama_kamar',
                'gw.nama_grup',   // optional: nama grupnya
            ])
            ->get();

        return response()->json($query);
    }
}
