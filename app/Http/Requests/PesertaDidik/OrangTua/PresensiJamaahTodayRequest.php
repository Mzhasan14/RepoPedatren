<?php

namespace App\Http\Requests\PesertaDidik\OrangTua;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PresensiJamaahTodayRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'santri_id'     => ['required', 'integer', 'exists:santri,id'],
            'tanggal'       => ['nullable', 'date'],
            'sholat_id'     => ['nullable', 'integer', 'exists:sholat,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'ID santri wajib diisi.',
            'santri_id.integer'  => 'ID santri harus berupa angka.',
            'santri_id.exists'   => 'ID santri tidak ditemukan.',
            'tanggal.date'       => 'Tanggal harus berupa format tanggal yang valid.',
            'sholat_id.integer'  => 'ID sholat harus berupa angka.',
            'sholat_id.exists'   => 'ID sholat tidak ditemukan.',
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
