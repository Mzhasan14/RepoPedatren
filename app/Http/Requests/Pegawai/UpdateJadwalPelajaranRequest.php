<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
class UpdateJadwalPelajaranRequest extends FormRequest
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
            'hari'               => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'mata_pelajaran_id'  => 'required|exists:mata_pelajaran,id',
            'jam_pelajaran_id'   => 'required|exists:jam_pelajaran,id',
        ];
    }

    public function messages(): array
    {
        return [
            'hari.required'               => 'Hari wajib dipilih.',
            'hari.string'                 => 'Hari harus berupa teks.',
            'hari.in'                     => 'Hari harus diisi dengan nama hari yang valid (Senin s/d Ahad).',

            'mata_pelajaran_id.required' => 'Mata pelajaran wajib dipilih.',
            'mata_pelajaran_id.exists'   => 'Mata pelajaran tidak ditemukan',

            'jam_pelajaran_id.required'  => 'Jam pelajaran wajib dipilih.',
            'jam_pelajaran_id.exists'    => 'Jam pelajaran tidak ditemukan',
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
