<?php

namespace App\Http\Requests\Kewaliasuhan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class KeluarAnakasuhRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ubah sesuai kebutuhan otorisasi
    }

    public function rules(): array
    {
        return [
            'tanggal_berakhir' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_berakhir.required' => 'Tanggal keluar wajib diisi.',
            'tanggal_berakhir.date' => 'Format tanggal keluar tidak valid.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Validasi gagal. Mohon periksa kembali input Anda.',
            'errors' => $errors,               // akan berisi detail per‐field
        ], 422);

        throw new HttpResponseException($response);
    }
}
