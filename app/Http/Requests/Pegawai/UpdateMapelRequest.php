<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

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
            'kode_mapel' => [
                'required',
                'string',
                Rule::unique('mata_pelajaran', 'kode_mapel')->ignore($this->route('materiId')),
            ],
            'nama_mapel' => 'required|string|max:255',
            'pengajar_id'  => 'required|exists:pengajar,id',
            'lembaga_id' => 'required|exists:lembaga,id',
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
            'pengajar_id.required' => 'Pengajar wajib dipilih.',
            'pengajar_id.exists' => 'Pengajar yang dipilih tidak valid.',
            'lembaga_id.required' => 'Lembaga wajib dipilih untuk setiap mata pelajaran.', // Tambahkan ini!
            'lembaga_id.exists' => 'Lembaga yang dipilih tidak valid.', 
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
