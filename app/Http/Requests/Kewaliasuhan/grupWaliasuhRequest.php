<?php

namespace App\Http\Requests\Kewaliasuhan;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class grupWaliasuhRequest extends FormRequest
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
            'nama_grup'     => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:l,p'],
            'id_wilayah'    => ['required', 'exists:wilayah,id'],
            'wali_asuh_id'  => ['nullable', 'integer', 'exists:wali_asuh,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_grup.required'     => 'Nama grup wajib diisi.',
            'nama_grup.string'       => 'Nama grup harus berupa teks.',
            'nama_grup.max'          => 'Nama grup maksimal 255 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin grup wajib diisi.',
            'jenis_kelamin.in'       => 'Jenis kelamin hanya boleh "l" atau "p".',
            'id_wilayah.required'    => 'Wilayah wajib dipilih.',
            'id_wilayah.exists'      => 'Wilayah tidak valid.',
            'wali_asuh_id.integer'   => 'Wali asuh tidak valid.',
            'wali_asuh_id.exists'    => 'Wali asuh tidak ditemukan.',
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
