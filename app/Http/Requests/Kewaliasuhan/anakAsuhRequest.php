<?php

namespace App\Http\Requests\Kewaliasuhan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class anakAsuhRequest extends FormRequest
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
            'id_wali_asuh' => 'required|exists:wali_asuh,id',
            'santri_id' => 'required|array|min:1',
            'santri_id.*' => 'required|exists:santri,id',
        ];
        
    }

    public function messages(): array
    {
        return [
            'id_wali_asuh.required' => 'Wali asuh wajib dipilih.',
            'id_wali_asuh.exists' => 'Wali asuh tidak ditemukan.',
            'santri_id.required' => 'Minimal satu santri harus dipilih.',
            'santri_id.array' => 'Format santri harus berupa array.',
            'santri_id.*.exists' => 'Beberapa santri tidak valid atau tidak ditemukan.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Validasi gagal. Mohon periksa kembali input Anda.',
            'errors'  => $errors,               // akan berisi detail per‐field
        ], 422);

        throw new HttpResponseException($response);
    }
}
