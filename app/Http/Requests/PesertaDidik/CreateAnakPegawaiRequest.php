<?php

namespace App\Http\Requests\PesertaDidik;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateAnakPegawaiRequest extends FormRequest
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
            // Biodata Diri
            'negara_id' => 'required|exists:negara,id',
            'provinsi_id' => 'nullable|exists:provinsi,id',
            'kabupaten_id' => 'nullable|exists:kabupaten,id',
            'kecamatan_id' => 'nullable|exists:kecamatan,id',
            'jalan' => 'nullable|string',
            'kode_pos' => 'nullable|string',
            'nama' => 'required|string|max:100',

            'no_passport' => 'nullable|string|unique:biodata,no_passport',
            'no_kk' => 'required|string|max:16',
            'jenis_kelamin' => 'required|in:l,p',
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'required|string|max:50',
            'nik' => 'nullable|digits:16|unique:biodata,nik',
            'no_telepon' => 'required|string|max:20',
            'no_telepon_2' => 'nullable|string|max:20',
            'email' => 'required|email|max:100|unique:biodata,email',

            'jenjang_pendidikan_terakhir' => 'nullable|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
            'nama_pendidikan_terakhir' => 'nullable|string',
            'anak_keberapa' => 'nullable|integer|min:1',
            'dari_saudara' => 'nullable|integer|min:1',
            'tinggal_bersama' => 'nullable|string|max:40',

            // Biodata Orang Tua
            // Ayah
            'nama_ayah' => 'nullable|string|max:100',
            'nik_ayah' => 'nullable|digits:16',
            'tempat_lahir_ayah' => 'required|string|max:50',
            'tanggal_lahir_ayah' => 'required|date',
            'no_telepon_ayah' => 'required|string|max:20',
            'pekerjaan_ayah' => 'required',
            'pendidikan_terakhir_ayah' => 'nullable|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
            'penghasilan_ayah' => 'required',

            // Ibu
            'nama_ibu' => 'nullable|string|max:100',
            'nik_ibu' => 'nullable|digits:16',
            'tempat_lahir_ibu' => 'required|string|max:50',
            'tanggal_lahir_ibu' => 'required|date',
            'no_telepon_ibu' => 'required|string|max:20',
            'pekerjaan_ibu' => 'required',
            'pendidikan_terakhir_ibu' => 'nullable|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
            'penghasilan_ibu' => 'required',

            // Wali
            'nama_wali' => 'nullable|string|max:100',
            'nik_wali' => 'nullable|digits:16',
            'tempat_lahir_wali' => 'required|string|max:50',
            'tanggal_lahir_wali' => 'required|date',
            'no_telepon_wali' => 'required|string|max:20',
            'pekerjaan_wali' => 'required',
            'pendidikan_terakhir_wali' => 'nullable|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
            'penghasilan_wali' => 'required',

            // rencana pendidikan
            'lembaga_id' => 'required|exists:lembaga,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'rombel_id' => 'nullable|exists:rombel,id',

            // rencana domisili
            'wilayah_id' => 'nullable|exists:wilayah,id',
            'blok_id' => 'nullable|exists:blok,id',
            'kamar_id' => 'nullable|exists:kamar,id',

            // berkas
            'berkas' => 'nullable|array',
            'berkas.*.jenis_berkas_id' => 'required|exists:jenis_berkas,id',
            'berkas.*.file_path' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Validasi gagal. Mohon periksa kembali input Anda.',
            'errors'  => $errors,               // akan berisi detail per‐field
        ], 422);

        throw new HttpResponseException($response);
    }
}
