<?php

namespace App\Http\Requests\PesertaDidik\OrangTua;

use Illuminate\Foundation\Http\FormRequest;

class KirimPesanRequest extends FormRequest
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
            'santri_id' => 'required|integer|exists:santri,id',
            'pesan'     => 'required|string|min:5|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'Santri wajib dipilih.',
            'santri_id.integer'  => 'ID santri tidak valid.',
            'santri_id.exists'   => 'Santri tidak ditemukan di sistem.',
            'pesan.required'     => 'Pesan tidak boleh kosong.',
            'pesan.min'          => 'Pesan minimal 5 karakter.',
            'pesan.max'          => 'Pesan terlalu panjang (maksimal 5000 karakter).',
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
