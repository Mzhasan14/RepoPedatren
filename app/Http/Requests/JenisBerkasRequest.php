<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class JenisBerkasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // bisa diganti dengan pengecekan permission
    }

    public function rules(): array
    {
        $id = $this->route('jenis_berka'); // ambil ID untuk update

        return [
            'nama_jenis_berkas' => [
                'required',
                'string',
                'max:255',
                Rule::unique('jenis_berkas', 'nama_jenis_berkas')->ignore($id),
            ],
            'status' => ['required', 'boolean'],
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
