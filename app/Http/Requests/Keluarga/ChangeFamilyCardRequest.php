<?php

namespace App\Http\Requests\Keluarga;

use Illuminate\Foundation\Http\FormRequest;

class ChangeFamilyCardRequest extends FormRequest
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
            'no_kk_baru' => [
                'required',
                'digits:16',
                'regex:/^[0-9]+$/',  
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'no_kk_baru.required' => 'Nomor KK baru wajib diisi.',
            'no_kk_baru.digits'   => 'Nomor KK baru harus terdiri dari 16 digit angka.',
            'no_kk_baru.regex'    => 'Nomor KK baru hanya boleh berisi angka.',
        ];
    }
}
