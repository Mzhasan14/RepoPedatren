<?php

namespace App\Http\Requests\Administrasi;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateCatatanAfektifRequest extends FormRequest
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
            'id_anak_asuh' => 'required|exists:anak_asuh,id',
            'kepedulian_nilai' => 'required|in:A,B,C,D,E',
            'kepedulian_tindak_lanjut' => 'required|string',
            'kebersihan_nilai' => 'required|in:A,B,C,D,E',
            'kebersihan_tindak_lanjut' => 'required|string',
            'akhlak_nilai' => 'required|in:A,B,C,D,E',
            'akhlak_tindak_lanjut' => 'required|string',
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
            'id_santri.required' => 'Santri wajib dipilih.',
            'id_santri.exists' => 'Santri yang dipilih tidak ditemukan.',

            'id_wali_asuh.required' => 'Wali asuh wajib dipilih.',
            'id_wali_asuh.exists' => 'Wali asuh yang dipilih tidak ditemukan.',

            'kepedulian_nilai.required' => 'Nilai kepedulian wajib diisi.',
            'kepedulian_nilai.in' => 'Nilai kepedulian harus berupa salah satu dari: A, B, C, D, atau E.',
            'kepedulian_tindak_lanjut.required' => 'Tindak lanjut kepedulian wajib diisi.',
            'kepedulian_tindak_lanjut.string' => 'Tindak lanjut kepedulian harus berupa teks.',

            'kebersihan_nilai.required' => 'Nilai kebersihan wajib diisi.',
            'kebersihan_nilai.in' => 'Nilai kebersihan harus berupa salah satu dari: A, B, C, D, atau E.',
            'kebersihan_tindak_lanjut.required' => 'Tindak lanjut kebersihan wajib diisi.',
            'kebersihan_tindak_lanjut.string' => 'Tindak lanjut kebersihan harus berupa teks.',

            'akhlak_nilai.required' => 'Nilai akhlak wajib diisi.',
            'akhlak_nilai.in' => 'Nilai akhlak harus berupa salah satu dari: A, B, C, D, atau E.',
            'akhlak_tindak_lanjut.required' => 'Tindak lanjut akhlak wajib diisi.',
            'akhlak_tindak_lanjut.string' => 'Tindak lanjut akhlak harus berupa teks.',

            'tanggal_buat.date' => 'Tanggal buat harus berupa tanggal yang valid.',
        ];
    }
}
