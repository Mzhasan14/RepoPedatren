<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
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
        'negara' => 'required|string|max:255',
        'provinsi' => 'required|string|max:255',
        'kabupaten' => 'required|string|max:255',
        'kecamatan' => 'required|string|max:255',
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
        'wafat' => 'required|boolean|in:0,1',

        // ======= Keluarga (Opsional) =======
        'no_kk' => 'nullable|string|max:16',

        // ======= Warga Pesantren (Opsional) =======
        'niup' => 'nullable|string|max:50',

        // // ======= Pegawai =======
        // 'nama_lembaga_pegawai' => 'nullable|string|max:255',
        // 'nama_jurusan_pegawai' => 'nullable|string|max:255',
        // 'nama_kelas_pegawai' => 'nullable|string|max:255',
        // 'nama_rombel_pegawai' => 'nullable|string|max:255',
        // 'gender_rombel' => 'required_with:nama_rombel_pegawai|in:putra,putri',

         // === TABEL LEMBAGA (WALI KELAS) ===
        'nama_lembaga_wali_kelas' => 'nullable|string|max:100',

        // === TABEL JURUSAN ===
        'nama_jurusan_wali_kelas' => 'nullable|string|max:100',

        // === TABEL KELAS ===
        'nama_kelas_wali_kelas' => 'nullable|string|max:100',

        // === TABEL ROMBEL ===
        'nama_rombel_wali_kelas' => 'nullable|string|max:100',

        // === TABEL WALI_KELAS ===
        'wali_kelas' => 'nullable|boolean',
        'jumlah_murid_wali_kelas' => 'required_if:wali_kelas,true|integer|min:1',


        // ======= Karyawan (Opsional) =======
        'karyawan' => 'nullable|boolean',
        'jabatan_karyawan' => 'nullable|required_if:karyawan,true|string|max:255',
        'nama_golongan_jabatan_karyawan' => 'nullable|string|max:255',
        'nama_lembaga_karyawan' => 'nullable|string|max:255',
        'keterangan_jabatan_karyawan' => 'nullable|string',
        'tanggal_mulai_karyawan' => 'nullable|date',

        // ======= Pengajar (Opsional) =======
        'pengajar' => 'nullable|boolean',
        'jabatan_pengajar' => 'nullable|required_if:pengajar,true|string|max:255',
        'tahun_masuk_pengajar' => 'nullable|date',
        'nama_golongan' => 'nullable|string|max:255',
        'nama_kategori_golongan' => 'nullable|string|max:255',
        'nama_lembaga_pengajar' => 'nullable|string|max:255',
        'materi_ajar' => 'nullable|array',
        'materi_ajar.*.nama_materi' => 'required_with:materi_ajar|string|max:255',
        'materi_ajar.*.jumlah_menit' => 'required_with:materi_ajar|integer|min:1',

        // ======= Pengurus (Opsional) =======
        'pengurus' => 'nullable|boolean',
        'jabatan_pengurus' => 'nullable|required_if:pengurus,true|string|max:255',
        'satuan_kerja_pengurus' => 'nullable|required_if:pengurus,true|string|max:255',
        'keterangan_jabatan_pengurus' => 'nullable|string',
        'tanggal_mulai_pengurus' => 'nullable|date',
        'nama_golongan_jabatan_pengurus' => 'nullable|string|max:255',
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
