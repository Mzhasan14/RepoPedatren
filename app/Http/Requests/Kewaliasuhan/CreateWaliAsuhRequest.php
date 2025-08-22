<?php

namespace App\Http\Requests\Kewaliasuhan;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'santri_ids'   => ['required', 'array', 'min:1'],
            'santri_ids.*' => ['integer', 'distinct', 'exists:santri,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'santri_ids.required'   => 'Daftar santri harus diisi.',
            'santri_ids.array'      => 'Format daftar santri tidak valid.',
            'santri_ids.min'        => 'Minimal pilih 1 santri.',
            'santri_ids.*.integer'  => 'ID santri harus berupa angka.',
            'santri_ids.*.exists'   => 'Santri dengan ID tersebut tidak ditemukan.',
            'santri_ids.*.distinct' => 'Terdapat duplikat ID santri di dalam input.',
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
