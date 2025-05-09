<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;

class PengurusRequest extends FormRequest
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
        'golongan_jabatan_id'   => 'required|exists:golongan_jabatan,id',
        'jabatan'               => 'nullable|string|max:255',
        'satuan_kerja'          => 'nullable|string|max:255',
        'keterangan_jabatan'    => 'nullable|string|max:255',
        'tanggal_akhir'         => 'nullable|date|after_or_equal:tanggal_mulai',
        ];
    }
}
