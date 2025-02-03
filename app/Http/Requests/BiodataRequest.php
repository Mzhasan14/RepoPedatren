<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class BiodataRequest extends FormRequest
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
            'id_desa' => 'required|integer',
            'nama' => 'required|string|max:100',
            'niup' => [
                'required',
                Rule::unique('biodata', 'niup')->ignore($this->route('biodata')),
            ],
            'jenis_kelamin' => 'required',
            'tanggal_lahir' => 'required|date|before:today',
            'tempat_lahir' => 'required|string|max:50',
            'nik' => [
                'required',
                Rule::unique('biodata', 'nik')->ignore($this->route('biodata')),
            ],
            'no_kk' => 'required',
            'no_telepon' => 'required',
            'email' => [
                'required',
                Rule::unique('biodata', 'email')->ignore($this->route('biodata')),
            ],
            'jenjang_pendidikan_terakhir' => 'required',
            'nama_pendidikan_terakhir' => 'required',
            'status' => 'required',
            'created_by' => 'required',
        ];
    }
}
