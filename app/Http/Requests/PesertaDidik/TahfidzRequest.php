<?php

namespace App\Http\Requests\PesertaDidik;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TahfidzRequest extends FormRequest
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
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'santri_id'       => 'required|exists:santri,id',
            'tanggal'         => 'required|date',
            'jenis_setoran'   => 'required|in:baru,murojaah',
            'surat'           => 'required|string|max:20',
            'ayat_mulai'      => 'required|integer|min:1',
            'ayat_selesai'    => 'required|integer|min:1|gte:ayat_mulai',
            'nilai'           => 'required|in:lancar,cukup,kurang',
            'catatan'         => 'nullable|string|max:1000',
            'status'          => 'required|in:proses,tuntas'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Validasi gagal. Mohon periksa kembali input Anda.',
            'error' => $errors,
        ], 422);

        throw new HttpResponseException($response);
    }
}
