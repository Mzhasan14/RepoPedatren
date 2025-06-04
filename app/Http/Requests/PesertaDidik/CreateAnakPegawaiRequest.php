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
            'provinsi_id' => 'required|exists:provinsi,id',
            'kabupaten_id' => 'required|exists:kabupaten,id',
            'kecamatan_id' => 'required|exists:kecamatan,id',
            'jalan' => 'required|string|max:255',
            'kode_pos' => 'required|string|max:10',
            'nama' => 'required|string|max:100',

            // Aturan fleksibel antara nik/no_kk dan passport
            'nik' => 'nullable|required_without_all:passport|digits:16',
            'no_kk' => 'nullable|required_without_all:passport|digits:16',
            'passport' => 'nullable|required_without_all:nik,no_kk|string|max:20',

            'no_kk' => 'nullable|string|digits:16',
            'jenis_kelamin' => 'required|in:l,p',
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'required|string|max:50',
            'no_telepon' => 'required|string|max:20',
            'no_telepon_2' => 'nullable|string|max:20',
            'email' => 'required|email|max:100|unique:biodata,email',

            'jenjang_pendidikan_terakhir' => 'nullable|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
            'nama_pendidikan_terakhir' => 'nullable|string|max:100',
            'anak_keberapa' => 'nullable|integer|min:1',
            'dari_saudara' => 'nullable|integer|min:1',
            'tinggal_bersama' => 'nullable|string|max:40',

            // Biodata Orang Tua - Ayah
            'nama_ayah' => 'required|string|max:100',
            'nik_ayah' => 'required|digits:16',
            'tempat_lahir_ayah' => 'required|string|max:50',
            'tanggal_lahir_ayah' => 'required|date',
            'no_telepon_ayah' => 'required|string|max:20',
            'pekerjaan_ayah' => 'required|string|max:100',
            'pendidikan_terakhir_ayah' => 'required|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
            'penghasilan_ayah' => 'required|string|max:100',
            'wafat_ayah' => 'required|integer|in:0,1',

            // Ibu
            'nama_ibu' => 'required|string|max:100',
            'nik_ibu' => 'required|digits:16',
            'tempat_lahir_ibu' => 'required|string|max:50',
            'tanggal_lahir_ibu' => 'required|date',
            'no_telepon_ibu' => 'required|string|max:20',
            'pekerjaan_ibu' => 'required|string|max:100',
            'pendidikan_terakhir_ibu' => 'required|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
            'penghasilan_ibu' => 'required|string|max:100',
            'wafat_ibu' => 'required|integer|in:0,1',

            // Wali
            'nama_wali' => 'required|string|max:100',
            'nik_wali' => 'required|digits:16',
            'hubungan' => 'required|in:ayah kandung,ibu kandung,kakak kandung,adik kandung,kakek kandung,nenek kandung,paman dari ayah/ibu,bibi dari ayah/ibu,ayah sambung,ibu sambung',
            'tempat_lahir_wali' => 'required|string|max:50',
            'tanggal_lahir_wali' => 'required|date',
            'no_telepon_wali' => 'required|string|max:20',
            'pekerjaan_wali' => 'required|string|max:100',
            'pendidikan_terakhir_wali' => 'required|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
            'penghasilan_wali' => 'required|string|max:100',

            'mondok' => 'nullable|integer|in:0,1',
            'nis' => 'nullable|string|max:15|min:10|unique:santri,nis',

            // Rencana Pendidikan
            'no_induk'  => 'nullable|string',
            'lembaga_id' => 'nullable|exists:lembaga,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'rombel_id' => 'nullable|exists:rombel,id',
            'tanggal_masuk_pendidikan' => 'nullable|date',

            // Rencana Domisili
            'wilayah_id' => 'nullable|exists:wilayah,id',
            'blok_id' => 'nullable|exists:blok,id',
            'kamar_id' => 'nullable|exists:kamar,id',
            'tanggal_masuk_domisili' => 'nullable|date',

            // Berkas
            'berkas' => 'required|array|min:1',
            'berkas.*.jenis_berkas_id' => 'required|exists:jenis_berkas,id',
            'berkas.*.file_path' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validasi: jika lembaga diisi, maka tanggal masuk pendidikan wajib
            if ($this->filled('lembaga_id') && !$this->filled('tanggal_masuk_pendidikan')) {
                $validator->errors()->add('tanggal_masuk_pendidikan', 'Tanggal masuk pendidikan wajib diisi jika lembaga diisi.');
            }

            // Validasi: jika wilayah diisi, maka blok, kamar, dan tanggal masuk domisili wajib
            if ($this->filled('wilayah_id')) {
                if (!$this->filled('blok_id')) {
                    $validator->errors()->add('blok_id', 'Blok wajib diisi jika wilayah diisi.');
                }
                if (!$this->filled('kamar_id')) {
                    $validator->errors()->add('kamar_id', 'Kamar wajib diisi jika wilayah diisi.');
                }
                if (!$this->filled('tanggal_masuk_domisili')) {
                    $validator->errors()->add('tanggal_masuk_domisili', 'Tanggal masuk domisili wajib diisi jika wilayah diisi.');
                }
            }

            // ✅ Validasi NIS jika mondok == 1 atau wilayah_id diisi
            $mondok = $this->input('mondok');
            $wilayahId = $this->input('wilayah_id');
            $nis = $this->input('nis');

            if (($mondok == 1 || $wilayahId !== null) && empty($nis)) {
                $validator->errors()->add('nis', 'Kolom NIS wajib diisi jika mondok atau wilayah diisi.');
            }
        });
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
