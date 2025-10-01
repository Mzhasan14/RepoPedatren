<?php

namespace App\Http\Requests\PesertaDidik\Pembayaran;

use Illuminate\Foundation\Http\FormRequest;

class PotonganRequest extends FormRequest
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
        $rules = [
            'nama'       => 'required|string|max:100',
            'kategori'   => 'required|in:anak_pegawai,bersaudara,khadam,umum',
            'jenis'      => 'required|in:persentase,nominal',
            'nilai'      => 'required|numeric|min:0',
            'status'     => 'boolean',
            'keterangan' => 'nullable|string',

            // Relasi ke tagihan
            'tagihan_ids'   => 'nullable|array',
            'tagihan_ids.*' => 'integer|exists:tagihan,id',
        ];

        // Hanya wajib jika kategori = umum
        if ($this->kategori === 'umum') {
            $rules['santri_ids'] = 'required|array|min:1';
            $rules['santri_ids.*'] = 'integer|exists:santri,id';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nama.required'       => 'Nama potongan wajib diisi.',
            'nama.string'         => 'Nama potongan harus berupa teks.',
            'nama.max'            => 'Nama potongan maksimal 100 karakter.',

            'kategori.required'   => 'Kategori potongan wajib diisi.',
            'kategori.in'         => 'Kategori harus salah satu dari: anak_pegawai, bersaudara, khadam, atau umum.',

            'jenis.required'      => 'Jenis potongan wajib diisi.',
            'jenis.in'            => 'Jenis potongan hanya boleh persentase atau nominal.',

            'nilai.required'      => 'Nilai potongan wajib diisi.',
            'nilai.numeric'       => 'Nilai potongan harus berupa angka.',
            'nilai.min'           => 'Nilai potongan minimal 0.',

            'status.boolean'      => 'Status potongan harus berupa true/false.',

            'keterangan.string'   => 'Keterangan harus berupa teks.',

            // Relasi tagihan
            'tagihan_ids.array'   => 'Tagihan harus berupa array.',
            'tagihan_ids.*.integer' => 'ID tagihan harus berupa angka.',
            'tagihan_ids.*.exists'  => 'Tagihan yang dipilih tidak valid.',

            // Relasi santri (khusus kategori umum)
            'santri_ids.required' => 'Daftar santri wajib diisi untuk kategori umum.',
            'santri_ids.array'    => 'Santri harus berupa array.',
            'santri_ids.min'      => 'Minimal pilih 1 santri.',
            'santri_ids.*.integer' => 'ID santri harus berupa angka.',
            'santri_ids.*.exists' => 'Santri yang dipilih tidak valid.',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Validasi gagal. Mohon periksa kembali input Anda.',
            'error' => $errors,
        ], 422);

        throw new \Illuminate\Http\Exceptions\HttpResponseException($response);
    }
}
