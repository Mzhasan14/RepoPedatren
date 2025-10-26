<?php

namespace App\Services\PesertaDidik\Fitur;

use App\Models\Sholat;
use Illuminate\Support\Facades\Auth;

class SholatService
{
    public function getAll()
    {
        return Sholat::orderByDesc('id')->get();
    }

    public function create(array $data)
    {
        $data['created_by'] = Auth::id();
        return Sholat::create($data);
    }

    public function update(Sholat $sholat, array $data)
    {
        $data['updated_by'] = Auth::id();
        $sholat->update($data);
        return $sholat;
    }

    public function delete(Sholat $sholat)
    {
        $sholat->update(['deleted_by' => Auth::id()]);
        $sholat->delete();
    }
}
