<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateJadwalRequest extends FormRequest
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
            'hari'         => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'semester_id'  => 'required|exists:semester,id',
            'kelas_id'     => 'required|exists:kelas,id',
            'jurusan_id'   => 'required|exists:jurusan,id',
            'rombel_id'    => 'nullable|exists:rombel,id',
            'jam_pelajaran_id' => 'required|exists:jam_pelajaran,id',
        ];
    }

    public function messages(): array
    {
        return [
            'hari.required' => 'Hari wajib diisi.',
            'hari.in' => 'Hari tidak valid. Pilih dari Senin - Minggu.',
            'semester_id.required' => 'Semester wajib diisi.',
            'semester_id.exists' => 'Semester tidak ditemukan.',
            'kelas_id.required' => 'Kelas wajib diisi.',
            'kelas_id.exists' => 'Kelas tidak ditemukan.',
            'jurusan_id.required' => 'Jurusan wajib diisi.',
            'jurusan_id.exists' => 'Jurusan tidak ditemukan.',
            'rombel_id.exists' => 'Rombel tidak ditemukan.',
            'jam_pelajaran_id.required' => 'Jam pelajaran wajib diisi.',
            'jam_pelajaran_id.exists' => 'Jam pelajaran tidak ditemukan.',
            'lembaga_id.required' => 'Lembaga wajib diisi.',
            'lembaga_id.exists' => 'Lembaga tidak ditemukan.',
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
