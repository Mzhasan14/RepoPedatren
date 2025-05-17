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
        'nik' => ['required', 'string', 'size:16'],
        'nama' => ['required', 'string', 'max:255'],
        'tanggal_lahir' => ['required', 'date'],
        'jenis_kelamin' => 'required|in:l,p',
        'tempat_lahir' => ['required', 'string', 'max:255'],
        'negara_id' => ['required', 'integer', 'exists:negara,id'],
        'provinsi_id' => ['required', 'integer', 'exists:provinsi,id'],
        'kabupaten_id' => ['required', 'integer', 'exists:kabupaten,id'],
        'kecamatan_id' => ['required', 'integer', 'exists:kecamatan,id'],
        'jalan' => ['required', 'string', 'max:255'],
        'kode_pos' => ['nullable', 'string', 'max:10'],
        'no_passport' => ['nullable', 'string', 'max:50'],
        'no_telepon' => ['nullable', 'string', 'max:20'],
        'no_telepon_2' => ['nullable', 'string', 'max:20'],
        'email' => ['nullable', 'email', 'max:255'],
        'jenjang_pendidikan_terakhir' => 'nullable|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
        'nama_pendidikan_terakhir' => ['nullable', 'string', 'max:255'],
        'anak_keberapa' => ['nullable', 'integer', 'min:1'],
        'dari_saudara' => ['nullable', 'integer', 'min:1'],
        'tinggal_bersama' => ['nullable', 'string', 'max:255'],
        'smartcard' => ['nullable', 'string', 'max:255'],
        'wafat' => ['nullable', 'boolean'],

        // Data keluarga (opsional)
        'no_kk' => ['nullable', 'string', 'max:20'],

        // Data warga pesantren
        'niup' => ['nullable', 'string', 'max:50'],

        // Data berkas
        'berkas' => ['nullable', 'array'],
        'berkas.*.file_path' => ['required', 'file', 'max:10240'], // max 10MB misal
        'berkas.*.jenis_berkas_id' => ['required', 'integer', 'exists:jenis_berkas,id'],

        // Data pegawai
        'karyawan' => ['nullable', 'boolean'],
        'golongan_jabatan_id' => ['nullable', 'integer', 'exists:golongan_jabatan,id'],
        'lembaga_id' => ['nullable', 'integer', 'exists:lembaga,id'],
        'jabatan' => ['nullable', 'string', 'max:255'],
        'keterangan_jabatan' => ['nullable', 'string', 'max:255'],
        'tanggal_mulai' => ['nullable', 'date'],

        // Data pengajar
        'pengajar' => ['nullable', 'boolean'],
        'golongan_id' => ['nullable', 'integer', 'exists:golongan,id'],

        // Materi ajar - array of materi
        'materi_ajar' => ['nullable', 'array'],
        'materi_ajar.*.nama' => ['required_with:materi_ajar', 'string', 'max:255'],
        'materi_ajar.*.menit' => ['nullable', 'integer', 'min:1'],

        // Data pengurus
        'pengurus' => ['nullable', 'boolean'],

        // Data wali kelas
        'wali_kelas' => ['nullable', 'boolean'],
        'jurusan_id' => ['nullable', 'integer', 'exists:jurusan,id'],
        'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
        'rombel_id' => ['nullable', 'integer', 'exists:rombel,id'],
        'jumlah_murid' => ['nullable', 'integer', 'min:0'],
        'satuan_kerja' => ['nullable', 'string', 'max:255'],
        ];
    }
public function messages()
{
    return [
        'nik.required' => 'NIK wajib diisi.',
        'nik.size' => 'NIK harus berjumlah 16 karakter.',
        'nik.unique' => 'NIK sudah terdaftar di sistem.',
        'nama.required' => 'Nama lengkap wajib diisi.',
        'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
        'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
        'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
        'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
        'jenis_kelamin.in' => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan).',
        'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
        'negara_id.required' => 'Negara wajib dipilih.',
        'negara_id.exists' => 'Negara yang dipilih tidak valid.',
        'provinsi_id.required' => 'Provinsi wajib dipilih.',
        'provinsi_id.exists' => 'Provinsi yang dipilih tidak valid.',
        'kabupaten_id.required' => 'Kabupaten wajib dipilih.',
        'kabupaten_id.exists' => 'Kabupaten yang dipilih tidak valid.',
        'kecamatan_id.required' => 'Kecamatan wajib dipilih.',
        'kecamatan_id.exists' => 'Kecamatan yang dipilih tidak valid.',
        'jalan.required' => 'Alamat jalan wajib diisi.',
        'kode_pos.max' => 'Kode pos maksimal 10 karakter.',
        'email.email' => 'Format email tidak valid.',
        'anak_keberapa.integer' => 'Anak keberapa harus berupa angka.',
        'dari_saudara.integer' => 'Dari saudara harus berupa angka.',
        'wafat.boolean' => 'Nilai wafat harus true atau false.',

        'berkas.array' => 'Data berkas harus berupa array.',
        'berkas.*.file_path.required' => 'File berkas wajib diunggah.',
        'berkas.*.file_path.file' => 'File berkas harus berupa file yang valid.',
        'berkas.*.jenis_berkas_id.required' => 'Jenis berkas wajib dipilih.',
        'berkas.*.jenis_berkas_id.exists' => 'Jenis berkas tidak valid.',

        'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',

        'materi_ajar.array' => 'Data materi ajar harus berupa array.',
        'materi_ajar.*.nama.required_with' => 'Nama materi wajib diisi jika materi ajar diinput.',
        'materi_ajar.*.nama.max' => 'Nama materi maksimal 255 karakter.',
        'materi_ajar.*.menit.integer' => 'Jumlah menit harus berupa angka.',
        'materi_ajar.*.menit.min' => 'Jumlah menit minimal 1.',

        'jumlah_murid.integer' => 'Jumlah murid harus berupa angka.',
        'jumlah_murid.min' => 'Jumlah murid minimal 0.',
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
