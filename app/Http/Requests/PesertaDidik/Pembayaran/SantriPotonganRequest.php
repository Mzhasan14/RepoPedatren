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
            'potongan_id'   => 'required|exists:potongan,id',
            'santri_ids'    => 'required_without:id|array|min:1',
            'santri_ids.*'  => 'required|exists:santri,id',
            'keterangan'    => 'nullable|string|max:255',
            'status'        => 'boolean',
            'berlaku_dari'   => 'nullable|date',
            'berlaku_sampai' => 'nullable|date|after_or_equal:berlaku_dari',
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
