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
            'tahun_ajaran_id' => ['nullable', 'exists:tahun_ajaran,id'],
            'santri_id'       => ['required', 'exists:santri,id'],
            'tanggal'         => ['required', 'date'],
            'jenis_setoran'   => ['required', 'in:baru,murojaah'],


            'surat'        => ['required', 'string', 'max:50'],
            'ayat_mulai'   => ['required', 'integer', 'min:1'],
            'ayat_selesai' => ['required', 'integer', 'gte:ayat_mulai'],
            
            // // 📖 Jika jenis_setoran = baru
            // 'surat'        => ['required_if:jenis_setoran,baru', 'string', 'max:50'],
            // 'ayat_mulai'   => ['required_if:jenis_setoran,baru', 'integer', 'min:1'],
            // 'ayat_selesai' => ['required_if:jenis_setoran,baru', 'integer', 'gte:ayat_mulai'],

            // // 📖 Jika jenis_setoran = murojaah
            // 'juz_mulai'   => ['required_if:jenis_setoran,murojaah', 'integer', 'min:1', 'max:30'],
            // 'juz_selesai' => ['required_if:jenis_setoran,murojaah', 'integer', 'gte:juz_mulai', 'max:30'],

            'nilai'   => ['required', 'in:lancar,cukup,kurang'],
            'catatan' => ['nullable', 'string', 'max:255'],
            'status'  => ['required_if:jenis_setoran,baru', 'in:proses,tuntas'],
        ];
    }

    public function messages(): array
    {
        return [
            'tahun_ajaran_id.required' => 'Tahun ajaran wajib dipilih.',
            'tahun_ajaran_id.exists'   => 'Tahun ajaran tidak valid.',

            'santri_id.required' => 'Santri wajib dipilih.',
            'santri_id.exists'   => 'Santri tidak valid.',

            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date'     => 'Format tanggal tidak valid.',

            'jenis_setoran.required' => 'Jenis setoran wajib dipilih.',
            'jenis_setoran.in'       => 'Jenis setoran hanya boleh "baru" atau "murojaah".',

            // 📖 Jika jenis_setoran = baru
            'surat.required_if'        => 'Surat wajib diisi untuk setoran baru.',
            'ayat_mulai.required_if'   => 'Ayat mulai wajib diisi untuk setoran baru.',
            'ayat_selesai.required_if' => 'Ayat selesai wajib diisi untuk setoran baru.',
            'ayat_selesai.gte'         => 'Ayat selesai harus lebih besar atau sama dengan ayat mulai.',

            // 📖 Jika jenis_setoran = murojaah
            'juz_mulai.required_if'   => 'Juz mulai wajib diisi untuk murojaah.',
            'juz_selesai.required_if' => 'Juz selesai wajib diisi untuk murojaah.',
            'juz_selesai.gte'         => 'Juz selesai harus lebih besar atau sama dengan juz mulai.',
            'juz_mulai.max'           => 'Juz mulai maksimal 30.',
            'juz_selesai.max'         => 'Juz selesai maksimal 30.',

            'nilai.required' => 'Nilai setoran wajib diisi.',
            'nilai.in'       => 'Nilai hanya boleh: lancar, cukup, atau kurang.',

            'catatan.max' => 'Catatan maksimal 1000 karakter.',

            'status.required' => 'Status wajib dipilih.',
            'status.in'       => 'Status hanya boleh: proses atau tuntas.',
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
