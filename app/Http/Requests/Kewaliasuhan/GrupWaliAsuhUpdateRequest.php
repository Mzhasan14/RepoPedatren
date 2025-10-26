<?php

namespace App\Http\Requests\Kewaliasuhan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\DB;

class GrupWaliAsuhUpdateRequest extends FormRequest
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
            'nama_grup'     => 'sometimes|string|max:255',         // opsional, maksimal 255 karakter
            'id_wilayah'    => 'sometimes|integer|exists:wilayah,id', // opsional, harus ada di tabel wilayah
            'jenis_kelamin' => 'sometimes|in:l,p',                // opsional, hanya 'l' atau 'p'
            'wali_asuh_id'  => 'nullable|integer|exists:wali_asuh,id', // opsional, harus ada di tabel wali_asuh
            // 'status' tidak perlu karena update status tidak diizinkan
        ];
    }

    public function messages(): array
    {
        return [
            'nama_grup.string'       => 'Nama grup harus berupa teks.',
            'nama_grup.max'          => 'Nama grup maksimal 255 karakter.',
            'id_wilayah.integer'     => 'Wilayah tidak valid.',
            'id_wilayah.exists'      => 'Wilayah tidak ditemukan.',
            'jenis_kelamin.in'       => 'Jenis kelamin grup hanya boleh L (laki-laki) atau P (perempuan).',
            'wali_asuh_id.integer'   => 'Wali asuh tidak valid.',
            'wali_asuh_id.exists'    => 'Wali asuh tidak ditemukan.',
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
