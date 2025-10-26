<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreatePegawaiRequest extends FormRequest
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
            // ===== Biodata =====
            'nik' => 'nullable|digits:16',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:l,p',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'passport' => 'nullable|required_without_all:nik,no_kk|string|max:20',
            'no_telepon' => [
                'required',
                'string',
                'regex:/^(\+?[0-9]{8,15})$/',
            ],
            'no_telepon_2' => [
                'nullable',
                'string',
                'regex:/^\+[1-9][0-9]{7,14}$/',
            ],
            'email' => 'required|email|unique:biodata,email',
            'jalan' => 'required|string|max:255',
            'kode_pos' => 'nullable|string|max:10',
            'negara_id' => 'required|exists:negara,id',
            'provinsi_id' => 'required|exists:provinsi,id',
            'kabupaten_id' => 'required|exists:kabupaten,id',
            'kecamatan_id' => 'required|exists:kecamatan,id',
            'jenjang_pendidikan_terakhir' => 'required|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
            'nama_pendidikan_terakhir' => 'required|string|max:100',
            'anak_keberapa' => 'nullable|integer|min:1',
            'dari_saudara' => 'nullable|integer|min:1',
            'tinggal_bersama' => 'nullable|string|max:100',
            // 'smartcard' => 'nullable|string|max:50',
            'wafat' => 'required|in:1,0',

            // ===== Keluarga =====
            'no_kk' => 'nullable|digits:16',

            // ===== Warga Pesantren =====
            'niup' => 'nullable|string|max:50|unique:warga_pesantren,niup',

            // ===== Karyawan =====
            'karyawan' => 'nullable|in:1,0',
            'golongan_jabatan_id_karyawan' => 'nullable|required_if:karyawan,1|exists:golongan_jabatan,id',
            'lembaga_id_karyawan' => 'nullable|required_if:karyawan,1|exists:lembaga,id',
            'jabatan_karyawan' => 'nullable|required_if:karyawan,1|string|max:100',
            'keterangan_jabatan_karyawan' => 'nullable|required_if:karyawan,1|string|max:255',
            'tanggal_mulai_karyawan' => 'nullable|required_if:karyawan,1|date',

            // ===== Pengajar =====
            'pengajar'                 => 'nullable|in:1,0',
            'golongan_id_pengajar'    => 'nullable|required_if:pengajar,1|nullable|exists:golongan,id',
            'lembaga_id_pengajar'     => 'nullable|required_if:pengajar,1|nullable|exists:lembaga,id',
            'keterangan_jabatan_pengajar' => 'nullable|required_if:pengajar,1|string|max:255',
            'jabatan_pengajar'        => 'nullable|required_if:pengajar,1|nullable|string|max:100',
            'tanggal_mulai_pengajar'  => 'nullable|required_if:pengajar,1|nullable|date',

            // ===== Mata Pelajaran =====
            'mata_pelajaran' => 'nullable|array',
            'mata_pelajaran.*.kode_mapel' => 'nullable|required_with:mata_pelajaran|nullable|string|max:50',
            'mata_pelajaran.*.nama_mapel' => 'nullable|required_with:mata_pelajaran|nullable|string|max:100',

            // ===== Pengurus =====
            'pengurus' => 'nullable|in:1,0',
            'golongan_jabatan_id_pengurus' => 'nullable|required_if:pengurus,1|exists:golongan_jabatan,id',
            'jabatan_pengurus' => 'nullable|required_if:pengurus,1|string|max:100',
            'satuan_kerja_pengurus' => 'nullable|required_if:pengurus,1|string|max:100',
            'keterangan_jabatan_pengurus' => 'nullable|required_if:pengurus,1|string|max:255',
            'tanggal_mulai_pengurus' => 'nullable|required_if:pengurus,1|date',

            // ===== Wali Kelas =====
            'wali_kelas' => 'nullable|in:1,0',
            'lembaga_id_wali' => 'nullable|required_if:wali_kelas,1|exists:lembaga,id',
            'jurusan_id_wali' => 'nullable|required_if:wali_kelas,1|exists:jurusan,id',
            'kelas_id_wali' => 'nullable|required_if:wali_kelas,1|exists:kelas,id',
            'rombel_id_wali' => 'nullable|exists:rombel,id',
            // 'rombel_id_wali' => 'nullable|nullable_if:wali_kelas,1|exists:rombel,id',
            // 'jumlah_murid_wali' => 'nullable|required_if:wali_kelas,1|numeric|min:1',
            'periode_awal_wali' => 'nullable|required_if:wali_kelas,1|date',

            // ===== Berkas =====
            'berkas.*.jenis_berkas_id' => 'nullable|integer|exists:jenis_berkas,id',
            'berkas.*.file_path' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ];
    }

    public function messages()
    {
        return [
            // Biodata
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus berjumlah 16 digit.',
            'nama.required' => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan).',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'no_telepon.required' => 'Nomor telepon wajib diisi.',
            'no_telepon.string' => 'Nomor telepon harus berupa teks.',
            'no_telepon.regex' => 'Format nomor telepon tidak valid. Gunakan format internasional, contoh: +6281234567890.',

            'no_telepon_2.string' => 'Nomor telepon tambahan harus berupa teks.',
            'no_telepon_2.regex' => 'Format nomor telepon tambahan tidak valid. Gunakan format internasional, contoh: +6281234567890.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan di data biodata.',
            'jalan.required' => 'Alamat jalan wajib diisi.',
            'jalan.max' => 'Alamat jalan maksimal 255 karakter.',
            'kode_pos.max' => 'Kode pos maksimal 10 karakter.',
            'negara_id.required' => 'Negara wajib dipilih.',
            'negara_id.exists' => 'Negara yang dipilih tidak valid.',
            'provinsi_id.required' => 'Provinsi wajib dipilih.',
            'provinsi_id.exists' => 'Provinsi yang dipilih tidak valid.',
            'kabupaten_id.required' => 'Kabupaten wajib dipilih.',
            'kabupaten_id.exists' => 'Kabupaten yang dipilih tidak valid.',
            'kecamatan_id.required' => 'Kecamatan wajib dipilih.',
            'kecamatan_id.exists' => 'Kecamatan yang dipilih tidak valid.',
            'jenjang_pendidikan_terakhir.required' => 'Jenjang pendidikan terakhir wajib dipilih.',
            'jenjang_pendidikan_terakhir.in' => 'Jenjang pendidikan tidak valid.',
            'nama_pendidikan_terakhir.required' => 'Nama pendidikan terakhir wajib diisi.',
            'nama_pendidikan_terakhir.max' => 'Nama pendidikan terakhir maksimal 100 karakter.',
            'anak_keberapa.integer' => 'Anak keberapa harus berupa angka.',
            'anak_keberapa.min' => 'Anak keberapa minimal 1.',
            'dari_saudara.integer' => 'Jumlah saudara harus berupa angka.',
            'dari_saudara.min' => 'Jumlah saudara minimal 1.',
            'tinggal_bersama.max' => 'Tinggal bersama maksimal 100 karakter.',
            // 'smartcard.max' => 'Smartcard maksimal 50 karakter.',
            'wafat.required' => 'Status wafat wajib dipilih.',
            'wafat.in' => 'Nilai wafat harus 1 (ya) atau 0 (tidak).',

            // Keluarga
            'no_kk.digits' => 'Nomor KK harus 16 digit.',

            // Warga Pesantren
            'niup.unique' => 'NIUP Bersifat Unique dan sudah digunakan. Silakan gunakan nilai lain.',
            'niup.max' => 'NIUP maksimal 50 karakter.',

            // Karyawan
            'golongan_jabatan_id_karyawan.required_if' => 'Golongan jabatan wajib diisi saat karyawan dipilih.',
            'golongan_jabatan_id_karyawan.exists' => 'Golongan jabatan tidak valid.',
            'lembaga_id_karyawan.required_if' => 'Lembaga wajib diisi saat karyawan dipilih.',
            'lembaga_id_karyawan.exists' => 'Lembaga tidak valid.',
            'jabatan_karyawan.required_if' => 'Jabatan karyawan wajib diisi saat karyawan dipilih.',
            'jabatan_karyawan.max' => 'Jabatan karyawan maksimal 100 karakter.',
            'keterangan_jabatan_karyawan.required_if' => 'Keterangan jabatan karyawan wajib diisi saat karyawan dipilih.',
            'keterangan_jabatan_karyawan.max' => 'Keterangan jabatan karyawan maksimal 255 karakter.',
            'tanggal_mulai_karyawan.required_if' => 'Tanggal mulai karyawan wajib diisi saat karyawan dipilih.',
            'tanggal_mulai_karyawan.date' => 'Format tanggal mulai karyawan tidak valid.',

            // Pengajar
            'golongan_id_pengajar.required_if'       => 'Golongan pengajar wajib diisi jika status pengajar aktif.',
            'lembaga_id_pengajar.required_if'        => 'Lembaga pengajar wajib diisi jika status pengajar aktif.',
            'jabatan_pengajar.required_if'           => 'Jabatan pengajar wajib diisi jika status pengajar aktif.',
            'tanggal_mulai_pengajar.required_if'     => 'Tanggal mulai pengajar wajib diisi jika status pengajar aktif.',

            // Mata Pelajaran
            'mata_pelajaran.required'                => 'Data mata pelajaran wajib diisi.',
            'mata_pelajaran.*.kode_mapel.required'   => 'Kode mapel tidak boleh kosong.',
            'mata_pelajaran.*.nama_mapel.required'   => 'Nama mapel tidak boleh kosong.',
            // Pengurus
            'golongan_jabatan_id_pengurus.required_if' => 'Golongan jabatan pengurus wajib diisi saat pengurus dipilih.',
            'golongan_jabatan_id_pengurus.exists' => 'Golongan jabatan pengurus tidak valid.',
            'jabatan_pengurus.required_if' => 'Jabatan pengurus wajib diisi saat pengurus dipilih.',
            'jabatan_pengurus.max' => 'Jabatan pengurus maksimal 100 karakter.',
            'satuan_kerja_pengurus.required_if' => 'Satuan kerja pengurus wajib diisi saat pengurus dipilih.',
            'satuan_kerja_pengurus.max' => 'Satuan kerja pengurus maksimal 100 karakter.',
            'keterangan_jabatan_pengurus.required_if' => 'Keterangan jabatan pengurus wajib diisi saat pengurus dipilih.',
            'keterangan_jabatan_pengurus.max' => 'Keterangan jabatan pengurus maksimal 255 karakter.',
            'tanggal_mulai_pengurus.required_if' => 'Tanggal mulai pengurus wajib diisi saat pengurus dipilih.',
            'tanggal_mulai_pengurus.date' => 'Format tanggal mulai pengurus tidak valid.',

            // Wali Kelas
            'lembaga_id_wali.required_if' => 'Lembaga wali kelas wajib diisi saat wali kelas dipilih.',
            'lembaga_id_wali.exists' => 'Lembaga wali kelas tidak valid.',
            'jurusan_id_wali.required_if' => 'Jurusan wali kelas wajib diisi saat wali kelas dipilih.',
            'jurusan_id_wali.exists' => 'Jurusan wali kelas tidak valid.',
            'kelas_id_wali.required_if' => 'Kelas wali wajib diisi saat wali kelas dipilih.',
            'kelas_id_wali.exists' => 'Kelas wali tidak valid.',
            'rombel_id_wali.exists' => 'Rombel tidak valid.',
            'jumlah_murid_wali.required_if' => 'Jumlah murid wajib diisi saat wali kelas dipilih.',
            'jumlah_murid_wali.numeric' => 'Jumlah murid harus berupa angka.',
            'jumlah_murid_wali.min' => 'Jumlah murid minimal 1.',
            'periode_awal_wali.required_if' => 'Periode awal wali wajib diisi saat wali kelas dipilih.',
            'periode_awal_wali.date' => 'Format periode awal wali tidak valid.',

            // Berkas
            'berkas.*.jenis_berkas_id.integer' => 'Jenis berkas harus berupa angka.',
            'berkas.*.jenis_berkas_id.exists' => 'Jenis berkas tidak valid.',
            'berkas.*.file_path.file' => 'File berkas harus berupa file.',
            'berkas.*.file_path.mimes' => 'Format file berkas harus PDF, JPG, atau PNG.',
            'berkas.*.file_path.max' => 'Ukuran file maksimal 2MB.',
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
