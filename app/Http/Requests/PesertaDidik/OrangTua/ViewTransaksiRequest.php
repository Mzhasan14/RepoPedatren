<?php

namespace App\Http\Requests\PesertaDidik\OrangTua;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ViewTransaksiRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'santri_id'   => 'required|integer|exists:santri,id',
            'outlet_id'   => 'nullable|integer|exists:outlets,id',
            'kategori_id' => 'nullable|integer|exists:kategori,id',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
            'q'           => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'ID santri wajib diisi.',
            'santri_id.integer'  => 'ID santri harus berupa angka.',
            'santri_id.exists'   => 'ID santri tidak ditemukan.',
            'outlet_id.integer' => 'ID outlet harus berupa angka.',
            'outlet_id.exists'  => 'ID outlet tidak ditemukan.',
            'kategori_id.integer' => 'ID kategori harus berupa angka.',
            'kategori_id.exists'  => 'ID kategori tidak ditemukan.',
            'date_from.date' => 'Tanggal awal harus berupa format tanggal yang valid.',
            'date_to.date'             => 'Tanggal akhir harus berupa format tanggal yang valid.',
            'date_to.after_or_equal'   => 'Tanggal akhir harus sama atau setelah tanggal awal.',
            'q.string' => 'Parameter pencarian harus berupa teks.',
            'q.max'    => 'Parameter pencarian maksimal 255 karakter.',
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Validasi gagal. Mohon periksa kembali input Anda.',
            'errors' => $errors,               // akan berisi detail per‐field
        ], 422);

        throw new HttpResponseException($response);
    }
}
