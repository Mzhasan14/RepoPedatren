<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'lembaga_id' => 'required|exists:lembaga,id',
            'golongan_id' => 'required|exists:golongan,id',
            'jabatan' => 'required|string|max:255',

            'nama_materi' => 'nullable|array|min:1',
            'nama_materi.*' => 'nullable|string|max:255',

            'jumlah_menit' => 'nullable|array|min:1',
            'jumlah_menit.*' => 'nullable|integer|min:0',

            'tahun_masuk' => 'nullable|date',

            'tahun_masuk_materi_ajar' => 'nullable|array',
            'tahun_masuk_materi_ajar.*' => 'nullable|date',

            'tahun_akhir_materi_ajar' => 'nullable|array',
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
            'lembaga_id.required' => 'Lembaga wajib diisi.',
            'lembaga_id.exists'   => 'Lembaga yang dipilih tidak valid.',

            'golongan_id.required' => 'Golongan wajib diisi.',
            'golongan_id.exists'   => 'Golongan yang dipilih tidak valid.',

            'jabatan.required' => 'Jabatan wajib diisi.',
            'jabatan.string'   => 'Jabatan harus berupa teks.',
            'jabatan.max'      => 'Jabatan tidak boleh lebih dari 255 karakter.',

            'nama_materi.array'    => 'Format nama materi tidak valid.',
            'nama_materi.min'      => 'Minimal harus ada satu nama materi.',
            'nama_materi.*.string' => 'Setiap nama materi harus berupa teks.',
            'nama_materi.*.max'    => 'Setiap nama materi tidak boleh lebih dari 255 karakter.',

            'jumlah_menit.array'      => 'Format jumlah menit tidak valid.',
            'jumlah_menit.min'        => 'Minimal harus ada satu data jumlah menit.',
            'jumlah_menit.*.integer'  => 'Jumlah menit harus berupa angka.',
            'jumlah_menit.*.min'      => 'Jumlah menit tidak boleh negatif.',

            'tahun_masuk.date' => 'Tahun masuk harus berupa tanggal yang valid.',

            'tahun_masuk_materi_ajar.array'       => 'Format tahun masuk materi ajar tidak valid.',
            'tahun_masuk_materi_ajar.*.date'      => 'Setiap tahun masuk materi ajar harus berupa tanggal yang valid.',

            'tahun_akhir_materi_ajar.array'       => 'Format tahun akhir materi ajar tidak valid.',
        ];
    }

}
