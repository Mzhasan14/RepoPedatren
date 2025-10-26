<?php

namespace App\Http\Requests\PesertaDidik\OrangTua;

use Illuminate\Foundation\Http\FormRequest;

class LimitSaldoRequest extends FormRequest
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
            'santri_id' => ['required', 'exists:santri,id'],
            'limit_saldo' => ['nullable', 'numeric', 'min:0'],
            'tak_terbatas' => ['required', 'integer', 'in:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'Santri wajib dipilih.',
            'santri_id.exists' => 'Santri tidak ditemukan.',
            'limit_saldo.numeric' => 'Limit saldo harus berupa angka.',
            'tak_terbatas.integer' => 'Format pilihan tak terbatas tidak valid.',
        ];
    }
}
