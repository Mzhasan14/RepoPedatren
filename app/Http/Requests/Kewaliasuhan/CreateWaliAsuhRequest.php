<?php

namespace App\Http\Requests\Kewaliasuhan;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class CreateWaliAsuhRequest extends FormRequest
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
            'id_santri' => [
                'required',
                'integer',
                'exists:santri,id', // cukup pastikan id santri ada
            ],
            'grup_wali_asuh_id' => [
                'nullable',
                'integer',
                'exists:grup_wali_asuh,id', // hanya cek id ada, tanpa cek status
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'id_santri.required' => 'Santri wajib dipilih.',
            'id_santri.integer'  => 'Santri tidak valid.',
            'id_santri.exists'   => 'Santri tidak ditemukan.',
            'grup_wali_asuh_id.integer' => 'Grup wali asuh tidak valid.',
            'grup_wali_asuh_id.exists'  => 'Grup wali asuh tidak ditemukan.',
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
