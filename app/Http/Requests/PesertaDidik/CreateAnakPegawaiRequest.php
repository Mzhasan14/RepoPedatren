<?php

namespace App\Http\Requests\PesertaDidik;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
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
            'tempat_lahir_wali' => 'required|string|max:50',
            'tanggal_lahir_wali' => 'required|date',
            'no_telepon_wali' => 'required|string|max:20',
            'pekerjaan_wali' => 'required|string|max:100',
            'pendidikan_terakhir_wali' => 'required|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
            'penghasilan_wali' => 'required|string|max:100',

            'mondok' => 'nullable|integer|in:0,1',
            'nis' => 'nullable|string|max:15|min:10|unique:santri,nis',

            // Rencana Pendidikan
            'no_induk' => 'nullable|string',
            'lembaga_id' => 'nullable|exists:lembaga,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'rombel_id' => 'nullable|exists:rombel,id',
            'tanggal_masuk_pendidikan' => 'nullable|date',
            'angkatan_pelajar_id' => 'nullable|integer|exists:angkatan,id',

            // Rencana Domisili
            'wilayah_id' => 'nullable|exists:wilayah,id',
            'blok_id' => 'nullable|exists:blok,id',
            'kamar_id' => 'nullable|exists:kamar,id',
            'tanggal_masuk_domisili' => 'nullable|date',
            'angkatan_santri_id' => 'nullable|integer|exists:angkatan,id',

            // Berkas
            'berkas' => 'required|array|min:1',
            'berkas.*.jenis_berkas_id' => 'required|exists:jenis_berkas,id',
            'berkas.*.file_path' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    public function withValidator($validator)
    {
        // NIK Anak ≠ Ayah ≠ Ibu ≠ Wali (semua harus unik jika ada)
        $validator->after(function ($validator) {
            $nik = $this->input('nik');
            $nikAyah = $this->input('nik_ayah');
            $nikIbu = $this->input('nik_ibu');
            $nikWali = $this->input('nik_wali');

            $niks = [
                'nik' => $nik,
                'nik_ayah' => $nikAyah,
                'nik_ibu' => $nikIbu,
                'nik_wali' => $nikWali,
            ];

            // Cek jika ada value sama selain null
            $nikCounts = array_count_values(array_filter($niks));
            foreach ($nikCounts as $nikValue => $count) {
                if ($count > 1) {
                    $dupeFields = array_keys(array_filter($niks, fn ($v) => $v === $nikValue));
                    $validator->errors()->add(
                        'nik',
                        'NIK '.implode(', ', $dupeFields).' tidak boleh sama.'
                    );
                }
            }
        });

        $validator->after(function ($validator) {
            // Validasi: jika lembaga diisi, maka tanggal masuk pendidikan wajib
            if ($this->filled('lembaga_id') && ! $this->filled('tanggal_masuk_pendidikan')) {
                $validator->errors()->add('tanggal_masuk_pendidikan', 'Tanggal masuk pendidikan wajib diisi jika lembaga diisi.');
                if (! $this->filled('angkatan_pelajar_id')) {
                    $validator->errors()->add('angkatan_pelajar_id', 'Angkatan pelajar wajib diisi jika lembaga diisi.');
                }
            }

            // Validasi: jika wilayah diisi, maka blok, kamar, dan tanggal masuk domisili wajib
            if ($this->filled('wilayah_id')) {
                if (! $this->filled('blok_id')) {
                    $validator->errors()->add('blok_id', 'Blok wajib diisi jika wilayah diisi.');
                }
                if (! $this->filled('kamar_id')) {
                    $validator->errors()->add('kamar_id', 'Kamar wajib diisi jika wilayah diisi.');
                }
                if (! $this->filled('tanggal_masuk_domisili')) {
                    $validator->errors()->add('tanggal_masuk_domisili', 'Tanggal masuk domisili wajib diisi jika wilayah diisi.');
                }
                if (! $this->filled('angkatan_santri_id')) {
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
            'negara_id.required' => 'Negara wajib dipilih.',
            'negara_id.exists' => 'Negara yang dipilih tidak valid.',
            'provinsi_id.required' => 'Provinsi wajib dipilih.',
            'provinsi_id.exists' => 'Provinsi yang dipilih tidak valid.',
            'kabupaten_id.required' => 'Kabupaten/kota wajib dipilih.',
            'kabupaten_id.exists' => 'Kabupaten/kota yang dipilih tidak valid.',
            'kecamatan_id.required' => 'Kecamatan wajib dipilih.',
            'kecamatan_id.exists' => 'Kecamatan yang dipilih tidak valid.',
            'jalan.required' => 'Jalan wajib diisi.',
            'jalan.max' => 'Jalan maksimal :max karakter.',
            'kode_pos.required' => 'Kode pos wajib diisi.',
            'kode_pos.max' => 'Kode pos maksimal :max karakter.',
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nama.max' => 'Nama lengkap maksimal :max karakter.',

            'nik.digits' => 'NIK harus terdiri dari 16 digit.',
            'nik.required_without_all' => 'NIK wajib diisi jika passport tidak diisi.',
            'no_kk.digits' => 'Nomor KK harus terdiri dari 16 digit.',
            'no_kk.required_without_all' => 'Nomor KK wajib diisi jika passport tidak diisi.',
            'passport.max' => 'Nomor passport maksimal :max karakter.',
            'passport.required_without_all' => 'Nomor passport wajib diisi jika NIK dan Nomor KK tidak diisi.',

            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid. Pilih Laki-laki atau Perempuan.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tempat_lahir.max' => 'Tempat lahir maksimal :max karakter.',
            'no_telepon.required' => 'Nomor telepon wajib diisi.',
            'no_telepon.max' => 'Nomor telepon maksimal :max karakter.',
            'no_telepon_2.max' => 'Nomor telepon tambahan maksimal :max karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
            'email.max' => 'Email maksimal :max karakter.',
            'email.unique' => 'Email sudah terdaftar.',

            'jenjang_pendidikan_terakhir.in' => 'Pilihan jenjang pendidikan terakhir tidak valid.',
            'nama_pendidikan_terakhir.max' => 'Nama pendidikan terakhir maksimal :max karakter.',
            'anak_keberapa.integer' => 'Anak keberapa harus berupa angka.',
            'anak_keberapa.min' => 'Anak keberapa minimal :min.',
            'dari_saudara.integer' => 'Jumlah saudara harus berupa angka.',
            'dari_saudara.min' => 'Jumlah saudara minimal :min.',
            'tinggal_bersama.max' => 'Tinggal bersama maksimal :max karakter.',

            // Ayah
            'nama_ayah.required' => 'Nama ayah wajib diisi.',
            'nama_ayah.max' => 'Nama ayah maksimal :max karakter.',
            'nik_ayah.required' => 'NIK ayah wajib diisi.',
            'nik_ayah.digits' => 'NIK ayah harus terdiri dari 16 digit.',
            'tempat_lahir_ayah.required' => 'Tempat lahir ayah wajib diisi.',
            'tempat_lahir_ayah.max' => 'Tempat lahir ayah maksimal :max karakter.',
            'tanggal_lahir_ayah.required' => 'Tanggal lahir ayah wajib diisi.',
            'tanggal_lahir_ayah.date' => 'Tanggal lahir ayah harus berupa tanggal yang valid.',
            'no_telepon_ayah.required' => 'Nomor telepon ayah wajib diisi.',
            'no_telepon_ayah.max' => 'Nomor telepon ayah maksimal :max karakter.',
            'pekerjaan_ayah.required' => 'Pekerjaan ayah wajib diisi.',
            'pekerjaan_ayah.max' => 'Pekerjaan ayah maksimal :max karakter.',
            'pendidikan_terakhir_ayah.required' => 'Pendidikan terakhir ayah wajib dipilih.',
            'pendidikan_terakhir_ayah.in' => 'Pilihan pendidikan terakhir ayah tidak valid.',
            'penghasilan_ayah.required' => 'Penghasilan ayah wajib diisi.',
            'penghasilan_ayah.max' => 'Penghasilan ayah maksimal :max karakter.',
            'wafat_ayah.required' => 'Status wafat ayah wajib dipilih.',
            'wafat_ayah.integer' => 'Status wafat ayah tidak valid.',
            'wafat_ayah.in' => 'Status wafat ayah tidak valid.',

            // Ibu
            'nama_ibu.required' => 'Nama ibu wajib diisi.',
            'nama_ibu.max' => 'Nama ibu maksimal :max karakter.',
            'nik_ibu.required' => 'NIK ibu wajib diisi.',
            'nik_ibu.digits' => 'NIK ibu harus terdiri dari 16 digit.',
            'tempat_lahir_ibu.required' => 'Tempat lahir ibu wajib diisi.',
            'tempat_lahir_ibu.max' => 'Tempat lahir ibu maksimal :max karakter.',
            'tanggal_lahir_ibu.required' => 'Tanggal lahir ibu wajib diisi.',
            'tanggal_lahir_ibu.date' => 'Tanggal lahir ibu harus berupa tanggal yang valid.',
            'no_telepon_ibu.required' => 'Nomor telepon ibu wajib diisi.',
            'no_telepon_ibu.max' => 'Nomor telepon ibu maksimal :max karakter.',
            'pekerjaan_ibu.required' => 'Pekerjaan ibu wajib diisi.',
            'pekerjaan_ibu.max' => 'Pekerjaan ibu maksimal :max karakter.',
            'pendidikan_terakhir_ibu.required' => 'Pendidikan terakhir ibu wajib dipilih.',
            'pendidikan_terakhir_ibu.in' => 'Pilihan pendidikan terakhir ibu tidak valid.',
            'penghasilan_ibu.required' => 'Penghasilan ibu wajib diisi.',
            'penghasilan_ibu.max' => 'Penghasilan ibu maksimal :max karakter.',
            'wafat_ibu.required' => 'Status wafat ibu wajib dipilih.',
            'wafat_ibu.integer' => 'Status wafat ibu tidak valid.',
            'wafat_ibu.in' => 'Status wafat ibu tidak valid.',

            // Wali
            'nama_wali.required' => 'Nama wali wajib diisi.',
            'nama_wali.max' => 'Nama wali maksimal :max karakter.',
            'nik_wali.required' => 'NIK wali wajib diisi.',
            'nik_wali.digits' => 'NIK wali harus terdiri dari 16 digit.',
            'tempat_lahir_wali.required' => 'Tempat lahir wali wajib diisi.',
            'tempat_lahir_wali.max' => 'Tempat lahir wali maksimal :max karakter.',
            'tanggal_lahir_wali.required' => 'Tanggal lahir wali wajib diisi.',
            'tanggal_lahir_wali.date' => 'Tanggal lahir wali harus berupa tanggal yang valid.',
            'no_telepon_wali.required' => 'Nomor telepon wali wajib diisi.',
            'no_telepon_wali.max' => 'Nomor telepon wali maksimal :max karakter.',
            'pekerjaan_wali.required' => 'Pekerjaan wali wajib diisi.',
            'pekerjaan_wali.max' => 'Pekerjaan wali maksimal :max karakter.',
            'pendidikan_terakhir_wali.required' => 'Pendidikan terakhir wali wajib dipilih.',
            'pendidikan_terakhir_wali.in' => 'Pilihan pendidikan terakhir wali tidak valid.',
            'penghasilan_wali.required' => 'Penghasilan wali wajib diisi.',
            'penghasilan_wali.max' => 'Penghasilan wali maksimal :max karakter.',

            'mondok.integer' => 'Status mondok tidak valid.',
            'mondok.in' => 'Status mondok tidak valid.',
            'nis.string' => 'NIS harus berupa teks.',
            'nis.max' => 'NIS maksimal :max karakter.',
            'nis.min' => 'NIS minimal :min karakter.',
            'nis.unique' => 'NIS sudah terdaftar.',

            // Rencana Pendidikan
            'lembaga_id.exists' => 'Lembaga yang dipilih tidak valid.',
            'jurusan_id.exists' => 'Jurusan yang dipilih tidak valid.',
            'kelas_id.exists' => 'Kelas yang dipilih tidak valid.',
            'rombel_id.exists' => 'Rombel yang dipilih tidak valid.',
            'tanggal_masuk_pendidikan.date' => 'Tanggal masuk pendidikan harus berupa tanggal yang valid.',
            'angkatan_pelajar_id.exists' => 'Angkatan pelajar yang dipilih tidak valid.',

            // Rencana Domisili
            'wilayah_id.exists' => 'Wilayah yang dipilih tidak valid.',
            'blok_id.exists' => 'Blok yang dipilih tidak valid.',
            'kamar_id.exists' => 'Kamar yang dipilih tidak valid.',
            'tanggal_masuk_domisili.date' => 'Tanggal masuk domisili harus berupa tanggal yang valid.',
            'angkatan_santri_id.exists' => 'Angkatan santri yang dipilih tidak valid.',

            // Berkas
            'berkas.required' => 'Minimal satu berkas harus diunggah.',
            'berkas.array' => 'Format berkas tidak valid.',
            'berkas.min' => 'Minimal satu berkas harus diunggah.',
            'berkas.*.jenis_berkas_id.required' => 'Jenis berkas wajib dipilih.',
            'berkas.*.jenis_berkas_id.exists' => 'Jenis berkas tidak valid.',
            'berkas.*.file_path.required' => 'File berkas wajib diunggah.',
            'berkas.*.file_path.file' => 'File berkas harus berupa file yang valid.',
            'berkas.*.file_path.mimes' => 'File berkas hanya boleh berupa PDF, JPG, JPEG, atau PNG.',
            'berkas.*.file_path.max' => 'Ukuran file berkas maksimal 2MB.',
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
