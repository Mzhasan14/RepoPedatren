<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePengajarRequest extends FormRequest
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
            'lembaga_id'   => 'nullable|exists:lembaga,id',
            'golongan_id'  => 'nullable|exists:golongan,id',
            'jabatan'      => 'nullable|string|max:255',
            'tahun_masuk'  => 'nullable|date',

            'materi_ajar' => 'nullable|array',
            'materi_ajar.*.id' => 'nullable|integer|exists:materi_ajar,id',
            'materi_ajar.*.nama_materi' => 'required_without:materi_ajar.*.id|string|max:255',
            'materi_ajar.*.tahun_masuk' => 'nullable|date',
            'materi_ajar.*.tahun_akhir_materi_ajar' => 'nullable|date',
            'materi_ajar.*.jumlah_menit' => 'required_without:materi_ajar.*.id|integer|min:0',
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
