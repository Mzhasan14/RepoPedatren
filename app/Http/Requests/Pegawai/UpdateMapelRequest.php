<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMapelRequest extends FormRequest
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
            'kode_mapel' => 'required|string|max:20|unique:mata_pelajaran,kode_mapel,' . $this->route('id'),
            'nama_mapel' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'kode_mapel.required' => 'Kode mapel wajib diisi.',
            'kode_mapel.max' => 'Kode mapel maksimal 20 karakter.',
            'kode_mapel.unique' => 'Kode mapel sudah digunakan.',
            'nama_mapel.required' => 'Nama mapel wajib diisi.',
            'nama_mapel.max' => 'Nama mapel maksimal 255 karakter.',
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
