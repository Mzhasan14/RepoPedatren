<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

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
            'tahun_masuk' => ['required', 'date'],

            // Mata pelajaran
            'mata_pelajaran' => ['nullable', 'array'],
            'mata_pelajaran.*.kode_mapel' => ['nullable', 'string', 'max:50'],
            'mata_pelajaran.*.nama_mapel' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'golongan_id.required' => 'Golongan wajib diisi.',
            'golongan_id.integer'  => 'Golongan harus berupa angka.',
            'golongan_id.exists'   => 'Golongan tidak valid.',

            'lembaga_id.required' => 'Lembaga wajib diisi.',
            'lembaga_id.integer'  => 'Lembaga harus berupa angka.',
            'lembaga_id.exists'   => 'Lembaga tidak valid.',

            'tahun_masuk.required'        => 'Tahun masuk wajib diisi.',
            'tahun_masuk.date'            => 'Tahun masuk harus berupa tanggal.',
            'tahun_masuk.after_or_equal'  => 'Tahun masuk tidak boleh sebelum hari ini.',

            'mata_pelajaran.required' => 'Mata pelajaran wajib diisi.',
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
