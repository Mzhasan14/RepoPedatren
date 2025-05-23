<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
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
            'no_passport' => 'nullable|string|max:50',
            'no_telepon' => 'required|string|max:20',
            'no_telepon_2' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'jalan' => 'nullable|string|max:255',
            'kode_pos' => 'nullable|string|max:10',
            'negara_id' => 'required|exists:negara,id',
            'provinsi_id' => 'nullable|exists:provinsi,id',
            'kabupaten_id' => 'nullable|exists:kabupaten,id',
            'kecamatan_id' => 'nullable|exists:kecamatan,id',
            'jenjang_pendidikan_terakhir' => 'nullable|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
            'nama_pendidikan_terakhir' => 'nullable|string|max:100',
            'anak_keberapa' => 'nullable|integer|min:1',
            'dari_saudara' => 'nullable|integer|min:1',
            'tinggal_bersama' => 'nullable|string|max:100',
            'smartcard' => 'nullable|string|max:50',
            'wafat' => 'nullable|in:1,0',

            // ===== Keluarga =====
            'no_kk' => 'nullable|digits:16',

            // ===== Warga Pesantren =====
            'niup' => 'nullable|string|max:50',

            // ===== Karyawan =====
            'karyawan' => 'nullable|in:1,0',
            'golongan_jabatan_id_karyawan' => 'required_if:karyawan,1|exists:golongan_jabatan,id',
            'lembaga_id_karyawan' => 'nullable|exists:lembaga,id',
            'jabatan_karyawan' => 'nullable|string|max:100',
            'keterangan_jabatan_karyawan' => 'nullable|string|max:255',
            'tanggal_mulai_karyawan' => 'nullable|date',

            // ===== Pengajar =====
            'pengajar' => 'nullable|in:1,0',
            'golongan_id_pengajar' => 'required_if:pengajar,1|exists:golongan,id',
            'lembaga_id_pengajar' => 'nullable|exists:lembaga,id',
            'jabatan_pengajar' => 'nullable|string|max:100',
            'tanggal_mulai_pengajar' => 'nullable|date',

            // ===== Materi Ajar =====
            'materi_ajar.*.nama_materi' => 'nullable|string|max:255', 
            'materi_ajar.*.jumlah_menit' => 'nullable|integer|min:0',
            'tanggal_mulai_materi' => 'nullable|date',

            // ===== Pengurus =====
            'pengurus' => 'nullable|in:1,0',
            'golongan_jabatan_id_pengurus' => 'required_if:pengurus,1|exists:golongan_jabatan,id',
            'jabatan_pengurus' => 'nullable|string|max:100',
            'satuan_kerja_pengurus' => 'nullable|string|max:100',
            'keterangan_jabatan_pengurus' => 'nullable|string|max:255',
            'tanggal_mulai_pengurus' => 'nullable|date',

            // ===== Wali Kelas =====
            'wali_kelas' => 'nullable|in:1,0',
            'lembaga_id_wali' => 'required_if:wali_kelas,1|exists:lembaga,id',
            'jurusan_id_wali' => 'nullable|exists:jurusan,id',
            'kelas_id_wali' => 'nullable|exists:kelas,id',
            'rombel_id_wali' => 'nullable|exists:rombel,id',
            'jumlah_murid_wali' => 'nullable|numeric|min:1',
            'periode_awal_wali' => 'nullable|date',

            // ===== Berkas =====
            'berkas.*.jenis_berkas_id' => 'nullable|integer|exists:jenis_berkas,id',
            'berkas.*.file_path' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ];
    }
    public function messages()
    {
        return [
                'nik.required' => 'NIK wajib diisi.',
                'nik.digits' => 'NIK harus berjumlah 16 digit.',
                'nama.required' => 'Nama lengkap wajib diisi.',
                'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
                'jenis_kelamin.in' => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan).',
                'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
                'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
                'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
                
                'no_telepon.required' => 'Nomor telepon wajib diisi.',
                'email.email' => 'Format email tidak valid.',

                'negara_id.required' => 'Negara wajib dipilih.',
                'negara_id.exists' => 'Negara yang dipilih tidak valid.',
                'provinsi_id.required' => 'Provinsi wajib dipilih.',
                'provinsi_id.exists' => 'Provinsi yang dipilih tidak valid.',
                'kabupaten_id.required' => 'Kabupaten wajib dipilih.',
                'kabupaten_id.exists' => 'Kabupaten yang dipilih tidak valid.',
                'kecamatan_id.required' => 'Kecamatan wajib dipilih.',
                'kecamatan_id.exists' => 'Kecamatan yang dipilih tidak valid.',

                'anak_keberapa.integer' => 'Anak keberapa harus berupa angka.',
                'anak_keberapa.min' => 'Anak keberapa minimal 1.',
                'dari_saudara.integer' => 'Dari saudara harus berupa angka.',
                'dari_saudara.min' => 'Dari saudara minimal 1.',

                'wafat.in' => 'Nilai wafat harus 1 (ya) atau 0 (tidak).',

                'berkas.*.jenis_berkas_id.integer' => 'Jenis berkas harus berupa angka.',
                'berkas.*.jenis_berkas_id.exists' => 'Jenis berkas tidak valid.',
                'berkas.*.file_path.file' => 'File berkas harus berupa file yang valid.',
                'berkas.*.file_path.mimes' => 'Format file berkas harus PDF, JPG, atau PNG.',
                'berkas.*.file_path.max' => 'Ukuran file maksimal 2MB.',

                'materi_ajar.*.nama_materi.string' => 'Nama materi harus berupa teks.',
                'materi_ajar.*.nama_materi.max' => 'Nama materi maksimal 255 karakter.',
                'materi_ajar.*.jumlah_menit.integer' => 'Jumlah menit harus berupa angka.',
                'materi_ajar.*.jumlah_menit.min' => 'Jumlah menit minimal 0.',

                'jumlah_murid.numeric' => 'Jumlah murid harus berupa angka.',
                'jumlah_murid.min' => 'Jumlah murid minimal 1.',

                'tanggal_mulai_karyawan.date' => 'Format tanggal mulai karyawan tidak valid.',
                'tanggal_mulai_pengajar.date' => 'Format tanggal mulai pengajar tidak valid.',
                'tanggal_mulai_pengurus.date' => 'Format tanggal mulai pengurus tidak valid.',
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
