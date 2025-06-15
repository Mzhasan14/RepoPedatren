<?php

namespace App\Http\Requests\Kewaliasuhan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PindahAnakasuhRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ubah sesuai kebutuhan
    }

    public function rules(): array
    {
        return [
            'id_wali_asuh' => ['required', 'exists:wali_asuh,id'],
            'tanggal_mulai' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_wali_asuh.required' => 'Wali asuh baru wajib dipilih.',
            'id_wali_asuh.exists' => 'Wali asuh tidak ditemukan.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date' => 'Format tanggal tidak valid.',
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
