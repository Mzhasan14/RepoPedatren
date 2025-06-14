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
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
            'tanggal_kembali' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jenis_izin' => 'required|in:Personal,Rombongan',
            'status' => 'required|in:sedang proses izin,perizinan diterima,sudah berada diluar pondok,perizinan ditolak,dibatalkan,telat(sudah kembali),telat(belum kembali),kembali tepat waktu',
            'keterangan' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'pengasuh_id.integer' => 'Pengasuh harus berupa angka.',
            'pengasuh_id.exists' => 'Pengasuh tidak ditemukan.',
            'biktren_id.integer' => 'Biktren harus berupa angka.',
            'biktren_id.exists' => 'Biktren tidak ditemukan.',
            'kamtib_id.integer' => 'Kamtib harus berupa angka.',
            'kamtib_id.exists' => 'Kamtib tidak ditemukan.',
            'alasan_izin.required' => 'Alasan izin wajib diisi.',
            'alasan_izin.string' => 'Alasan izin harus berupa teks.',
            'alamat_tujuan.required' => 'Alamat tujuan wajib diisi.',
            'alamat_tujuan.string' => 'Alamat tujuan harus berupa teks.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date' => 'Tanggal mulai tidak valid.',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai minimal hari ini.',
            'tanggal_akhir.required' => 'Tanggal akhir wajib diisi.',
            'tanggal_akhir.date' => 'Tanggal akhir tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir minimal sama dengan atau setelah tanggal mulai.',
            'tanggal_kembali.date' => 'Tanggal kembali tidak valid.',
            'tanggal_kembali.after_or_equal' => 'Tanggal kembali minimal sama dengan atau setelah tanggal mulai.',
            'jenis_izin.required' => 'Jenis izin wajib diisi.',
            'jenis_izin.in' => 'Jenis izin harus Personal atau Rombongan.',
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'keterangan.string' => 'Keterangan harus berupa teks.',
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
