<?php

namespace App\Http\Requests\PesertaDidik\OrangTua;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PresensiJamaahAnakRequest extends FormRequest
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
            'santri_id'     => ['required', 'integer', 'exists:santri,id'],
            'tanggal'       => ['nullable', 'date'],
            'sholat_id'     => ['nullable', 'integer', 'exists:sholat,id'],
            'jadwal_id'     => ['nullable', 'integer', 'exists:jadwal_sholat,id'],
            'metode'        => ['nullable', 'string'],
            'status'        => ['nullable', 'in:all,hadir,tidak_hadir'], // contoh status
            'all'           => ['nullable', 'boolean'],
            'jenis_kelamin' => ['nullable', 'in:l,p'],
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'ID santri wajib diisi.',
            'santri_id.integer'  => 'ID santri harus berupa angka.',
            'santri_id.exists'   => 'ID santri tidak ditemukan.',
            'tanggal.date'       => 'Tanggal harus berupa format tanggal yang valid.',
            'sholat_id.integer'  => 'ID sholat harus berupa angka.',
            'sholat_id.exists'   => 'ID sholat tidak ditemukan.',
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
