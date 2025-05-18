<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PindahPengajarRequest extends FormRequest
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
            'golongan_id' => ['required', 'integer', 'exists:golongan,id'],
            'lembaga_id'  => ['required', 'integer', 'exists:lembaga,id'],
            'jabatan'     => ['nullable', 'string', 'max:255'],
            'tahun_masuk' => [
                'required',
                'date',
            ],
            'materi_ajar.*.nama_materi' => ['nullable', 'string', 'max:255'], 
            'materi_ajar.*.jumlah_menit' => ['nullable', 'integer', 'min:0'],
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
