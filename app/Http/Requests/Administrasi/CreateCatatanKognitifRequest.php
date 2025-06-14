<?php

namespace App\Http\Requests\Administrasi;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateCatatanKognitifRequest extends FormRequest
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
            'id_santri' => ['required', 'exists:santri,id'],
            'id_wali_asuh' => ['required', 'exists:wali_asuh,id'],

            'kebahasaan_nilai' => ['required', 'in:A,B,C,D,E'],
            'kebahasaan_tindak_lanjut' => ['required', 'string'],

            'baca_kitab_kuning_nilai' => ['required', 'in:A,B,C,D,E'],
            'baca_kitab_kuning_tindak_lanjut' => ['required', 'string'],

            'hafalan_tahfidz_nilai' => ['required', 'in:A,B,C,D,E'],
            'hafalan_tahfidz_tindak_lanjut' => ['required', 'string'],

            'furudul_ainiyah_nilai' => ['required', 'in:A,B,C,D,E'],
            'furudul_ainiyah_tindak_lanjut' => ['required', 'string'],

            'tulis_alquran_nilai' => ['required', 'in:A,B,C,D,E'],
            'tulis_alquran_tindak_lanjut' => ['required', 'string'],

            'baca_alquran_nilai' => ['required', 'in:A,B,C,D,E'],
            'baca_alquran_tindak_lanjut' => ['required', 'string'],

            'tanggal_buat' => ['nullable', 'date'],
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
