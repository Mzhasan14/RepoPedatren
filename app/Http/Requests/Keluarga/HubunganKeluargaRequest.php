<?php

namespace App\Http\Requests\Keluarga;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class HubunganKeluargaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_status' => 'required|string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Validasi gagal. Mohon periksa kembali input Anda.',
            'errors'  => $errors,               // akan berisi detail per‐field
        ], 422);

        throw new HttpResponseException($response);
    }
}