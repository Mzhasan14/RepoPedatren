<?php

namespace App\Http\Requests\Kewaliasuhan;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BerhentiAnakAsuhRequest extends FormRequest
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
            'anak_asuh_ids' => 'required|array|min:1',
            'anak_asuh_ids.*' => 'integer|exists:anak_asuh,id',
        ];
    }
    public function messages(): array
    {
        return [
            'anak_asuh_ids.required' => 'Daftar anak asuh wajib diisi.',
            'anak_asuh_ids.array' => 'Format anak asuh harus berupa array.',
            'anak_asuh_ids.min' => 'Setidaknya harus ada :min anak asuh yang dipilih.',
            'anak_asuh_ids.*.integer' => 'Setiap ID anak asuh harus berupa angka.',
            'anak_asuh_ids.*.exists' => 'Anak asuh dengan ID :input tidak ditemukan.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Validasi gagal. Mohon periksa kembali input Anda.',
            'errors' => $errors,               // akan berisi detail per‐field
        ], 422);

        throw new HttpResponseException($response);
    }
}
