<?php

namespace App\Http\Requests\Administrasi;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
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

        return [
            'kebahasaan_nilai' => 'required|string|max:255',
            'kebahasaan_tindak_lanjut' => 'required|string|max:1000',
            'baca_kitab_kuning_nilai' => 'required|string|max:255',
            'baca_kitab_kuning_tindak_lanjut' => 'required|string|max:1000',
            'hafalan_tahfidz_nilai' => 'required|string|max:255',
            'hafalan_tahfidz_tindak_lanjut' => 'required|string|max:1000',
            'furudul_ainiyah_nilai' => 'required|string|max:255',
            'furudul_ainiyah_tindak_lanjut' => 'required|string|max:1000',
            'tulis_alquran_nilai' => 'required|string|max:255',
            'tulis_alquran_tindak_lanjut' => 'required|string|max:1000',
            'baca_alquran_nilai' => 'required|string|max:255',
            'baca_alquran_tindak_lanjut' => 'required|string|max:1000',
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
            'id_wali_asuh.required' => 'Wali asuh wajib dipilih.',
            'id_wali_asuh.exists' => 'Wali asuh yang dipilih tidak valid.',

            'kebahasaan_nilai.required' => 'Nilai kebahasaan wajib diisi.',
            'kebahasaan_nilai.string' => 'Nilai kebahasaan harus berupa teks.',
            'kebahasaan_nilai.max' => 'Nilai kebahasaan tidak boleh lebih dari 255 karakter.',
            'kebahasaan_tindak_lanjut.string' => 'Tindak lanjut kebahasaan harus berupa teks.',
            'kebahasaan_tindak_lanjut.max' => 'Tindak lanjut kebahasaan tidak boleh lebih dari 1000 karakter.',

            'baca_kitab_kuning_nilai.required' => 'Nilai baca kitab kuning wajib diisi.',
            'baca_kitab_kuning_nilai.string' => 'Nilai baca kitab kuning harus berupa teks.',
            'baca_kitab_kuning_nilai.max' => 'Nilai baca kitab kuning tidak boleh lebih dari 255 karakter.',
            'baca_kitab_kuning_tindak_lanjut.string' => 'Tindak lanjut baca kitab kuning harus berupa teks.',
            'baca_kitab_kuning_tindak_lanjut.max' => 'Tindak lanjut baca kitab kuning tidak boleh lebih dari 1000 karakter.',

            'hafalan_tahfidz_nilai.required' => 'Nilai hafalan tahfidz wajib diisi.',
            'hafalan_tahfidz_nilai.string' => 'Nilai hafalan tahfidz harus berupa teks.',
            'hafalan_tahfidz_nilai.max' => 'Nilai hafalan tahfidz tidak boleh lebih dari 255 karakter.',
            'hafalan_tahfidz_tindak_lanjut.string' => 'Tindak lanjut hafalan tahfidz harus berupa teks.',
            'hafalan_tahfidz_tindak_lanjut.max' => 'Tindak lanjut hafalan tahfidz tidak boleh lebih dari 1000 karakter.',

            'furudul_ainiyah_nilai.required' => 'Nilai furudul ainiyah wajib diisi.',
            'furudul_ainiyah_nilai.string' => 'Nilai furudul ainiyah harus berupa teks.',
            'furudul_ainiyah_nilai.max' => 'Nilai furudul ainiyah tidak boleh lebih dari 255 karakter.',
            'furudul_ainiyah_tindak_lanjut.string' => 'Tindak lanjut furudul ainiyah harus berupa teks.',
            'furudul_ainiyah_tindak_lanjut.max' => 'Tindak lanjut furudul ainiyah tidak boleh lebih dari 1000 karakter.',

            'tulis_alquran_nilai.required' => 'Nilai tulis Al-Qur\'an wajib diisi.',
            'tulis_alquran_nilai.string' => 'Nilai tulis Al-Qur\'an harus berupa teks.',
            'tulis_alquran_nilai.max' => 'Nilai tulis Al-Qur\'an tidak boleh lebih dari 255 karakter.',
            'tulis_alquran_tindak_lanjut.string' => 'Tindak lanjut tulis Al-Qur\'an harus berupa teks.',
            'tulis_alquran_tindak_lanjut.max' => 'Tindak lanjut tulis Al-Qur\'an tidak boleh lebih dari 1000 karakter.',

            'baca_alquran_nilai.required' => 'Nilai baca Al-Qur\'an wajib diisi.',
            'baca_alquran_nilai.string' => 'Nilai baca Al-Qur\'an harus berupa teks.',
            'baca_alquran_nilai.max' => 'Nilai baca Al-Qur\'an tidak boleh lebih dari 255 karakter.',
            'baca_alquran_tindak_lanjut.string' => 'Tindak lanjut baca Al-Qur\'an harus berupa teks.',
            'baca_alquran_tindak_lanjut.max' => 'Tindak lanjut baca Al-Qur\'an tidak boleh lebih dari 1000 karakter.',

            'tanggal_buat.date' => 'Tanggal buat harus berupa tanggal yang valid.',
        ];
    }
}
