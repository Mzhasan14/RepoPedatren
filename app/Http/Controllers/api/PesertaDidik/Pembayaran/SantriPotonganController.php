<?php

namespace App\Http\Controllers\api\PesertaDidik\Pembayaran;

use Exception;
use Illuminate\Http\Request;
use App\Models\SantriPotongan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\Pembayaran\SantriPotonganRequest;

class SantriPotonganController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = SantriPotongan::with(['santri', 'potongan']);

            if ($request->filled('santri_id')) {
                $query->where('santri_id', $request->santri_id);
            }

            if ($request->filled('potongan_id')) {
                $query->where('potongan_id', $request->potongan_id);
            }

            $data = $query->get();
            return response()->json(['success' => true, 'data' => $data], 200);
        } catch (Exception $e) {
            Log::error('Failed to fetch santri_potongan', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar santri potongan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show(SantriPotongan $santriPotongan): JsonResponse
    {
        try {
            $santriPotongan->load(['santri', 'potongan']);
            return response()->json(['success' => true, 'data' => $santriPotongan], 200);
        } catch (Exception $e) {
            Log::error('Failed to fetch santri_potongan detail', ['id' => $santriPotongan->id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail santri potongan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function store(SantriPotonganRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $santriPotongan = SantriPotongan::create($request->validated());

            DB::commit();
            Log::info('SantriPotongan created', ['id' => $santriPotongan->id]);

            return response()->json(['success' => true, 'data' => $santriPotongan], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create santri_potongan', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat santri potongan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function update(SantriPotonganRequest $request, SantriPotongan $santriPotongan): JsonResponse
    {
        DB::beginTransaction();
        try {
            $santriPotongan->update($request->validated());

            DB::commit();
            Log::info('SantriPotongan updated', ['id' => $santriPotongan->id]);

            return response()->json(['success' => true, 'data' => $santriPotongan], 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update santri_potongan', ['id' => $santriPotongan->id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate santri potongan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(SantriPotongan $santriPotongan): JsonResponse
    {
        DB::beginTransaction();
        try {
            $santriPotongan->delete();

            DB::commit();
            Log::info('SantriPotongan deleted', ['id' => $santriPotongan->id]);

            return response()->json(['success' => true, 'message' => 'Santri potongan berhasil dihapus'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete santri_potongan', ['id' => $santriPotongan->id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus santri potongan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
