<?php

namespace App\Http\Requests\PesertaDidik;

use Illuminate\Foundation\Http\FormRequest;

class PresensiJamaahRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'uid_kartu' => 'required|string|max:50',
            'user_id'   => 'sometimes|nullable|integer|exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'uid_kartu.required' => 'UID kartu wajib diisi.',
        ];
    }
}
