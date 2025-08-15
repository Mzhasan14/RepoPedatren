<?php

namespace App\Http\Requests\PesertaDidik\Transaksi;

use Illuminate\Foundation\Http\FormRequest;

class StoreDetailUserOutletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id|unique:detail_user_outlet,user_id',
            'outlet_id' => 'required|exists:outlet,id',
            'status' => 'boolean',
        ];
    }
}
