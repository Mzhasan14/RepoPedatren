<?php

namespace App\Http\Requests\Administrasi;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PerizinanRequest extends FormRequest
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
    public function rules()
    {
        return [
            'pengasuh_id' => 'nullable|integer|exists:users,id',
            'biktren_id' => 'nullable|integer|exists:users,id',
            'kamtib_id' => 'nullable|integer|exists:users,id',
            'alasan_izin' => 'required|string',
            'alamat_tujuan' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
            'tanggal_kembali' => 'nullable|date',
            'jenis_izin' => 'required|in:Personal,Rombongan',
            'status' => 'required|in:sedang proses izin,perizinan diterima,sudah berada diluar pondok,perizinan ditolak,dibatalkan,telat(sudah kembali),telat(belum kembali),kembali tepat waktu',
            'keterangan' => 'nullable|string',
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
