<?php

namespace App\Http\Requests\PesertaDidik\Pembayaran;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TagihanSantriManualRequest extends FormRequest
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
            'tagihan_id'  => 'required|exists:tagihan,id',
            'periode'     => 'required|string',
            'santri_ids'  => 'required|array|min:1',
            'santri_ids.*' => 'required|exists:santri,id',
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
