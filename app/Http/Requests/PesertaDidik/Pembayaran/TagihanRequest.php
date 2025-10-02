<?php

namespace App\Http\Requests\PesertaDidik\Pembayaran;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TagihanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Atur ke policy jika perlu
    }

    public function rules(): array
    {
        return [
            // 'kode_tagihan' => 'required|string|max:50|unique:tagihan,kode_tagihan,' . $this->id,
            'nama_tagihan' => 'required|string|max:150',
            'tipe'         => 'required|string',
            'periode'         => 'required|string',
            'nominal'      => 'required|numeric|min:0',
            'jatuh_tempo'  => 'nullable|date',
            'status'       => 'boolean',
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
