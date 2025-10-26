<?php

namespace App\Http\Requests\Administrasi\Formulir\Catatan;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAfektifRequest extends FormRequest
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
            'kepedulian_nilai' => 'required|string|max:255',
            'kepedulian_tindak_lanjut' => 'required|string|max:255',
            'kebersihan_nilai' => 'required|string|max:255',
            'kebersihan_tindak_lanjut' => 'required|string|max:255',
            'akhlak_nilai' => 'required|string|max:255',
            'akhlak_tindak_lanjut' => 'required|string|max:255',
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
    public function messages(): array
    {
        return [
            'id_wali_asuh.required' => 'Wali asuh wajib dipilih.',
            'id_wali_asuh.exists'   => 'Wali asuh yang dipilih tidak ditemukan atau tidak valid.',

            'kepedulian_nilai.string'         => 'Nilai kepedulian harus berupa teks.',
            'kepedulian_nilai.max'            => 'Nilai kepedulian tidak boleh lebih dari 255 karakter.',
            'kepedulian_tindak_lanjut.string' => 'Tindak lanjut kepedulian harus berupa teks.',
            'kepedulian_tindak_lanjut.max'    => 'Tindak lanjut kepedulian tidak boleh lebih dari 255 karakter.',

            'kebersihan_nilai.string'         => 'Nilai kebersihan harus berupa teks.',
            'kebersihan_nilai.max'            => 'Nilai kebersihan tidak boleh lebih dari 255 karakter.',
            'kebersihan_tindak_lanjut.string' => 'Tindak lanjut kebersihan harus berupa teks.',
            'kebersihan_tindak_lanjut.max'    => 'Tindak lanjut kebersihan tidak boleh lebih dari 255 karakter.',

            'akhlak_nilai.string'         => 'Nilai akhlak harus berupa teks.',
            'akhlak_nilai.max'            => 'Nilai akhlak tidak boleh lebih dari 255 karakter.',
            'akhlak_tindak_lanjut.string' => 'Tindak lanjut akhlak harus berupa teks.',
            'akhlak_tindak_lanjut.max'    => 'Tindak lanjut akhlak tidak boleh lebih dari 255 karakter.',

            'tanggal_buat.date' => 'Tanggal buat harus berupa tanggal yang valid.',
        ];
    }
}
