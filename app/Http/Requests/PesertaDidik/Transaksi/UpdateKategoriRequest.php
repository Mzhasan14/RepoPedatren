<?php

namespace App\Http\Requests\PesertaDidik\Transaksi;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKategoriRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
 public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,' . $this->route('kategori'),
            'status' => 'boolean',
        ];
    }
}
