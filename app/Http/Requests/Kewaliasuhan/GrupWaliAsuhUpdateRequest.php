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
            'nama_grup'  => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['l', 'p'])],
            'wali_asuh_id'  => [
                'nullable',
                'exists:wali_asuh,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $sudahPunyaGrup = DB::table('wali_asuh')
                            ->where('id', $value)
                            ->whereNotNull('id_grup_wali_asuh')
                            ->exists();
                        if ($sudahPunyaGrup) {
                            $fail("Wali asuh dengan ID $value sudah terikat dengan grup lain.");
                        }
                    }
                }
            ],
            'status' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_wilayah.required' => 'Wilayah wajib diisi.',
            'id_wilayah.exists'   => 'Wilayah tidak ditemukan.',
            'nama_grup.required'  => 'Nama grup wajib diisi.',
            'nama_grup.string'    => 'Nama grup harus berupa teks.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib diisi.',
            'jenis_kelamin.in'    => 'Jenis kelamin hanya boleh L atau P.',
            'wali_asuh_id.exists' => 'Wali asuh tidak ditemukan.',
            'status.boolean'      => 'Status hanya boleh true atau false.',
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
