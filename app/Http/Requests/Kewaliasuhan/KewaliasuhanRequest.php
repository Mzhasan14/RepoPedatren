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
            'id_wilayah' => 'required|exists:wilayah,id',
            'nama_grup' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:l,p',

            'wali_asuh.id_santri' => 'required|exists:santri,id',

            'anak_asuh' => 'required|array|min:1',
            'anak_asuh.*.id_santri' => 'required|distinct|exists:santri,id',
        ];
    }

    public function messages(): array
    {
        return [
            'id_wilayah.required' => 'Wilayah harus dipilih.',
            'id_wilayah.exists' => 'Wilayah tidak valid.',
            'nama_grup.required' => 'Nama grup wajib diisi.',
            'nama_grup.string' => 'Nama grup harus berupa teks.',
            'nama_grup.max' => 'Nama grup maksimal 255 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin grup wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin grup harus Laki-laki (l) atau Perempuan (p).',

            'wali_asuh.id_santri.required' => 'Santri wali asuh harus dipilih.',
            'wali_asuh.id_santri.exists' => 'Santri wali asuh tidak valid.',

            'anak_asuh.required' => 'Minimal 1 anak asuh harus dipilih.',
            'anak_asuh.array' => 'Anak asuh harus berupa array.',
            'anak_asuh.min' => 'Minimal 1 anak asuh harus dipilih.',
            'anak_asuh.*.id_santri.required' => 'ID Santri anak asuh harus diisi.',
            'anak_asuh.*.id_santri.distinct' => 'Santri anak asuh tidak boleh duplikat.',
            'anak_asuh.*.id_santri.exists' => 'Santri anak asuh tidak valid.',
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
