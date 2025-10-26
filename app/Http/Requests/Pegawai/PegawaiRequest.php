<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PegawaiRequest extends FormRequest
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
            // ======= Alamat ==========
            'negara_id' => 'required|exists:negara,id',
            'provinsi_id' => 'required|exists:provinsi,id',
            'kabupaten_id' => 'required|exists:kabupaten,id',
            'kecamatan_id' => 'required|exists:kecamatan,id',
            'jalan' => 'required|string|max:255',
            'kode_pos' => 'nullable|string|max:10',

            // ======= Biodata ==========
            'nama' => 'required|string|max:255',
            'no_passport' => 'nullable|string|max:50',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:l,p',
            'tempat_lahir' => 'required|string|max:255',
            'nik' => 'required|string|max:16',
            'no_telepon' => 'nullable|string|max:20',
            'no_telepon_2' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'jenjang_pendidikan_terakhir' => 'nullable|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
            'nama_pendidikan_terakhir' => 'nullable|string|max:255',
            'anak_keberapa' => 'nullable|integer',
            'dari_saudara' => 'nullable|integer',
            'tinggal_bersama' => 'nullable|string|max:255',
            'smartcard' => 'nullable|string|max:255',
            'wafat' => 'required|in:0,1',

            // ======= Keluarga (Opsional) =======
            'no_kk' => 'nullable|string|max:16',

            // ======= Warga Pesantren (Opsional) =======
            'niup' => 'nullable|string|max:50',

            // Validasi Pas Foto
            'pas_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
