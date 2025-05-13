<?php

namespace App\Http\Requests\Administrasi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CatatanKognitifRequest extends FormRequest
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
        $nilaiRules = ['nullable', Rule::in(['A', 'B', 'C', 'D', 'E'])];

        return [
            'id_wali_asuh' => 'nullable|exists:wali_asuh,id',
            
            // Validasi nilai
            'kebahasaan_nilai' => $nilaiRules,
            'baca_kitab_kuning_nilai' => $nilaiRules,
            'hafalan_tahfidz_nilai' => $nilaiRules,
            'furudul_ainiyah_nilai' => $nilaiRules,
            'tulis_alquran_nilai' => $nilaiRules,
            'baca_alquran_nilai' => $nilaiRules,
            
            // Validasi tindak lanjut
            'kebahasaan_tindak_lanjut' => 'nullable|string|max:500',
            'baca_kitab_kuning_tindak_lanjut' => 'nullable|string|max:500',
            'hafalan_tahfidz_tindak_lanjut' => 'nullable|string|max:500',
            'furudul_ainiyah_tindak_lanjut' => 'nullable|string|max:500',
            'tulis_alquran_tindak_lanjut' => 'nullable|string|max:500',
            'baca_alquran_tindak_lanjut' => 'nullable|string|max:500',
            
            // Validasi tanggal
            'tanggal_buat' => 'nullable|date|before_or_equal:today',
            'tanggal_selesai' => 'nullable|date|after:tanggal_buat',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Validasi gagal. Mohon periksa kembali input Anda.',
            'errors'  => $errors,               // akan berisi detail per‐field
        ], 422);

        throw new HttpResponseException($response);
    }
}
