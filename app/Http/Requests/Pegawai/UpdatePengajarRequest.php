<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
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
            'lembaga_id' => 'required|exists:lembaga,id',
            'golongan_id' => 'required|exists:golongan,id',
            'jabatan' => 'required|string|max:255',
            'keterangan_jabatan' => 'required|string|max:255',
            'tahun_masuk' => 'nullable|date',
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
            'lembaga_id.required'  => 'Lembaga wajib diisi.',
            'lembaga_id.exists'    => 'Lembaga yang dipilih tidak valid.',

            'golongan_id.required' => 'Golongan wajib diisi.',
            'golongan_id.exists'   => 'Golongan yang dipilih tidak valid.',

            'jabatan.required' => 'Jabatan wajib diisi.',
            'jabatan.string'   => 'Jabatan harus berupa teks.',
            'jabatan.max'      => 'Jabatan tidak boleh lebih dari 255 karakter.',

            'tahun_masuk.date' => 'Tahun masuk harus berupa tanggal yang valid.',
        ];
    }
}
