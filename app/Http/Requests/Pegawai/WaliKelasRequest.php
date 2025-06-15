<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class WaliKelasRequest extends FormRequest
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
            'lembaga_id' => ['required', 'exists:lembaga,id'],
            'jurusan_id' => ['required', 'exists:jurusan,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'rombel_id' => ['required', 'exists:rombel,id'],
            'jumlah_murid' => ['required', 'numeric', 'min:1'],
            'periode_awal' => ['nullable', 'date'],
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
            'lembaga_id.exists'   => 'Lembaga yang dipilih tidak valid.',
            'jurusan_id.exists'   => 'Jurusan yang dipilih tidak valid.',
            'kelas_id.exists'     => 'Kelas yang dipilih tidak valid.',
            'rombel_id.exists'    => 'Rombel yang dipilih tidak valid.',

            'jumlah_murid.required' => 'Jumlah murid wajib diisi.',
            'jumlah_murid.numeric'  => 'Jumlah murid harus berupa angka.',
            'jumlah_murid.min'      => 'Jumlah murid minimal 1 orang.',

            'periode_awal.date' => 'Periode awal harus berupa tanggal yang valid.',
        ];
    }
}
