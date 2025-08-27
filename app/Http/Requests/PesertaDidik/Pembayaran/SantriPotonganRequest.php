<?php

namespace App\Http\Requests\PesertaDidik\Pembayaran;

use Illuminate\Foundation\Http\FormRequest;

class SantriPotonganRequest extends FormRequest
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
            'santri_id'   => 'required|exists:santri,id',
            'potongan_id' => 'required|exists:potongan,id',
            'keterangan'  => 'nullable|string|max:255',
            'status'      => 'nullable|boolean',
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
