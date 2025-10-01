<?php

namespace App\Http\Requests\PesertaDidik\Pembayaran;

use Illuminate\Foundation\Http\FormRequest;

class PotonganRequest extends FormRequest
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
            'nama'       => 'required|string|max:100',
            'kategori'      => 'required|in:anak_pegawai,bersaudara,khadam,umum',
            'jenis'      => 'required|in:persentase,nominal',
            'nilai'      => 'required|numeric|min:0',
            'status'     => 'boolean',
            'keterangan' => 'nullable|string',

            // Optional relasi ke tagihan
            'tagihan_ids'   => 'nullable|array',
            'tagihan_ids.*' => 'integer|exists:tagihan,id',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Validasi gagal. Mohon periksa kembali input Anda.',
            'error' => $errors,
        ], 422);

        throw new \Illuminate\Http\Exceptions\HttpResponseException($response);
    }
}
