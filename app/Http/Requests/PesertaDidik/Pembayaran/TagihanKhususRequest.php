<?php

namespace App\Http\Requests\PesertaDidik\Pembayaran;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TagihanKhususRequest extends FormRequest
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
            'tagihan_id'     => 'required|exists:tagihan,id',
            'angkatan_id'    => 'nullable|exists:angkatan,id',
            'lembaga_id'     => 'nullable|exists:lembaga,id',
            'jurusan_id'     => 'nullable|exists:jurusan,id',
            'jenis_kelamin'  => 'nullable|in:l,p',
            'kategori_santri' => 'nullable|in:mukim,non_mukim',
            'domisili'       => 'nullable|in:lokal,luar_kota',
            'kondisi_khusus' => 'nullable|in:anak_pegawai,beasiswa,wna',
            'nominal'        => 'nullable|numeric|min:0',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Validasi gagal. Mohon periksa kembali input Anda.',
            'error' => $errors,
        ], 422);

        throw new HttpResponseException($response);
    }
}
