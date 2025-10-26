<?php

namespace App\Http\Requests\PesertaDidik\Pembayaran;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // atur via policy jika perlu
    }

    public function rules(): array
    {
        $id = $this->route('id'); // mengikuti routes {id}

        return [
            'kode_bank' => [
                'required',
                'string',
                'max:10',
                Rule::unique('banks', 'kode_bank')->ignore($id),
            ],
            'nama_bank' => ['required', 'string', 'max:100'],
            'status'    => ['sometimes', 'boolean'],
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
