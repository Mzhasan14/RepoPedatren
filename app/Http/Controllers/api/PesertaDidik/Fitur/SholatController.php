<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use App\Models\Sholat;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\SholatRequest;
use App\Services\PesertaDidik\Fitur\SholatService;

class SholatController extends Controller
{
    public function __construct(private SholatService $service) {}

    public function index()
    {
        return response()->json($this->service->getAll());
    }

    public function store(SholatRequest $request)
    {
        return response()->json($this->service->create($request->validated()), 201);
    }

    public function show(Sholat $sholat)
    {
        return response()->json($sholat);
    }

    public function update(SholatRequest $request, Sholat $sholat)
    {
        return response()->json($this->service->update($sholat, $request->validated()));
    }

    public function destroy(Sholat $sholat)
    {
        $this->service->delete($sholat);
        return response()->json(['message' => 'Deleted']);
    }
}
