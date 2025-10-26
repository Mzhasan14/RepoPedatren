<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use App\Models\JadwalSholat;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\JadwalSholatRequest;
use App\Services\PesertaDidik\Fitur\JadwalSholatService;

class JadwalSholatController extends Controller
{
    public function __construct(private JadwalSholatService $service) {}

    public function index()
    {
        return response()->json($this->service->getAll());
    }

    public function store(JadwalSholatRequest $request)
    {
        return response()->json($this->service->create($request->validated()), 201);
    }

    public function show(JadwalSholat $jadwal_sholat)
    {
        return response()->json($jadwal_sholat->load('sholat'));
    }

    public function update(JadwalSholatRequest $request, JadwalSholat $jadwal_sholat)
    {
        return response()->json($this->service->update($jadwal_sholat, $request->validated()));
    }

    public function destroy(JadwalSholat $jadwal_sholat)
    {
        $this->service->delete($jadwal_sholat);
        return response()->json(['message' => 'Deleted']);
    }
}
