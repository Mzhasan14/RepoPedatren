<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SantriRequest extends FormRequest
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
            'id_biodata' => 'required', // |exists:biodatas,id
            'nis' => [
                'required',
                'numeric',
                Rule::unique('peserta_didik', 'nis')->ignore($this->route('santri')),
            ],
            'anak_keberapa' => 'required|numeric|min:1',
            'dari_saudara' => 'required|numeric|min:1|gte:anak_keberapa',
            'tinggal_bersama' => 'required|string|max:50',
            'jenjang_pendidikan_terakhir' => 'required',
            'nama_pendidikan_terakhir' => 'required|string|max:100',
            'smartcard' => 'required|string|max:255',
            'tahun_masuk' => 'required|date|before_or_equal:tahun_keluar',
            'tahun_keluar' => 'required|date|after_or_equal:tahun_masuk',
            'created_by' => 'required', // |exists:users,id
            'status' => 'required'
        ];
    }
}
