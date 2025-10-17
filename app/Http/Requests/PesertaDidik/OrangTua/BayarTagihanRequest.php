<?php

namespace App\Http\Requests\PesertaDidik\OrangTua;

use Illuminate\Foundation\Http\FormRequest;

class BayarTagihanRequest extends FormRequest
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
            "password" => 'required|string|min:8',
            "tagihan_santri_id" => 'required|integer|exists:tagihan_santri,id'
        ];
    }

    public function messages(): array
    {
        return [
            'password.required'          => 'Password wajib diisi untuk melakukan pembayaran.',
            'password.string'            => 'Format password tidak valid.',
            'password.min'               => 'Password minimal harus terdiri dari :min karakter.',

            'tagihan_santri_id.required' => 'Tagihan santri wajib diisi.',
            'tagihan_santri_id.integer'  => 'ID tagihan santri harus berupa angka.',
            'tagihan_santri_id.exists'   => 'Tagihan santri tidak ditemukan dalam sistem.',
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
