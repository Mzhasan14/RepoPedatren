<?php

namespace App\Http\Requests\PesertaDidik;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BerkasRequest extends FormRequest
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
    public function rules()
    {
        // if ($this->isMethod('post')) {
        //     // Untuk store: menerima array of berkas
        return [
            'jenis_berkas_id' => 'required|exists:jenis_berkas,id',
            'file_path' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
        // }

        // if ($this->isMethod('put') || $this->isMethod('patch')) {
        //     // Untuk update: hanya satu berkas
        //     return [
        //         'jenis_berkas_id' => 'required|exists:jenis_berkas,id',
        //         'file_path' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        //     ];
        // }
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
