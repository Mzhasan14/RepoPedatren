<?php

namespace App\Http\Requests\PesertaDidik\Transaksi;

use Illuminate\Foundation\Http\FormRequest;

class TopUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth & role dicek di service/controller
    }

    public function rules(): array
    {
        return [
            'nominal' => 'required|numeric|min:1000',
            'bukti_transfer' => 'required|file|mimes:jpg,png,pdf|max:2048',
        ];
    }
}
