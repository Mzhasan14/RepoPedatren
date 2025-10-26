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
            'lembaga_id'         => 'required|exists:lembaga,id',
            'jurusan_id'         => 'required|exists:jurusan,id',
            'kelas_id'           => 'required|exists:kelas,id',
            'semester_id'        => 'required|exists:semester,id',
            'rombel_id'          => 'nullable|exists:rombel,id',
            'jadwal'             => 'required|array|min:1',
            'jadwal.*.hari'              => 'required|string',
            'jadwal.*.jam_pelajaran_id'  => 'required|exists:jam_pelajaran,id',
            'jadwal.*.mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
        ];
    }


    public function messages(): array
    {
        return [
            'mata_pelajaran_id.required' => 'Mata pelajaran wajib dipilih.',
            'mata_pelajaran_id.exists'   => 'Mata pelajaran tidak ditemukan di database.',
            'lembaga_id.required'        => 'Lembaga wajib dipilih.',
            'lembaga_id.exists'          => 'Lembaga tidak valid.',
            'jurusan_id.required'        => 'Jurusan wajib dipilih.',
            'jurusan_id.exists'          => 'Jurusan tidak ditemukan.',
            'kelas_id.required'          => 'Kelas wajib dipilih.',
            'kelas_id.exists'            => 'Kelas tidak ditemukan.',
            'semester_id.required'       => 'Semester wajib dipilih.',
            'semester_id.exists'         => 'Semester tidak ditemukan.',
            'rombel_id.exists'           => 'Rombel tidak ditemukan.',

            'jadwal.required'            => 'Minimal satu jadwal harus diinput.',
            'jadwal.array'               => 'Format jadwal tidak valid.',
            'jadwal.min'                 => 'Minimal input satu baris jadwal.',

            'jadwal.*.hari.required'             => 'Hari pada setiap jadwal wajib diisi.',
            'jadwal.*.hari.string'               => 'Hari pada jadwal harus berupa teks.',
            'jadwal.*.jam_pelajaran_id.required' => 'Jam pelajaran wajib dipilih pada setiap jadwal.',
            'jadwal.*.jam_pelajaran_id.exists'   => 'Jam pelajaran tidak ditemukan.',
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
