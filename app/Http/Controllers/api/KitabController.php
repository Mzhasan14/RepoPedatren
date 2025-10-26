<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\KitabRequest;
use App\Services\PesertaDidik\Fitur\KitabService;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class KitabController extends Controller
{
    protected KitabService $kitabService;

    public function __construct(KitabService $kitabService)
    {
        $this->kitabService = $kitabService;
    }
    public function indexAll()
    {
        try {
            $kitabs = $this->kitabService->getAllKitabs();
            return response()->json($kitabs);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Gagal mengambil semua data kitab', 'details' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $kitab = $this->kitabService->show($id);
            if ($kitab) {
                return response()->json($kitab);
            }
            return response()->json(['message' => 'Kitab tidak ditemukan'], 404);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Gagal mengambil data kitab', 'details' => $e->getMessage()], 500);
        }
    }

    public function store(KitabRequest $request)
    {
        try {
            $validated = $request->validated();
            $result = $this->kitabService->createKitab($validated);

            if ($result['success']) {
                return response()->json(['message' => $result['message'], 'id' => $result['id']], 201);
            } else {
                return response()->json(['message' => $result['message']], 400);
            }
        } catch (QueryException $e) {
            return response()->json(['error' => 'Gagal membuat kitab', 'details' => $e->getMessage()], 500);
        }
    }


    public function update(KitabRequest $request, $id)
    {
        try {
            $validated = $request->validated();

            $result = $this->kitabService->updateKitab($id, $validated);

            if ($result['success']) {
                return response()->json(['message' => $result['message']]);
            } else {
                return response()->json(['message' => $result['message']], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal mengupdate kitab',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id, Request $request)
    {
        try {
            $userId = $request->user()->id ?? null;

            $result = $this->kitabService->deactivateKitab($id, $userId);

            if ($result['success']) {
                return response()->json(['message' => $result['message']]);
            } else {
                return response()->json(['message' => $result['message']], 404);
            }
        } catch (QueryException $e) {
            return response()->json(['error' => 'Gagal menonaktifkan kitab', 'details' => $e->getMessage()], 500);
        }
    }

    public function activate($id)
    {
        try {
            $result = $this->kitabService->activateKitab($id);

            if ($result['success']) {
                return response()->json(['message' => $result['message']]);
            } else {
                return response()->json(['message' => $result['message']], 404);
            }
        } catch (QueryException $e) {
            return response()->json(['error' => 'Gagal mengaktifkan kitab', 'details' => $e->getMessage()], 500);
        }
    }

}
