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
            'id_anak_asuh' => ['required', 'exists:santri,id'],

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
            'id_santri.required' => 'Santri wajib dipilih.',
            'id_santri.exists' => 'Santri yang dipilih tidak ditemukan.',

            'id_wali_asuh.required' => 'Wali asuh wajib dipilih.',
            'id_wali_asuh.exists' => 'Wali asuh yang dipilih tidak ditemukan.',

            'kebahasaan_nilai.required' => 'Nilai kebahasaan wajib diisi.',
            'kebahasaan_nilai.in' => 'Nilai kebahasaan harus salah satu dari: A, B, C, D, atau E.',
            'kebahasaan_tindak_lanjut.required' => 'Tindak lanjut kebahasaan wajib diisi.',
            'kebahasaan_tindak_lanjut.string' => 'Tindak lanjut kebahasaan harus berupa teks.',

            'baca_kitab_kuning_nilai.required' => 'Nilai baca kitab kuning wajib diisi.',
            'baca_kitab_kuning_nilai.in' => 'Nilai baca kitab kuning harus salah satu dari: A, B, C, D, atau E.',
            'baca_kitab_kuning_tindak_lanjut.required' => 'Tindak lanjut baca kitab kuning wajib diisi.',
            'baca_kitab_kuning_tindak_lanjut.string' => 'Tindak lanjut baca kitab kuning harus berupa teks.',

            'hafalan_tahfidz_nilai.required' => 'Nilai hafalan tahfidz wajib diisi.',
            'hafalan_tahfidz_nilai.in' => 'Nilai hafalan tahfidz harus salah satu dari: A, B, C, D, atau E.',
            'hafalan_tahfidz_tindak_lanjut.required' => 'Tindak lanjut hafalan tahfidz wajib diisi.',
            'hafalan_tahfidz_tindak_lanjut.string' => 'Tindak lanjut hafalan tahfidz harus berupa teks.',

            'furudul_ainiyah_nilai.required' => 'Nilai furudul ain wajib diisi.',
            'furudul_ainiyah_nilai.in' => 'Nilai furudul ain harus salah satu dari: A, B, C, D, atau E.',
            'furudul_ainiyah_tindak_lanjut.required' => 'Tindak lanjut furudul ain wajib diisi.',
            'furudul_ainiyah_tindak_lanjut.string' => 'Tindak lanjut furudul ain harus berupa teks.',

            'tulis_alquran_nilai.required' => 'Nilai tulis Al-Qur’an wajib diisi.',
            'tulis_alquran_nilai.in' => 'Nilai tulis Al-Qur’an harus salah satu dari: A, B, C, D, atau E.',
            'tulis_alquran_tindak_lanjut.required' => 'Tindak lanjut tulis Al-Qur’an wajib diisi.',
            'tulis_alquran_tindak_lanjut.string' => 'Tindak lanjut tulis Al-Qur’an harus berupa teks.',

            'baca_alquran_nilai.required' => 'Nilai baca Al-Qur’an wajib diisi.',
            'baca_alquran_nilai.in' => 'Nilai baca Al-Qur’an harus salah satu dari: A, B, C, D, atau E.',
            'baca_alquran_tindak_lanjut.required' => 'Tindak lanjut baca Al-Qur’an wajib diisi.',
            'baca_alquran_tindak_lanjut.string' => 'Tindak lanjut baca Al-Qur’an harus berupa teks.',

            'tanggal_buat.date' => 'Tanggal buat harus berupa format tanggal yang valid.',
        ];
    }
}
