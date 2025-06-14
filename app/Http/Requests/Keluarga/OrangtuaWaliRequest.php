<?php

namespace App\Http\Requests\Keluarga;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class OrangtuaWaliRequest extends FormRequest
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
            'negara' => 'required|string|max:255',
            'provinsi' => 'nullable|string|max:255',
            'kabupaten' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'jalan' => 'required|string|max:255',
            'kode_pos' => 'nullable|string|max:10',

            // ======= Biodata ==========
            'nama' => 'required|string|max:255',
            'no_passport' => 'nullable|string|max:50',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:l,p',
            'tempat_lahir' => 'required|string|max:255',
            'nik' => 'nullable|string|max:16',
            'no_telepon' => 'nullable|string|max:20',
            'no_telepon_2' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'jenjang_pendidikan_terakhir' => 'nullable|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
            'nama_pendidikan_terakhir' => 'nullable|string|max:255',
            'anak_keberapa' => 'nullable|integer',
            'dari_saudara' => 'nullable|integer',
            'wafat' => 'required|boolean|in:0,1',

            // ======= Keluarga (Opsional) =======
            'no_kk' => 'nullable|string|max:16',

            // ====== Orangtua ======
            'id_hubungan_keluarga' => 'nullable|exists:hubungan_keluarga,id',
            'wali' => 'nullable|boolean',
            'pekerjaan' => 'nullable|string',
            'penghasilan' => 'nullable|string',
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
