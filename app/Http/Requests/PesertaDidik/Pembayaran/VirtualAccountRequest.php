<?php

namespace App\Http\Requests\PesertaDidik\Pembayaran;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VirtualAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Atur ke policy jika perlu
    }

    public function rules(): array
    {
        return [
            'santri_id' => 'required|exists:santri,id',
            'bank_code' => 'required|string|max:10',
            'va_number' => 'required|string|max:30|unique:virtual_accounts,va_number,' . $this->id,
            'status'    => 'boolean',
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
