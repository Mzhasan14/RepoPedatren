<?php

namespace App\Http\Requests\PesertaDidik;

use Illuminate\Foundation\Http\FormRequest;

class KartuStoreRequest extends FormRequest
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
            'santri_id' => 'required|exists:santri,id',
            'uid_kartu' => 'required|string|max:50|unique:kartu,uid_kartu',
            'pin' => 'nullable|string|min:4|max:6',
            'aktif' => 'boolean',
            'tanggal_terbit' => 'required|date',
            'tanggal_expired' => 'nullable|date|after_or_equal:tanggal_terbit',
        ];
    }
}
