<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
class CreateMataPelajaran extends FormRequest
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
            'pengajar_id' => 'required|exists:pengajar,id',
            'mata_pelajaran' => 'required|array|min:1',
            'mata_pelajaran.*.kode_mapel' => 'required|string|max:50',
            'mata_pelajaran.*.nama_mapel' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'pengajar_id.required' => 'Pengajar harus dipilih.',
            'pengajar_id.exists' => 'Pengajar tidak ditemukan.',
            'mata_pelajaran.required' => 'Mata pelajaran tidak boleh kosong.',
            'mata_pelajaran.array' => 'Format mata pelajaran tidak valid.',
            'mata_pelajaran.*.kode_mapel.required' => 'Kode mata pelajaran wajib diisi.',
            'mata_pelajaran.*.nama_mapel.required' => 'Nama mata pelajaran wajib diisi.',
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
