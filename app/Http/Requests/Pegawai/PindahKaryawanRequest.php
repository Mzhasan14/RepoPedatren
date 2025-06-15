<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PindahKaryawanRequest extends FormRequest
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
            'golongan_jabatan_id' => 'required|exists:golongan_jabatan,id',
            'lembaga_id' => 'required|exists:lembaga,id',
            'jabatan' => 'required|string|max:255',
            'keterangan_jabatan' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
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
    public function messages()
    {
        return [
            'golongan_jabatan_id.required' => 'Golongan jabatan wajib diisi.',
            'golongan_jabatan_id.exists'   => 'Golongan jabatan yang dipilih tidak valid.',

            'lembaga_id.required' => 'Lembaga wajib diisi.',
            'lembaga_id.exists'   => 'Lembaga yang dipilih tidak valid.',

            'jabatan.string' => 'Jabatan harus berupa teks.',
            'jabatan.max'    => 'Jabatan tidak boleh lebih dari 255 karakter.',

            'keterangan_jabatan.string' => 'Keterangan jabatan harus berupa teks.',
            'keterangan_jabatan.max'    => 'Keterangan jabatan tidak boleh lebih dari 255 karakter.',

            'tanggal_mulai.required'         => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date'             => 'Tanggal mulai harus berupa tanggal yang valid.',
            'tanggal_mulai.after_or_equal'   => 'Tanggal mulai tidak boleh lebih kecil dari hari ini.',
        ];
    }
}
