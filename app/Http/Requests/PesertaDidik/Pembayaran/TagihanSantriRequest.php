<?php

namespace App\Http\Requests\PesertaDidik\Pembayaran;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TagihanSantriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // pakai policy/role jika perlu
    }

    public function rules(): array
    {
        return [
            'tagihan_id' => 'required|exists:tagihan,id',
            'angkatan_id' => 'required|exists:angkatan,id',
            'santri_ids' => 'required|array',
            'santri_ids.*' => 'exists:santri,id',
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
