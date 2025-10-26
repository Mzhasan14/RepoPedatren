<?php

namespace App\Http\Requests\Administrasi;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateKategoriRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kategori' => 'required|in:akhlak,kepedulian,kebersihan',
            'nilai' => 'required|in:A,B,C,D,E',
            'tindak_lanjut' => 'required|string'
        ];
    }
    public function messages(): array
    {
        return [
            'kategori.in' => 'Kategori hanya boleh: akhlak, kepedulian, kebersihan.',
            'nilai.in' => 'Nilai harus berupa huruf A-E.',
            'tindak_lanjut.required' => 'Tindak lanjut wajib diisi.',
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
