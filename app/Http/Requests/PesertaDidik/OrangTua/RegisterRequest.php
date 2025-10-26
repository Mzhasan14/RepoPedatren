<?php

namespace App\Http\Requests\PesertaDidik\OrangTua;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
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
            // 'nama_ortu' => 'required|string|max:100',
            // 'nik_ortu' => 'required|string|max:16|exists:biodata,nik',
            // 'nis_anak' => 'required|string|max:20|exists:santri,nis',
            'no_kk' => 'required|string|max:16|exists:keluarga,no_kk',
            'nis_anak' => 'required|string|max:20|exists:santri,nis',
            'no_hp' => 'required|string|max:15|unique:user_ortu,no_hp',
            'email' => 'required|email|max:100|unique:user_ortu,email',
            'password' => 'required|string|min:8|confirmed',
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
