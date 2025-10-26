<?php

namespace App\Http\Requests\PesertaDidik\Transaksi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'metode' => 'required|in:scan,manual',
            'santri_id' => 'required|exists:santri,id',
            'jumlah'    => 'required|numeric|min:1000',
            'pin'       => 'nullable|string|min:4|max:6',
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'Santri wajib dipilih.',
            'pin.required' => 'Pin wajib di isi.',
            'pin.min' => 'Pin minimal 4 angka.',
            'pin.max' => 'Pin maksimal 4 angka.',
            'santri_id.exists'   => 'Santri tidak ditemukan.',
            'jumlah.required'    => 'Jumlah wajib diisi.',
            'jumlah.numeric'     => 'Jumlah harus angka.',
            'jumlah.min'         => 'Minimal transaksi Rp 1.000.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Validasi gagal. Mohon periksa kembali input Anda.',
            'error' => $errors,
        ], 422);

        throw new HttpResponseException($response);
    }
}
