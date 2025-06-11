<?php

namespace App\Http\Requests\Administrasi\Formulir\Catatan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateKognitifRequest extends FormRequest
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
            'id_wali_asuh'                     => 'required|exists:users,id',
            'kebahasaan_nilai'                 => 'required|string|max:255',
            'kebahasaan_tindak_lanjut'         => 'nullable|string|max:1000',
            'baca_kitab_kuning_nilai'          => 'required|string|max:255',
            'baca_kitab_kuning_tindak_lanjut'  => 'nullable|string|max:1000',
            'hafalan_tahfidz_nilai'            => 'required|string|max:255',
            'hafalan_tahfidz_tindak_lanjut'    => 'nullable|string|max:1000',
            'furudul_ainiyah_nilai'            => 'required|string|max:255',
            'furudul_ainiyah_tindak_lanjut'    => 'nullable|string|max:1000',
            'tulis_alquran_nilai'              => 'required|string|max:255',
            'tulis_alquran_tindak_lanjut'      => 'nullable|string|max:1000',
            'baca_alquran_nilai'               => 'required|string|max:255',
            'baca_alquran_tindak_lanjut'       => 'nullable|string|max:1000',
            'tanggal_buat'                     => 'nullable|date',
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
