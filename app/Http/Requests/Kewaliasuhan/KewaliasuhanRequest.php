<?php

namespace App\Http\Requests\Kewaliasuhan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class KewaliasuhanRequest extends FormRequest
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
            'wali_santri_id'   => 'required|integer|exists:santri,id',
            'anak_santri_ids'  => 'required|array|min:1',
            'anak_santri_ids.*' => 'integer|exists:santri,id|distinct',

            'nama_grup'        => 'required|string|max:255',
            'id_wilayah'       => 'required|integer|exists:wilayah,id',
            'jenis_kelamin'    => 'required|in:l,p',
        ];
    }

    public function messages(): array
    {
        return [
            'wali_santri_id.required' => 'Santri wali asuh wajib dipilih.',
            'wali_santri_id.exists'   => 'Santri wali asuh tidak ditemukan.',

            'anak_santri_ids.required' => 'Minimal pilih satu santri anak asuh.',
            'anak_santri_ids.array'    => 'Format anak asuh tidak valid.',
            'anak_santri_ids.*.exists' => 'Ada santri anak asuh yang tidak valid.',
            'anak_santri_ids.*.distinct' => 'Santri anak asuh tidak boleh duplikat.',

            'nama_grup.required' => 'Nama grup wajib diisi.',
            'id_wilayah.required' => 'Wilayah grup wajib dipilih.',
            'id_wilayah.exists' => 'Wilayah tidak ditemukan.',

            'jenis_kelamin.in' => 'Jenis kelamin grup hanya boleh "l" atau "p".',
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
