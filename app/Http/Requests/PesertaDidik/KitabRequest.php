<?php

namespace App\Http\Requests\PesertaDidik;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class KitabRequest extends FormRequest
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
        if ($this->isMethod('POST')) {
            return [
                'nama_kitab' => 'required|string|max:100',
                'total_bait' => 'nullable|integer|min:0',
            ];
        } elseif ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return [
                'nama_kitab' => 'sometimes|required|string|max:100',
                'total_bait' => 'sometimes|integer|min:0',
            ];
        }

        return [];
    }
    public function messages()
    {
        return [
            'nama_kitab.required' => 'Nama kitab wajib diisi.',
            'nama_kitab.max' => 'Nama kitab maksimal 100 karakter.',
            'total_bait.integer' => 'Total bait harus berupa angka.',
            'total_bait.min' => 'Total bait minimal 0.',
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
