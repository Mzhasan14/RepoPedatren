<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\PesertaDidik\Fitur\KartuService;
use App\Http\Requests\PesertaDidik\KartuStoreRequest;
use App\Http\Requests\PesertaDidik\KartuUpdateRequest;

class KartuController extends Controller
{
    protected $service;

    public function __construct(KartuService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
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

    public function destroy($id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Kartu deleted successfully']);
    }
}
