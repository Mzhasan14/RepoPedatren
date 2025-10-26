<?php

namespace App\Http\Requests\PesertaDidik\OrangTua;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ViewHafalanRequest extends FormRequest
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
            'santri_id' => 'required|integer|exists:santri,id',
            'tahun_ajaran_id' => 'nullable|integer|exists:tahun_ajaran,id',
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'ID santri wajib diisi.',
            'santri_id.integer'  => 'ID santri harus berupa angka.',
            'santri_id.exists'   => 'ID santri tidak ditemukan.',
            'tahun_ajaran_id.integer' => 'ID tahun ajaran harus berupa angka.',
            'tahun_ajaran_id.exists'  => 'ID tahun ajaran tidak ditemukan.',
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
