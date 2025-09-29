<?php

namespace App\Services\PesertaDidik\OrangTua;

use App\Models\Saldo;

class SaldoService
{
    public function saldo($request)
    {
        $saldo = Saldo::where('santri_id', $request['santri_id'])
            ->where('status', true)
            ->first();

        if (!$saldo) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Data saldo tidak di temukan'
            ];
        }

        return [
            'success' => true,
            'data' => $saldo,
            'message' => 'Saldo berhasil ditampilkan'
        ];
    }
}
