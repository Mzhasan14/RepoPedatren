<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class PengajarResquest extends FormRequest
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
            'lembaga_id'   => 'required|exists:lembaga,id',
            'golongan_id'  => 'required|exists:golongan,id',
            'jabatan'      => 'nullable|string|max:255',

            'nama_materi' => 'nullable|array|min:1',
            'nama_materi.*' => 'nullable|string|max:255',

            'jumlah_menit' => 'nullable|array|min:1',
            'jumlah_menit.*' => 'nullable|integer|min:0',

            'tahun_masuk' => 'nullable|date',
            'tahun_akhir' => 'nullable|date',

            'tahun_masuk_materi_ajar' => 'nullable|array',
            'tahun_masuk_materi_ajar.*' => 'nullable|date',


            'tahun_akhir_materi_ajar' => 'nullable|array',
            'tahun_akhir_materi_ajar.*' => 'nullable|date',
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
