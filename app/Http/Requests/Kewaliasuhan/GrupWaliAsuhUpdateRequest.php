<?php

namespace App\Http\Requests\Kewaliasuhan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\DB;

class GrupWaliAsuhUpdateRequest extends FormRequest
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
            'id_wilayah' => ['required', 'exists:wilayah,id'],
            'nama_grup' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:l,p'],
            'wali_asuh_id' => ['nullable', 'exists:santri,id'],
            'status' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_wilayah.required' => 'Wilayah harus diisi.',
            'id_wilayah.exists'   => 'Wilayah tidak ditemukan.',
            'nama_grup.required'  => 'Nama grup wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin grup wajib diisi.',
            'jenis_kelamin.in'    => 'Jenis kelamin hanya boleh L atau P.',
            'wali_asuh_id.exists' => 'Santri yang dipilih untuk wali asuh tidak valid.',
            'status.boolean'      => 'Status harus berupa true atau false.',
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
