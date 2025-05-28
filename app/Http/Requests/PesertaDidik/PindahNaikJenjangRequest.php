<?php

namespace App\Http\Requests\PesertaDidik;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PindahNaikJenjangRequest extends FormRequest
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
            'santri_id' => 'required|array|min:1',
            'santri_id.*' => 'required|integer|exists:santri,id',
            'lembaga_id' => 'required|integer|exists:lembaga,id',
            'jurusan_id' => 'required|integer|exists:jurusan,id',
            'kelas_id' => 'required|integer|exists:kelas,id',
            'rombel_id' => 'required|integer|exists:rombel,id',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Validasi gagal. Mohon periksa kembali input Anda.',
            'error' => $errors
        ], 422);

        throw new HttpResponseException($response);
    }

}
