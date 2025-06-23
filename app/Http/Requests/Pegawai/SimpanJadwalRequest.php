<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SimpanJadwalRequest extends FormRequest
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
            'jadwal'       => 'required|array|min:1',
            'jadwal.*.hari'             => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jadwal.*.semester_id'      => 'required|exists:semester,id',
            'jadwal.*.kelas_id'         => 'required|exists:kelas,id',
            'jadwal.*.jurusan_id'       => 'required|exists:jurusan,id',
            'jadwal.*.rombel_id'        => 'nullable|exists:rombel,id',
            'jadwal.*.jam_pelajaran_id' => 'required|exists:jam_pelajaran,id',
        ];
    }
        public function messages(): array
    {
        return [
            'lembaga_id.required' => 'Lembaga wajib dipilih.',
            'lembaga_id.exists'   => 'Lembaga yang dipilih tidak ditemukan di sistem.',

            'jadwal.required' => 'Data jadwal wajib diisi.',
            'jadwal.array'    => 'Format jadwal harus berupa array.',
            'jadwal.min'      => 'Minimal harus ada satu jadwal yang diinputkan.',

            'jadwal.*.hari.required' => 'Hari wajib dipilih pada setiap entri jadwal.',
            'jadwal.*.hari.in'       => 'Hari yang dipilih tidak valid. Pilih antara Senin - Minggu.',

            'jadwal.*.semester_id.required' => 'Semester wajib diisi.',
            'jadwal.*.semester_id.exists'   => 'Semester tidak valid atau tidak ditemukan.',

            'jadwal.*.kelas_id.required' => 'Kelas wajib diisi.',
            'jadwal.*.kelas_id.exists'   => 'Kelas tidak ditemukan di sistem.',

            'jadwal.*.jurusan_id.required' => 'Jurusan wajib diisi.',
            'jadwal.*.jurusan_id.exists'   => 'Jurusan tidak ditemukan di sistem.',

            'jadwal.*.rombel_id.exists' => 'Rombel tidak ditemukan di sistem.',

            'jadwal.*.jam_pelajaran_id.required' => 'Jam pelajaran wajib diisi.',
            'jadwal.*.jam_pelajaran_id.exists'   => 'Jam pelajaran tidak valid atau tidak ditemukan.',
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
