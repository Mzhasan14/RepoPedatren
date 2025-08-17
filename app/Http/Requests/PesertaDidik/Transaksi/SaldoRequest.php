<?php

namespace App\Http\Requests\PesertaDidik\Transaksi;

use Illuminate\Foundation\Http\FormRequest;

class SaldoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'santri_id' => 'required|exists:santri,id',
            'jumlah'    => 'required|numeric|min:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'Santri wajib dipilih.',
            'santri_id.exists'   => 'Santri tidak ditemukan.',
            'jumlah.required'    => 'Jumlah wajib diisi.',
            'jumlah.numeric'     => 'Jumlah harus angka.',
            'jumlah.min'         => 'Minimal transaksi Rp 1.000.',
        ];
    }
}
