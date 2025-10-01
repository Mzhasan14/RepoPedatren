<?php

namespace App\Http\Controllers\api\PesertaDidik\Pembayaran;

use Exception;
use App\Models\Potongan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\PesertaDidik\Pembayaran\PotonganService;
use App\Http\Requests\PesertaDidik\Pembayaran\PotonganRequest;

class PotonganController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $potongan = Potongan::with([
                'tagihans:id,nama_tagihan',
                'santris:id,biodata_id,nis',
                'santris.biodata:id,nama'
            ])->get([
                'id',
                'nama',
                'kategori',
                'jenis',
                'nilai',
                'status',
                'keterangan'
            ]);

            $potongan->transform(function ($p) {
                return [
                    'id'         => $p->id,
                    'nama'       => $p->nama,
                    'kategori'   => $p->kategori,
                    'jenis'      => $p->jenis,
                    'nilai'      => $p->nilai,
                    'status'     => $p->status,
                    'keterangan' => $p->keterangan,
                    'tagihans'   => $p->tagihans->map(function ($t) {
                        return [
                            'id'           => $t->id,
                            'nama_tagihan' => $t->nama_tagihan,
                        ];
                    }),
                    'santris'    => $p->santris->map(function ($s) {
                        return [
                            'id'   => $s->id,
                            'nis'  => $s->nis,
                            'nama' => $s->biodata->nama ?? null,
                        ];
                    }),
                ];
            });

            return response()->json(['success' => true, 'data' => $potongan], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar potongan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show(Potongan $potongan): JsonResponse
    {
        try {
            $potongan->load([
                'tagihans:id,nama_tagihan',
                'santris:id,biodata_id,nis',
                'santris.biodata:id,nama'
            ]);

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'         => $potongan->id,
                    'nama'       => $potongan->nama,
                    'kategori'   => $potongan->kategori,
                    'jenis'      => $potongan->jenis,
                    'nilai'      => $potongan->nilai,
                    'status'     => $potongan->status,
                    'keterangan' => $potongan->keterangan,
                    'tagihans'   => $potongan->tagihans->map(function ($tagihan) {
                        return [
                            'id'           => $tagihan->id,
                            'nama_tagihan' => $tagihan->nama_tagihan,
                        ];
                    }),
                    'santris'    => $potongan->santris->map(function ($santri) {
                        return [
                            'id'   => $santri->id,
                            'nis'  => $santri->nis,
                            'nama' => $santri->biodata->nama ?? null,
                        ];
                    }),
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail potongan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function store(PotonganRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $potongan = Potongan::create($data);

            if (!empty($data['tagihan_ids'])) {
                $potongan->tagihans()->sync($data['tagihan_ids']);
            }

            if ($potongan->kategori === 'umum' && !empty($data['santri_ids'])) {
                $syncData = [];
                foreach ($data['santri_ids'] as $santriId) {
                    $syncData[$santriId] = ['status' => true];
                }
                $potongan->santris()->sync($syncData);
            }

            DB::commit();

            $potongan->load([
                'tagihans:id,nama_tagihan,tipe,nominal',
                'santris:id,nama_lengkap,nis'
            ]);

            return response()->json(['success' => true, 'data' => $potongan], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat potongan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function update(PotonganRequest $request, Potongan $potongan): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $potongan->update($data);

            if (!empty($data['tagihan_ids'])) {
                $potongan->tagihans()->sync($data['tagihan_ids']);
            }

            if ($potongan->kategori === 'umum') {
                $syncData = [];
                foreach ($data['santri_ids'] ?? [] as $santriId) {
                    $syncData[$santriId] = ['status' => true];
                }
                $potongan->santris()->sync($syncData);
            } else {
                $potongan->santris()->detach();
            }

            DB::commit();

            $potongan->load([
                'tagihans:id,nama_tagihan,tipe,nominal',
                'santris:id,nama_lengkap,nis'
            ]);

            return response()->json(['success' => true, 'data' => $potongan], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate potongan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Potongan $potongan): JsonResponse
    {
        try {
            $potongan->delete();
            return response()->json(['success' => true, 'message' => 'Potongan berhasil dihapus'], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus potongan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus(Potongan $potongan): JsonResponse
    {
        try {
            $potongan->status = !$potongan->status;
            $potongan->save();

            return response()->json([
                'success' => true,
                'message' => 'Status potongan berhasil diubah',
                'data'    => $potongan->only(['id', 'nama_potongan', 'status'])
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status potongan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
