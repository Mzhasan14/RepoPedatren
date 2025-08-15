<?php

namespace App\Http\Requests\PesertaDidik\Transaksi;

use Illuminate\Foundation\Http\FormRequest;

class StoreOutletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
  public function authorize(): bool
    {
        return true; // bisa diatur pakai Gate/Policy
    }

    public function rules(): array
    {
        return [
            'nama_outlet' => 'required|string|max:255|unique:outlet,nama_outlet',
            'jenis_outlet' => 'required|string|max:50',
            'status' => 'boolean',
        ];
    }
}
