<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
class PengajarResquest extends FormRequest
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
            'golongan_id' => 'bail|required|integer|exists:golongan,id',
            'lembaga_id'  => 'bail|required|integer|exists:lembaga,id',
            'keterangan_jabatan'     => 'required|string|max:255',
            'jabatan'     => 'nullable|string|max:255',
            'tahun_masuk' => 'bail|required|date|after_or_equal:2000-01-01',

            'mata_pelajaran' => 'nullable|array',
            'mata_pelajaran.*.kode_mapel' => 'nullable|required_with:mata_pelajaran|string|max:100',
            'mata_pelajaran.*.nama_mapel' => 'nullable|required_with:mata_pelajaran|string|max:100',
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
            // Pengajar
            'golongan_id.required' => 'Golongan wajib diisi.',
            'golongan_id.integer'  => 'Golongan harus berupa angka.',
            'golongan_id.exists'   => 'Golongan tidak ditemukan.',

            'lembaga_id.required' => 'Lembaga wajib diisi.',
            'lembaga_id.integer'  => 'Lembaga harus berupa angka.',
            'lembaga_id.exists'   => 'Lembaga tidak ditemukan.',

            'jabatan.string' => 'Jabatan harus berupa teks.',
            'jabatan.max'    => 'Jabatan maksimal 255 karakter.',

            'tahun_masuk.required'         => 'Tahun masuk wajib diisi.',
            'tahun_masuk.date'             => 'Tahun masuk harus berupa tanggal.',
            'tahun_masuk.after_or_equal'   => 'Tahun masuk tidak boleh sebelum hari ini.',

            // Mata pelajaran
            'mata_pelajaran.array' => 'Format mata pelajaran tidak valid.',

            'mata_pelajaran.*.kode_mapel.required_with' => 'Kode mata pelajaran wajib diisi.',
            'mata_pelajaran.*.kode_mapel.string'        => 'Kode mata pelajaran harus berupa teks.',
            'mata_pelajaran.*.kode_mapel.max'           => 'Kode mata pelajaran maksimal 100 karakter.',

            'mata_pelajaran.*.nama_mapel.required_with' => 'Nama mata pelajaran wajib diisi.',
            'mata_pelajaran.*.nama_mapel.string'        => 'Nama mata pelajaran harus berupa teks.',
            'mata_pelajaran.*.nama_mapel.max'           => 'Nama mata pelajaran maksimal 100 karakter.',
        ];
    }

}
