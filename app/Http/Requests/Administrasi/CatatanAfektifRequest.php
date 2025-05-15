<?php

namespace App\Http\Requests\Administrasi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class CatatanAfektifRequest extends FormRequest
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
            'kepedulian_nilai' => 'nullable|string|max:255',
            'kepedulian_tindak_lanjut' => 'nullable|string|max:255',
            'kebersihan_nilai' => 'nullable|string|max:255',
            'kebersihan_tindak_lanjut' => 'nullable|string|max:255',
            'akhlak_nilai' => 'nullable|string|max:255',
            'akhlak_tindak_lanjut' => 'nullable|string|max:255',
            'tanggal_buat' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_buat',
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
