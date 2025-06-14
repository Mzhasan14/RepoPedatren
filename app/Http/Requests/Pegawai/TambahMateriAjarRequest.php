<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TambahMateriAjarRequest extends FormRequest
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
            'tahun_masuk' => 'nullable|date',
            'materi_ajar' => 'required|array|min:1',
            'materi_ajar.*.nama_materi' => 'required|string|max:255',
            'materi_ajar.*.jumlah_menit' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'materi_ajar.required' => 'Data materi ajar wajib diisi.',
            'materi_ajar.*.nama_materi.required' => 'Nama materi wajib diisi.',
            'materi_ajar.*.jumlah_menit.integer' => 'Jumlah menit harus berupa angka.',
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
