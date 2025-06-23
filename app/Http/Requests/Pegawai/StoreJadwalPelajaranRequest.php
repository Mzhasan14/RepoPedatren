<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreJadwalPelajaranRequest extends FormRequest
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
            'mata_pelajaran_id'               => 'required|exists:mata_pelajaran,id',
            'jadwal'                          => 'required|array|min:1',

            'jadwal.*.hari'                   => 'required|string',
            'jadwal.*.semester_id'            => 'required|exists:semester,id',
            'jadwal.*.jurusan_id'             => 'required|exists:jurusan,id',
            'jadwal.*.kelas_id'               => 'required|exists:kelas,id',
            'jadwal.*.lembaga_id'             => 'required|exists:lembaga,id',
            'jadwal.*.jam_pelajaran_id'       => 'required|exists:jam_pelajaran,id',
            'jadwal.*.rombel_id'              => 'nullable|exists:rombel,id',
        ];
    }

    public function messages(): array
    {
        return [
            'mata_pelajaran_id.required'         => 'Mata pelajaran wajib dipilih.',
            'mata_pelajaran_id.exists'           => 'Mata pelajaran tidak ditemukan di sistem.',
            'jadwal.required'                    => 'Setidaknya satu jadwal harus diisi.',
            'jadwal.array'                       => 'Format jadwal tidak valid.',
            'jadwal.min'                         => 'Minimal satu jadwal harus ditambahkan.',

            'jadwal.*.hari.required'             => 'Hari wajib diisi untuk setiap jadwal.',
            'jadwal.*.hari.string'               => 'Hari harus berupa teks.',
            'jadwal.*.semester_id.required'      => 'Semester wajib dipilih untuk setiap jadwal.',
            'jadwal.*.semester_id.exists'        => 'Semester tidak ditemukan.',
            'jadwal.*.jurusan_id.required'       => 'Jurusan wajib dipilih untuk setiap jadwal.',
            'jadwal.*.jurusan_id.exists'         => 'Jurusan tidak ditemukan.',
            'jadwal.*.kelas_id.required'         => 'Kelas wajib dipilih untuk setiap jadwal.',
            'jadwal.*.kelas_id.exists'           => 'Kelas tidak ditemukan.',
            'jadwal.*.lembaga_id.required'       => 'Lembaga wajib dipilih untuk setiap jadwal.',
            'jadwal.*.lembaga_id.exists'         => 'Lembaga tidak ditemukan.',
            'jadwal.*.jam_pelajaran_id.required' => 'Jam pelajaran wajib dipilih untuk setiap jadwal.',
            'jadwal.*.jam_pelajaran_id.exists'   => 'Jam pelajaran tidak ditemukan.',
            'jadwal.*.rombel_id.exists'          => 'Rombel yang dipilih tidak ditemukan.',
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
