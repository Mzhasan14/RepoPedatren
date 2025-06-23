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
            'semester_id'        => 'required|exists:semester,id',
            'lembaga_id'         => 'required|exists:lembaga,id',
            'jurusan_id'         => 'required|exists:jurusan,id',
            'kelas_id'           => 'required|exists:kelas,id',
            'rombel_id'          => 'nullable|exists:rombel,id',
            'mata_pelajaran_id'  => 'required|exists:mata_pelajaran,id',
            'jam_pelajaran_id'   => 'required|exists:jam_pelajaran,id',
        ];
    }

    public function messages(): array
    {
        return [
            'hari.required'               => 'Hari wajib diisi.',
            'hari.in'                     => 'Hari harus berupa Senin sampai Minggu.',
            'semester_id.required'        => 'Semester wajib dipilih.',
            'semester_id.exists'          => 'Semester tidak ditemukan.',
            'lembaga_id.required'         => 'Lembaga wajib dipilih.',
            'lembaga_id.exists'           => 'Lembaga tidak ditemukan.',
            'jurusan_id.required'         => 'Jurusan wajib dipilih.',
            'jurusan_id.exists'           => 'Jurusan tidak ditemukan.',
            'kelas_id.required'           => 'Kelas wajib dipilih.',
            'kelas_id.exists'             => 'Kelas tidak ditemukan.',
            'rombel_id.exists'            => 'Rombel tidak ditemukan.',
            'mata_pelajaran_id.required'  => 'Mata pelajaran wajib dipilih.',
            'mata_pelajaran_id.exists'    => 'Mata pelajaran tidak ditemukan.',
            'jam_pelajaran_id.required'   => 'Jam pelajaran wajib dipilih.',
            'jam_pelajaran_id.exists'     => 'Jam pelajaran tidak ditemukan.',
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
