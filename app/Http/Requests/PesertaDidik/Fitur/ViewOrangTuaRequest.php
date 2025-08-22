<?php

namespace App\Http\Requests\PesertaDidik\Fitur;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ViewOrangTuaRequest extends FormRequest
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
            'biodata_id_ortu' => 'required|uuid|exists:biodata,id',
        ];
    }

    public function messages(): array
    {
        return [
            'biodata_id_ortu.required' => 'ID biodata orang tua wajib diisi.',
            'biodata_id_ortu.uuid' => 'ID biodata orang tua harus berupa angka.',
            'biodata_id_ortu.exists' => 'ID biodata orang tua tidak ditemukan.',
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
