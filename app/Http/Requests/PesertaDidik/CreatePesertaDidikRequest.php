<?php

namespace App\Http\Requests\PesertaDidik;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreatePesertaDidikRequest extends FormRequest
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
            'angkatan_id' => 'nullable|exists:angkatan,id',
            'tanggal_masuk_pendidikan' => 'nullable|date',
            'angkatan_pelajar_id' => 'nullable|integer',

            // Rencana Domisili
            'wilayah_id' => 'nullable|exists:wilayah,id',
            'blok_id' => 'nullable|exists:blok,id',
            'kamar_id' => 'nullable|exists:kamar,id',
            'tanggal_masuk_domisili' => 'nullable|date',
            'angkatan_santri_id' => 'nullable|integer',

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
                if (!$this->filled('angkatan_pelajar_id')) {
                    $validator->errors()->add('angkatan_pelajar_id', 'Angkatan pelajar wajib diisi jika lembaga diisi.');
                }
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
                if (!$this->filled('angkatan_santri_id')) {
                    $validator->errors()->add('angkatan_santri_id', 'Angkatan santri wajib diisi jika wilayah diisi.');
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

    public function messages(): array
    {
        return [
            // Biodata Diri
            'negara_id.required' => 'Negara wajib diisi.',
            'negara_id.exists' => 'Negara tidak valid.',
            'provinsi_id.required' => 'Provinsi wajib diisi.',
            'provinsi_id.exists' => 'Provinsi tidak valid.',
            'kabupaten_id.required' => 'Kabupaten wajib diisi.',
            'kabupaten_id.exists' => 'Kabupaten tidak valid.',
            'kecamatan_id.required' => 'Kecamatan wajib diisi.',
            'kecamatan_id.exists' => 'Kecamatan tidak valid.',
            'jalan.required' => 'Jalan wajib diisi.',
            'jalan.max' => 'Jalan maksimal 255 karakter.',
            'kode_pos.required' => 'Kode pos wajib diisi.',
            'kode_pos.max' => 'Kode pos maksimal 10 karakter.',
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',

            'nik.required_without' => 'NIK atau Passport wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit.',
            'passport.required_without' => 'Passport atau NIK wajib diisi.',
            'passport.max' => 'Passport maksimal 20 karakter.',

            'no_kk.required' => 'No KK wajib diisi.',
            'no_kk.digits' => 'No KK harus terdiri dari 16 digit.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib diisi.',
            'jenis_kelamin.in' => 'Jenis kelamin harus L atau P.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tempat_lahir.max' => 'Tempat lahir maksimal 50 karakter.',
            'no_telepon.required' => 'No telepon wajib diisi.',
            'no_telepon.max' => 'No telepon maksimal 20 karakter.',
            'no_telepon_2.max' => 'No telepon 2 maksimal 20 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'email.max' => 'Email maksimal 100 karakter.',

            'jenjang_pendidikan_terakhir.in' => 'Jenjang pendidikan terakhir tidak valid.',
            'nama_pendidikan_terakhir.max' => 'Nama pendidikan terakhir maksimal 100 karakter.',
            'anak_keberapa.integer' => 'Anak keberapa harus berupa angka.',
            'anak_keberapa.min' => 'Anak keberapa minimal 1.',
            'dari_saudara.integer' => 'Jumlah saudara harus berupa angka.',
            'dari_saudara.min' => 'Jumlah saudara minimal 1.',
            'tinggal_bersama.max' => 'Tinggal bersama maksimal 40 karakter.',

            // Ayah
            'nama_ayah.required' => 'Nama ayah wajib diisi.',
            'nama_ayah.max' => 'Nama ayah maksimal 100 karakter.',
            'nik_ayah.required' => 'NIK ayah wajib diisi.',
            'nik_ayah.digits' => 'NIK ayah harus 16 digit.',
            'tempat_lahir_ayah.required' => 'Tempat lahir ayah wajib diisi.',
            'tempat_lahir_ayah.max' => 'Tempat lahir ayah maksimal 50 karakter.',
            'tanggal_lahir_ayah.required' => 'Tanggal lahir ayah wajib diisi.',
            'tanggal_lahir_ayah.date' => 'Format tanggal lahir ayah tidak valid.',
            'no_telepon_ayah.required' => 'No telepon ayah wajib diisi.',
            'no_telepon_ayah.max' => 'No telepon ayah maksimal 20 karakter.',
            'pekerjaan_ayah.required' => 'Pekerjaan ayah wajib diisi.',
            'pekerjaan_ayah.max' => 'Pekerjaan ayah maksimal 100 karakter.',
            'pendidikan_terakhir_ayah.required' => 'Pendidikan terakhir ayah wajib diisi.',
            'pendidikan_terakhir_ayah.in' => 'Pendidikan terakhir ayah tidak valid.',
            'penghasilan_ayah.required' => 'Penghasilan ayah wajib diisi.',
            'penghasilan_ayah.max' => 'Penghasilan ayah maksimal 100 karakter.',
            'wafat_ayah.required' => 'Status wafat ayah wajib diisi.',
            'wafat_ayah.boolean' => 'Status wafat ayah harus berupa boolean.',

            // Ibu
            'nama_ibu.required' => 'Nama ibu wajib diisi.',
            'nama_ibu.max' => 'Nama ibu maksimal 100 karakter.',
            'nik_ibu.required' => 'NIK ibu wajib diisi.',
            'nik_ibu.digits' => 'NIK ibu harus 16 digit.',
            'tempat_lahir_ibu.required' => 'Tempat lahir ibu wajib diisi.',
            'tempat_lahir_ibu.max' => 'Tempat lahir ibu maksimal 50 karakter.',
            'tanggal_lahir_ibu.required' => 'Tanggal lahir ibu wajib diisi.',
            'tanggal_lahir_ibu.date' => 'Format tanggal lahir ibu tidak valid.',
            'no_telepon_ibu.required' => 'No telepon ibu wajib diisi.',
            'no_telepon_ibu.max' => 'No telepon ibu maksimal 20 karakter.',
            'pekerjaan_ibu.required' => 'Pekerjaan ibu wajib diisi.',
            'pekerjaan_ibu.max' => 'Pekerjaan ibu maksimal 100 karakter.',
            'pendidikan_terakhir_ibu.required' => 'Pendidikan terakhir ibu wajib diisi.',
            'pendidikan_terakhir_ibu.in' => 'Pendidikan terakhir ibu tidak valid.',
            'penghasilan_ibu.required' => 'Penghasilan ibu wajib diisi.',
            'penghasilan_ibu.max' => 'Penghasilan ibu maksimal 100 karakter.',
            'wafat_ibu.required' => 'Status wafat ibu wajib diisi.',
            'wafat_ibu.boolean' => 'Status wafat ibu harus berupa boolean.',

            // Wali
            'nama_wali.required' => 'Nama wali wajib diisi.',
            'nama_wali.max' => 'Nama wali maksimal 100 karakter.',
            'nik_wali.required' => 'NIK wali wajib diisi.',
            'nik_wali.digits' => 'NIK wali harus 16 digit.',
            'hubungan.required' => 'Hubungan dengan wali wajib diisi.',
            'hubungan.in' => 'Hubungan dengan wali tidak valid.',
            'tempat_lahir_wali.required' => 'Tempat lahir wali wajib diisi.',
            'tempat_lahir_wali.max' => 'Tempat lahir wali maksimal 50 karakter.',
            'tanggal_lahir_wali.required' => 'Tanggal lahir wali wajib diisi.',
            'tanggal_lahir_wali.date' => 'Format tanggal lahir wali tidak valid.',
            'no_telepon_wali.required' => 'No telepon wali wajib diisi.',
            'no_telepon_wali.max' => 'No telepon wali maksimal 20 karakter.',
            'pekerjaan_wali.required' => 'Pekerjaan wali wajib diisi.',
            'pekerjaan_wali.max' => 'Pekerjaan wali maksimal 100 karakter.',
            'pendidikan_terakhir_wali.required' => 'Pendidikan terakhir wali wajib diisi.',
            'pendidikan_terakhir_wali.in' => 'Pendidikan terakhir wali tidak valid.',
            'penghasilan_wali.required' => 'Penghasilan wali wajib diisi.',
            'penghasilan_wali.max' => 'Penghasilan wali maksimal 100 karakter.',

            // Rencana Pendidikan
            'lembaga_id.exists' => 'Lembaga tidak valid.',
            'jurusan_id.exists' => 'Jurusan tidak valid.',
            'kelas_id.exists' => 'Kelas tidak valid.',
            'rombel_id.exists' => 'Rombel tidak valid.',
            'tanggal_masuk_pendidikan.date' => 'Tanggal masuk pendidikan tidak valid.',

            // Rencana Domisili
            'wilayah_id.exists' => 'Wilayah tidak valid.',
            'blok_id.exists' => 'Blok tidak valid.',
            'kamar_id.exists' => 'Kamar tidak valid.',
            'tanggal_masuk_domisili.date' => 'Tanggal masuk domisili tidak valid.',

            // Berkas
            'berkas.required' => 'Berkas wajib diisi.',
            'berkas.array' => 'Format berkas tidak valid.',
            'berkas.min' => 'Minimal 1 berkas harus diunggah.',
            'berkas.*.jenis_berkas_id.required' => 'Jenis berkas wajib diisi.',
            'berkas.*.jenis_berkas_id.exists' => 'Jenis berkas tidak valid.',
            'berkas.*.file_path.required' => 'File berkas wajib diunggah.',
            'berkas.*.file_path.file' => 'Berkas harus berupa file.',
            'berkas.*.file_path.mimes' => 'Berkas harus berupa PDF, JPG, JPEG, atau PNG.',
            'berkas.*.file_path.max' => 'Ukuran berkas maksimal 2MB.',
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
