<?php

namespace App\Http\Controllers\api\keluarga;

use App\Models\Biodata;
use App\Models\Keluarga;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Keluarga\KeluargaRequest;
use Illuminate\Support\Facades\Auth;;
use App\Services\Keluarga\KeluargaService;

class KeluargaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private KeluargaService $service;

    public function __construct(KeluargaService $service)
    {
        $this->service = $service;
    }
    public function index($idBio)
    {
        // 1. Cari no_kk berdasarkan id_biodata yang dipilih
        $noKk = DB::table('keluarga')
            ->where('id_biodata', $idBio)
            ->value('no_kk');

        if (!$noKk) {
            return response()->json([
                'status' => false,
                'message' => 'Data keluarga tidak ditemukan'
            ], 404);
        }

        // 2. Ambil data keluarga berdasarkan no_kk yang ditemukan
        // Orang tua / wali
        $ortu = DB::table('keluarga as k')
            ->where('k.no_kk', $noKk)
            ->join('orang_tua_wali as ow', 'k.id_biodata', '=', 'ow.id_biodata')
            ->join('biodata as bo', 'ow.id_biodata', '=', 'bo.id')
            ->join('hubungan_keluarga as hk', 'ow.id_hubungan_keluarga', '=', 'hk.id')
            ->select([
                'k.id',
                'bo.nama',
                'bo.nik',
                DB::raw("hk.nama_status as status"),
                'ow.wali',
                'bo.id as id_biodata' // Tambahkan id_biodata untuk identifikasi
            ])
            ->get();

        // Ambil semua id_biodata yang sudah terdaftar sebagai orang tua
        $excluded = DB::table('orang_tua_wali')->pluck('id_biodata')->toArray();

        // Anak kandung
        $saudara = DB::table('keluarga as k')
            ->where('k.no_kk', $noKk)
            ->whereNotIn('k.id_biodata', $excluded)
            ->join('biodata as bs', 'k.id_biodata', '=', 'bs.id')
            ->select([
                'k.id',
                'bs.nama',
                'bs.nik',
                DB::raw("'Anak Kandung' as status"),
                DB::raw("NULL as wali"),
                'bs.id as id_biodata' // Tambahkan id_biodata untuk identifikasi
            ])
            ->get();

        // Gabungkan dan urutkan
        $anggota = $ortu->merge($saudara)->sortBy(function ($i) {
            $status = strtolower($i->status ?? '');
            return match ($status) {
                'ayah' => 1,
                'ibu' => 2,
                default => 3,
            };
        })->values();

        // Tambahkan penanda untuk biodata yang dipilih
        $anggota = $anggota->map(function ($item) use ($idBio) {
            $item->is_selected = ($item->id_biodata == $idBio);
            return $item;
        });

        // Cek dulu apakah anak kandung yang is_selected = true ada
        $adaAnakKandungTerpilih = $anggota->contains(function ($item) {
            return strtolower($item->status ?? '') === 'anak kandung' && ($item->is_selected ?? false);
        });

        return response()->json([
            'status' => true,
            'data' => [
                'no_kk' => $noKk,
                'relasi_keluarga' => $anggota->map(function ($i) use ($adaAnakKandungTerpilih) {
                    $status = $i->status;

                    if (
                        strtolower($status) === 'anak kandung' &&
                        !$i->is_selected &&
                        $adaAnakKandungTerpilih
                    ) {
                        $status = 'Saudara Kandung';
                    }

                    return [
                        'id_keluarga' => $i->id,
                        'nik' => $i->nik,
                        'nama' => $i->nama,
                        'status_keluarga' => $status,
                        'sebagai_wali' => $i->wali,
                        'is_selected' => $i->is_selected ?? false,
                    ];
                }),
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        try {
            $result = $this->service->show($id);

            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.'
                ], 200);
            }

            return response()->json([
                'message' => 'Detail data berhasil ditampilkan',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil detail khadam: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // /**
    //  * Update the specified resource in storage.
    //  */
     public function update(KeluargaRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $result = $this->service->update($validated, $id);

            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }

            return response()->json([
                'message' => 'Data berhasil diperbarui',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal update Keluarga: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function pindahKKbaru(KeluargaRequest $request, $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->service->pindahKKbaru($validated, $id);

            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }

            return response()->json([
                'message' => 'KK baru berhasil dibuat',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal pindah KK: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function pindahkanSeluruhKK(KeluargaRequest $request, $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->service->pindahkanSeluruhKk($validated, $id);

            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }

            return response()->json([
                'message' => 'KK baru berhasil dibuat',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal pindah KK: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getAllKeluarga()
    {
        try {
            // $data = DB::table('keluarga as k')
            //     ->join('biodata as b', 'k.id_biodata', '=', 'b.id')
            //     ->join('orang_tua_wali as ow','ow.id_biodata','=','b.id')
            //     ->join('hubungan_keluarga as h', 'ow.id_hubungan_keluarga', '=', 'h.id')
            //     ->select(
            //         'k.no_kk',
            //         'b.nik',
            //         'b.nama as nama',
            //         'h.nama_status as status_keluarga',
            //         'ow.wali'
            //     )
            //     ->orderBy('k.no_kk')
            //     ->get();

            // $grouped = collect($data)->groupBy('no_kk')->map(function ($items, $no_kk) {
            //     return [
            //         'no_kk' => $no_kk,
            //         'relasi keluarga' => $items->map(function ($item) {
            //             return [
            //                 'nik' => $item->nik,
            //                 'nama' => $item->nama,
            //                 'status_keluarga' => $item->status_keluarga,
            //                 'sebagai_wali' => $item->wali
            //             ];
            //         })->values()
            //     ];
            // })->values();

            // return response()->json([
            //     'status' => true,
            // ]);
            //     'data' => $grouped

            $noKks = DB::table('keluarga')->distinct()->pluck('no_kk');

            $data = $noKks->map(function ($noKk) {
                // Orang tua / wali
                $ortu = DB::table('keluarga as k')
                    ->where('k.no_kk', $noKk)
                    ->join('orang_tua_wali as ow', 'k.id_biodata', '=', 'ow.id_biodata')
                    ->join('biodata as bo', 'ow.id_biodata', '=', 'bo.id')
                    ->join('hubungan_keluarga as hk', 'ow.id_hubungan_keluarga', '=', 'hk.id')
                    ->select([
                        'k.id',
                        'bo.nama',
                        'bo.nik',
                        DB::raw("hk.nama_status as status"),
                        'ow.wali'
                    ])
                    ->get();

                // Ambil semua id_biodata yang sudah terdaftar sebagai orang tua
                $excluded = DB::table('orang_tua_wali')->pluck('id_biodata')->toArray();

                // Anak kandung
                $saudara = DB::table('keluarga as k')
                    ->where('k.no_kk', $noKk)
                    ->whereNotIn('k.id_biodata', $excluded)
                    ->join('biodata as bs', 'k.id_biodata', '=', 'bs.id')
                    ->select([
                        'k.id',
                        'bs.nama',
                        'bs.nik',
                        DB::raw("'Anak Kandung' as status"),
                        DB::raw("NULL as wali")
                    ])
                    ->get();

                // Gabungkan dan urutkan
                $anggota = $ortu->merge($saudara)->sortBy(function ($i) {
                    $status = strtolower($i->status ?? '');
                    return match ($status) {
                        'ayah_kandung' => 1,
                        'ibu_kandung' => 2,
                        default => 3,
                    };
                })->values();

                return [
                    'no_kk' => $noKk,
                    'relasi_keluarga' => $anggota->map(fn($i) => [
                        'id_keluarga' => $i->id,
                        'nik' => $i->nik,
                        'nama' => $i->nama,
                        'status_keluarga' => $i->status,
                        'sebagai_wali' => $i->wali,
                    ])
                ];
            });

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mendapatkan data keluarga: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'no_kk' => 'required|max:16',
    //         'status_wali' => 'nullable',
    //         'id_status_keluarga' => 'required',
    //         'created_by' => 'required',
    //         'status' => 'nullable'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $keluarga = Keluarga::create($validator->validated());
    //     return new PdResource(true, 'Data berhasil Ditambah', $keluarga);
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(string $id)
    // {
    //     $keluarga = Keluarga::findOrFail($id);
    //     return new PdResource(true, 'detail data', $keluarga);
    // }

    
    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(string $id)
    // {
    //     $keluarga = Keluarga::findOrFail($id);

    //     $keluarga->delete();
    //     return new PdResource(true, 'Data berhasil dihapus', null);
    // }


}
