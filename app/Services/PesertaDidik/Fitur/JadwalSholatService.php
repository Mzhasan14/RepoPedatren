<?php

namespace App\Services\PesertaDidik\Fitur;

use App\Models\JadwalSholat;
use Illuminate\Support\Facades\Auth;

class JadwalSholatService
{
    public function getAll()
    {
        return JadwalSholat::with('sholat')
            ->orderBy('berlaku_mulai', 'desc')
            ->get();
    }

    public function create(array $data)
    {
        $data['created_by'] = Auth::id();
        return JadwalSholat::create($data);
    }

    public function update(JadwalSholat $jadwal, array $data)
    {
        $data['updated_by'] = Auth::id();
        $jadwal->update($data);
        return $jadwal;
    }

    public function delete(JadwalSholat $jadwal)
    {
        $jadwal->update(['deleted_by' => Auth::id()]);
        $jadwal->delete();
    }
}
