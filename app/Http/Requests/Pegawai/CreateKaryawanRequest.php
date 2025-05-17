<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateKaryawanRequest extends FormRequest
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
                        // Biodata
            'nik' => 'required|digits:16',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:l,p',
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'required|string|max:255',
            'no_telepon' => 'required|string|max:20',
            'no_telepon_2' => 'nullable|string|max:20',
            'email' => 'required|email',
            'negara_id' => 'required|exists:negara,id',
            'provinsi_id' => 'required|exists:provinsi,id',
            'kabupaten_id' => 'required|exists:kabupaten,id',
            'kecamatan_id' => 'required|exists:kecamatan,id',
            'jalan' => 'required|string|max:255',
            'no_passport' => 'nullable|string|max:50',
            'kode_pos' => 'required|digits:5',
            
            // Keluarga (optional)
            'no_kk' => 'nullable|digits:16',
            
            // Warga Pesantren (optional)
            'niup' => 'nullable|string|max:50',
            
            // Berkas (optional)
            'berkas.*.jenis_berkas_id' => 'nullable|integer|exists:jenis_berkas,id',
            'berkas.*.file_path' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            
            // Karyawan
            'golongan_jabatan_id' => 'required|exists:golongan_jabatan,id',
            'lembaga_id' => 'required|exists:lembaga,id',
            'jabatan' => 'required|string|max:255',
            'keterangan_jabatan' => 'nullable|string|max:255',
            'tanggal_mulai' => 'required|date',
            
            // Pendidikan
            'jenjang_pendidikan_terakhir' => 'nullable|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
            'nama_pendidikan_terakhir' => 'required|string|max:255',
            
            // Info Tambahan
            'anak_keberapa' => 'nullable|integer|min:1',
            'dari_saudara' => 'nullable|integer|min:1',
            'tinggal_bersama' => 'nullable|string|max:255',
            'smartcard' => 'nullable|string|max:50',
            'wafat' => 'nullable|boolean',
        ];
    }
    public function messages()
    {
        return [
            // Biodata
            'nik.required' => 'NIK wajib diisi',
            'nik.digits' => 'NIK harus 16 digit',
            'nik.unique' => 'NIK sudah terdaftar',
            'nama.required' => 'Nama lengkap wajib diisi',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi',
            'no_telepon.required' => 'Nomor telepon wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'negara_id.required' => 'Negara wajib dipilih',
            'negara_id.exists' => 'Negara tidak valid',
            'provinsi_id.required' => 'Provinsi wajib dipilih',
            'provinsi_id.exists' => 'Provinsi tidak valid',
            'kabupaten_id.required' => 'Kabupaten wajib dipilih',
            'kabupaten_id.exists' => 'Kabupaten tidak valid',
            'kecamatan_id.required' => 'Kecamatan wajib dipilih',
            'kecamatan_id.exists' => 'Kecamatan tidak valid',
            'jalan.required' => 'Alamat jalan wajib diisi',
            'kode_pos.required' => 'Kode pos wajib diisi',
            'kode_pos.digits' => 'Kode pos harus 5 digit',
            
            // Keluarga
            'no_kk.digits' => 'Nomor KK harus 16 digit',
            
            // Warga Pesantren
            'niup.unique' => 'NIUP sudah terdaftar',
            
            // Berkas
            'berkas.*.file_path.file' => 'Berkas harus berupa file',
            'berkas.*.file_path.mimes' => 'Format file harus PDF, JPG, atau PNG',
            'berkas.*.file_path.max' => 'Ukuran file maksimal 2MB',
            
            // Karyawan
            'golongan_jabatan_id.required' => 'Golongan jabatan wajib dipilih',
            'golongan_jabatan_id.exists' => 'Golongan jabatan tidak valid',
            'lembaga_id.required' => 'Lembaga wajib dipilih',
            'lembaga_id.exists' => 'Lembaga tidak valid',
            'jabatan.required' => 'Jabatan wajib diisi',
            
            // Pendidikan
            'jenjang_pendidikan_terakhir.required' => 'Jenjang pendidikan terakhir wajib diisi',
            'nama_pendidikan_terakhir.required' => 'Nama pendidikan terakhir wajib diisi',
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
