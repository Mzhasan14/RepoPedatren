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

    public function messages(): array
    {
        return [
            // Biodata Diri
            'negara_id.required' => 'Negara wajib diisi.',
            'negara_id.exists' => 'Negara tidak valid.',
            'provinsi_id.exists' => 'Provinsi tidak valid.',
            'kabupaten_id.exists' => 'Kabupaten tidak valid.',
            'kecamatan_id.exists' => 'Kecamatan tidak valid.',
            'jalan.string' => 'Jalan harus berupa teks.',
            'kode_pos.string' => 'Kode pos harus berupa teks.',
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'no_passport.string' => 'No passport harus berupa teks.',
            'no_passport.unique' => 'No passport sudah terdaftar.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib diisi.',
            'jenis_kelamin.in' => 'Jenis kelamin harus l atau p.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tempat_lahir.string' => 'Tempat lahir harus berupa teks.',
            'tempat_lahir.max' => 'Tempat lahir maksimal 50 karakter.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'no_telepon.required' => 'No. telepon wajib diisi.',
            'no_telepon.string' => 'No. telepon harus berupa teks.',
            'no_telepon.max' => 'No. telepon maksimal 20 karakter.',
            'no_telepon_2.string' => 'No. telepon 2 harus berupa teks.',
            'no_telepon_2.max' => 'No. telepon 2 maksimal 20 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email harus format yang valid.',
            'email.max' => 'Email maksimal 100 karakter.',
            'email.unique' => 'Email sudah terdaftar.',
            'jenjang_pendidikan_terakhir.in' => 'Jenjang pendidikan terakhir tidak valid.',
            'nama_pendidikan_terakhir.string' => 'Nama pendidikan terakhir harus berupa teks.',
            'anak_keberapa.integer' => 'Anak keberapa harus berupa angka.',
            'anak_keberapa.min' => 'Anak keberapa minimal 1.',
            'dari_saudara.integer' => 'Jumlah saudara harus berupa angka.',
            'dari_saudara.min' => 'Jumlah saudara minimal 1.',
            'tinggal_bersama.string' => 'Tinggal bersama harus berupa teks.',
            'tinggal_bersama.max' => 'Tinggal bersama maksimal 40 karakter.',

            // Biodata Orang Tua
            'nama_ayah.string' => 'Nama ayah harus berupa teks.',
            'nama_ayah.max' => 'Nama ayah maksimal 100 karakter.',
            'nik_ayah.digits' => 'NIK ayah harus 16 digit.',
            'nik_ayah.unique' => 'NIK ayah sudah terdaftar.',
            'tempat_lahir_ayah.required' => 'Tempat lahir ayah wajib diisi.',
            'tempat_lahir_ayah.string' => 'Tempat lahir ayah harus berupa teks.',
            'tempat_lahir_ayah.max' => 'Tempat lahir ayah maksimal 50 karakter.',
            'tanggal_lahir_ayah.required' => 'Tanggal lahir ayah wajib diisi.',
            'tanggal_lahir_ayah.date' => 'Tanggal lahir ayah harus format tanggal.',
            'no_telepon_ayah.required' => 'No. telepon ayah wajib diisi.',
            'no_telepon_ayah.string' => 'No. telepon ayah harus berupa teks.',
            'no_telepon_ayah.max' => 'No. telepon ayah maksimal 20 karakter.',
            'pekerjaan_ayah.required' => 'Pekerjaan ayah wajib diisi.',
            'pendidikan_terakhir_ayah.in' => 'Pendidikan terakhir ayah tidak valid.',
            'penghasilan_ayah.required' => 'Penghasilan ayah wajib diisi.',

            'nama_ibu.string' => 'Nama ibu harus berupa teks.',
            'nama_ibu.max' => 'Nama ibu maksimal 100 karakter.',
            'nik_ibu.digits' => 'NIK ibu harus 16 digit.',
            'nik_ibu.unique' => 'NIK ibu sudah terdaftar.',
            'tempat_lahir_ibu.required' => 'Tempat lahir ibu wajib diisi.',
            'tempat_lahir_ibu.string' => 'Tempat lahir ibu harus berupa teks.',
            'tempat_lahir_ibu.max' => 'Tempat lahir ibu maksimal 50 karakter.',
            'tanggal_lahir_ibu.required' => 'Tanggal lahir ibu wajib diisi.',
            'tanggal_lahir_ibu.date' => 'Tanggal lahir ibu harus format tanggal.',
            'no_telepon_ibu.required' => 'No. telepon ibu wajib diisi.',
            'no_telepon_ibu.string' => 'No. telepon ibu harus berupa teks.',
            'no_telepon_ibu.max' => 'No. telepon ibu maksimal 20 karakter.',
            'pekerjaan_ibu.required' => 'Pekerjaan ibu wajib diisi.',
            'pendidikan_terakhir_ibu.in' => 'Pendidikan terakhir ibu tidak valid.',
            'penghasilan_ibu.required' => 'Penghasilan ibu wajib diisi.',

            'nama_wali.string' => 'Nama wali harus berupa teks.',
            'nama_wali.max' => 'Nama wali maksimal 100 karakter.',
            'nik_wali.digits' => 'NIK wali harus 16 digit.',
            'nik_wali.unique' => 'NIK wali sudah terdaftar.',
            'tempat_lahir_wali.required' => 'Tempat lahir wali wajib diisi.',
            'tempat_lahir_wali.string' => 'Tempat lahir wali harus berupa teks.',
            'tempat_lahir_wali.max' => 'Tempat lahir wali maksimal 50 karakter.',
            'tanggal_lahir_wali.required' => 'Tanggal lahir wali wajib diisi.',
            'tanggal_lahir_wali.date' => 'Tanggal lahir wali harus format tanggal.',
            'no_telepon_wali.required' => 'No. telepon wali wajib diisi.',
            'no_telepon_wali.string' => 'No. telepon wali harus berupa teks.',
            'no_telepon_wali.max' => 'No. telepon wali maksimal 20 karakter.',
            'pekerjaan_wali.required' => 'Pekerjaan wali wajib diisi.',
            'pendidikan_terakhir_wali.in' => 'Pendidikan terakhir wali tidak valid.',
            'penghasilan_wali.required' => 'Penghasilan wali wajib diisi.',

            // Rencana Pendidikan
            'lembaga_id.required' => 'Lembaga tujuan wajib dipilih.',
            'lembaga_id.exists' => 'Lembaga tidak valid.',
            'jurusan_id.exists' => 'Jurusan tidak valid.',
            'kelas_id.exists' => 'Kelas tidak valid.',
            'rombel_id.exists' => 'Rombel tidak valid.',

            // Rencana Domisili
            'wilayah_id.exists' => 'Wilayah tidak valid.',
            'blok_id.exists' => 'Blok tidak valid.',
            'kamar_id.exists' => 'Kamar tidak valid.',

            // Berkas
            'berkas.array' => 'Berkas harus berupa array.',
            'berkas.*.jenis_berkas_id.required' => 'Jenis berkas wajib dipilih.',
            'berkas.*.jenis_berkas_id.exists' => 'Jenis berkas tidak valid.',
            'berkas.*.file_path.required' => 'File berkas wajib diunggah.',
            'berkas.*.file_path.file' => 'File berkas harus berupa file.',
            'berkas.*.file_path.mimes' => 'File harus berupa PDF, JPG, JPEG, atau PNG.',
            'berkas.*.file_path.max' => 'Ukuran file maksimal 2 MB.',
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
