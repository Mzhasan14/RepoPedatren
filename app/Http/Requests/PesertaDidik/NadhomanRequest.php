<?php

namespace App\Http\Requests\PesertaDidik;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class NadhomanRequest extends FormRequest
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
            'santri_id'       => 'required|exists:santri,id',
            'kitab_id'        => 'required|exists:kitab,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'tanggal'         => 'required|date',
            'jenis_setoran'   => 'required|in:baru,murojaah',
            'bait_mulai'      => 'required|integer|min:1',
            'bait_selesai'    => 'required|integer|min:1|gte:bait_mulai',
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
    public function messages(): array
    {
        return [
            'santri_id.required'       => 'Santri wajib dipilih.',
            'santri_id.exists'         => 'Santri yang dipilih tidak ditemukan.',
            
            'kitab_id.required'        => 'Kitab wajib dipilih.',
            'kitab_id.exists'          => 'Kitab yang dipilih tidak ditemukan.',

            'tahun_ajaran_id.required' => 'Tahun ajaran wajib dipilih.',
            'tahun_ajaran_id.exists'   => 'Tahun ajaran yang dipilih tidak ditemukan.',

            'tanggal.required'         => 'Tanggal setoran wajib diisi.',
            'tanggal.date'             => 'Tanggal setoran harus berupa format tanggal yang valid.',

            'jenis_setoran.required'   => 'Jenis setoran wajib dipilih.',
            'jenis_setoran.in'         => 'Jenis setoran hanya boleh berupa "baru" atau "murojaah".',

            'bait_mulai.required'      => 'Bait mulai wajib diisi.',
            'bait_mulai.integer'       => 'Bait mulai harus berupa angka.',
            'bait_mulai.min'           => 'Bait mulai minimal bernilai 1.',

            'bait_selesai.required'    => 'Bait selesai wajib diisi.',
            'bait_selesai.integer'     => 'Bait selesai harus berupa angka.',
            'bait_selesai.min'         => 'Bait selesai minimal bernilai 1.',
            'bait_selesai.gte'         => 'Bait selesai tidak boleh lebih kecil dari bait mulai.',

            'nilai.required'           => 'Nilai wajib dipilih.',
            'nilai.in'                 => 'Nilai hanya boleh "lancar", "cukup", atau "kurang".',

            'catatan.string'           => 'Catatan harus berupa teks.',
            'catatan.max'              => 'Catatan maksimal 1000 karakter.',

            'status.required'          => 'Status wajib dipilih.',
            'status.in'                => 'Status hanya boleh "proses" atau "tuntas".',
        ];
    }
}
