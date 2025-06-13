<?php

namespace App\Http\Requests\PesertaDidik;

use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PresensiSantriRequest extends FormRequest
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
            'santri_id'         => 'required|exists:santri,id',
            'jenis_presensi_id' => 'required|exists:jenis_presensi,id',
            'tanggal'           => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    // Tidak boleh di masa depan
                    if (Carbon::parse($value)->gt(Carbon::today())) {
                        $fail('Tanggal presensi tidak boleh di masa depan.');
                    }
                }
            ],
            'waktu_presensi'    => 'nullable|date_format:H:i:s',
            'status'            => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alfa'])],
            'keterangan'        => 'nullable|string|max:255',
            'lokasi'            => 'nullable|string|max:50',
            'metode'            => ['required', Rule::in(['qr', 'manual', 'rfid', 'fingerprint'])],
        ];
    }

    public function messages()
    {
        return [
            'santri_id.required'         => 'Santri wajib diisi.',
            'santri_id.exists'           => 'Santri tidak ditemukan.',
            'jenis_presensi_id.required' => 'Jenis presensi wajib diisi.',
            'jenis_presensi_id.exists'   => 'Jenis presensi tidak ditemukan.',
            'tanggal.required'           => 'Tanggal presensi wajib diisi.',
            'tanggal.date'               => 'Format tanggal tidak valid.',
            'waktu_presensi.date_format' => 'Format waktu harus HH:MM:SS.',
            'status.required'            => 'Status wajib diisi.',
            'status.in'                  => 'Status hanya boleh: hadir, izin, sakit, alfa.',
            'metode.required'            => 'Metode presensi wajib diisi.',
            'metode.in'                  => 'Metode hanya boleh: qr, manual, rfid, fingerprint.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Validasi gagal. Mohon periksa kembali input Anda.',
            'error' => $errors
        ], 422);

        throw new HttpResponseException($response);
    }
}
