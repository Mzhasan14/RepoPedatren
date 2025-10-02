<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\PesertaDidik\Fitur\KartuService;
use App\Http\Requests\PesertaDidik\KartuStoreRequest;
use App\Http\Requests\PesertaDidik\KartuUpdateRequest;
use App\Services\PesertaDidik\Filters\FilterKartuService;

class KartuController extends Controller
{
    protected $service;
    protected $filter;

    public function __construct(KartuService $service, FilterKartuService $filter)
    {
        $this->service = $service;
        $this->filter = $filter;
    }

    public function index(Request $request): JsonResponse
    {
        $query = $this->service->getAll($request);
        $query = $this->filter->filterKartuRFID($query, $request);

        $perPage = (int) $request->input('limit', 25);
        $currentPage = (int) $request->input('page', 1);

        $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
        return response()->json([
            'data' => $results->items(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total' => $results->total(),
            'last_page' => $results->lastPage(),
        ]);
    }

    public function show($id): JsonResponse
    {
        return response()->json($this->service->getById($id));
    }

    public function store(KartuStoreRequest $request): JsonResponse
    {
        return response()->json($this->service->create($request->validated()), 201);
    }

    public function update(KartuUpdateRequest $request, $id): JsonResponse
    {
        return response()->json($this->service->update($id, $request->validated()));
    }

    public function nonactive($id): JsonResponse
    {
        $this->service->nonactive($id);
        return response()->json(['message' => 'Kartu berhasil dinonaktifkan']);
    }
    public function destroy($id): JsonResponse
    {
        $this->service->destroy($id);
        return response()->json(['message' => 'Kartu berhasil dihapus']);
    }
    public function activate($id): JsonResponse
    {
        $this->service->activate($id);
        return response()->json(['message' => 'Kartu berhasil diaktifkan kembali']);
    }

    public function riwayatKartu($id): JsonResponse
    {
        $result = $this->service->RiwayatKartu($id);

        $statusCode = $result['code'];

        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null 
        ], $statusCode);
    }
}
