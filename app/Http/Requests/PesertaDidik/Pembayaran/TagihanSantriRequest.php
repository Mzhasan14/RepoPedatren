<?php

namespace App\Http\Requests\PesertaDidik\Pembayaran;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TagihanSantriRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // sesuaikan dengan policy / auth
    }

    public function rules(): array
    {
        return [
            'tagihan_id'    => ['required', 'exists:tagihan,id'],
            'periode'         => ['required', 'string'],
            'all'           => ['boolean'],                      // true = semua santri
            'santri_ids'    => ['array'],                        // list ID santri
            'santri_ids.*'  => ['integer', 'exists:santri,id'],
            'jenis_kelamin' => ['nullable', 'in:l,p'],           // filter gender
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
