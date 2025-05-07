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
        // Negara dan Wilayah
        'negara' => ['required', 'string', 'max:100'],
        'provinsi' => ['required', 'string', 'max:100'],
        'kabupaten' => ['required', 'string', 'max:100'],
        'kecamatan' => ['required', 'string', 'max:100'],

        // Alamat dan identitas
        'jalan' => ['nullable', 'string', 'max:255'],
        'kode_pos' => ['nullable', 'numeric'],
        'nama' => ['required', 'string', 'max:255'],
        'no_passport' => ['nullable', 'string', 'max:50'],
        'tanggal_lahir' => ['required', 'date'],
        'jenis_kelamin' => ['required', 'in:L,P'],
        'tempat_lahir' => ['required', 'string', 'max:100'],
        'nik' => ['required', 'numeric', 'digits:16'],
        'no_telepon' => ['nullable', 'string', 'max:20'],
        'no_telepon_2' => ['nullable', 'string', 'max:20'],
        'email' => ['nullable', 'email', 'max:100'],
        'jenjang_pendidikan_terakhir' => ['nullable', 'string', 'max:50'],
        'nama_pendidikan_terakhir' => ['nullable', 'string', 'max:100'],
        'anak_keberapa' => ['nullable', 'integer'],
        'dari_saudara' => ['nullable', 'integer'],
        'tinggal_bersama' => ['nullable', 'string', 'max:100'],
        'smartcard' => ['nullable', 'string', 'max:50'],
        'wafat' => ['nullable', 'boolean'],

        // Keluarga dan Warga Pesantren
        'no_kk' => ['nullable', 'string', 'max:20'],
        'niup' => ['nullable', 'string', 'max:30'],

        // Lembaga & Struktur Pendidikan
        'nama_lembaga_pegawai' => ['nullable', 'string', 'max:100'],
        'nama_jurusan' => ['nullable', 'string', 'max:100'],
        'nama_kelas' => ['nullable', 'string', 'max:100'],
        'nama_rombel' => ['nullable', 'string', 'max:100'],
        'gender_rombel' => ['required_with:nama_rombel', 'in:L,P'],

        // Pegawai
        'status_aktif' => ['required', 'in:aktif,tidak aktif'],


        // Karyawan
        'karyawan' => ['required', 'in:0,1'],
        'jabatan' => ['required_with:karyawan,pengajar,pengurus', 'string', 'max:100'],
        'nama_golongan_jabatan_karyawan' => ['nullable', 'string', 'max:100'],
        'nama_lembaga_karyawan' => ['nullable', 'string', 'max:100'],
        'keterangan_jabatan' => ['nullable', 'string', 'max:255'],
        'tanggal_mulai' => ['nullable', 'date'],

        // Pengajar
        'pengajar' => ['required', 'in:0,1'],
        'tahun_masuk' => ['required_with:pengajar', 'date'],
        'nama_kategori_golongan' => ['nullable', 'string', 'max:100'],
        'nama_golongan' => ['nullable', 'string', 'max:100'],
        'materi_ajar' => ['nullable', 'array'],
        'materi_ajar.*.nama_materi' => ['required_with:materi_ajar', 'string', 'max:100'],
        'materi_ajar.*.jumlah_menit' => ['required_with:materi_ajar', 'integer', 'min:1'],

        // Pengurus
        'pengurus' => ['required', 'in:0,1'],
        'satuan_kerja' => ['required_with:pengurus', 'string', 'max:100'],
        'nama_golongan_jabatan_pengurus' => ['nullable', 'string', 'max:100'],
        'nama_lembaga_pengurus' => ['nullable', 'string', 'max:100'],
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
