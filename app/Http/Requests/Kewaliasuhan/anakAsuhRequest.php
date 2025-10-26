<?php

namespace App\Http\Requests\Kewaliasuhan;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
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
            // wajib array & minimal 1 santri
            'santri_id' => 'required|array|min:1',
            // tiap id harus integer & ada di tabel santri
            'santri_id.*' => 'integer|exists:santri,id',

            // wajib ada grup_wali_asuh yang aktif
            'grup_wali_asuh_id' => 'required|integer|exists:grup_wali_asuh,id',
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'Santri wajib dipilih.',
            'santri_id.array'    => 'Format santri tidak valid.',
            'santri_id.min'      => 'Minimal pilih satu santri.',
            'santri_id.*.exists' => 'Santri yang dipilih tidak ditemukan.',

            'grup_wali_asuh_id.required' => 'Grup wali asuh wajib dipilih.',
            'grup_wali_asuh_id.integer'  => 'Grup wali asuh tidak valid.',
            'grup_wali_asuh_id.exists'   => 'Grup wali asuh tidak ditemukan.',
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
