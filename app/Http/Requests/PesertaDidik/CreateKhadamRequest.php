<?php

namespace App\Http\Requests\PesertaDidik;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateKhadamRequest extends FormRequest
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
            'jalan' => 'nullable|string|max:255',
            'kode_pos' => 'nullable|string|max:10',
            'nama' => 'required|string|max:100',

            // Aturan fleksibel antara nik/no_kk dan passport
            'nik' => 'nullable|required_without_all:passport|digits:16',
            'no_kk' => 'nullable|required_without_all:passport|digits:16',
            'passport' => 'nullable|required_without_all:nik,no_kk|string|max:20',

            'jenis_kelamin' => 'required|in:l,p',
            'tanggal_lahir' => 'nullable|date',
            'tempat_lahir' => 'nullable|string|max:50',
            'no_telepon' => 'nullable|string|max:20',
            'no_telepon_2' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100|unique:biodata,email',

            'jenjang_pendidikan_terakhir' => 'nullable|string',
            'nama_pendidikan_terakhir' => 'nullable|string|max:100',
            'anak_keberapa' => 'nullable|integer|min:1',
            'dari_saudara' => 'nullable|integer|min:1',
            'tinggal_bersama' => 'nullable|string|max:40',

            // khadam
            'keterangan' => 'required|string',
            'tanggal_mulai' => 'required|date',

            'kartu_rfid' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            // Biodata Diri
            'negara_id.required' => 'Negara wajib dipilih.',
            'negara_id.exists' => 'Negara yang dipilih tidak valid.',
            'provinsi_id.required' => 'Provinsi wajib dipilih.',
            'provinsi_id.exists' => 'Provinsi yang dipilih tidak valid.',
            'kabupaten_id.required' => 'Kabupaten/Kota wajib dipilih.',
            'kabupaten_id.exists' => 'Kabupaten/Kota yang dipilih tidak valid.',
            'kecamatan_id.required' => 'Kecamatan wajib dipilih.',
            'kecamatan_id.exists' => 'Kecamatan yang dipilih tidak valid.',
            'jalan.required' => 'Alamat jalan wajib diisi.',
            'jalan.max' => 'Alamat jalan maksimal :max karakter.',
            'kode_pos.required' => 'Kode pos wajib diisi.',
            'kode_pos.max' => 'Kode pos maksimal :max karakter.',
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nama.max' => 'Nama lengkap maksimal :max karakter.',

            // Aturan fleksibel NIK/No KK/Passport
            'nik.required_without_all' => 'NIK wajib diisi jika passport tidak diisi.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit.',
            'no_kk.required_without_all' => 'Nomor KK wajib diisi jika passport tidak diisi.',
            'no_kk.digits' => 'Nomor KK harus terdiri dari 16 digit.',
            'passport.required_without_all' => 'Nomor passport wajib diisi jika NIK dan No KK tidak diisi.',
            'passport.max' => 'Nomor passport maksimal :max karakter.',

            // Data Lain
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid. Pilih Laki-laki (l) atau Perempuan (p).',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tempat_lahir.max' => 'Tempat lahir maksimal :max karakter.',
            'no_telepon.required' => 'Nomor telepon wajib diisi.',
            'no_telepon.max' => 'Nomor telepon maksimal :max karakter.',
            'no_telepon_2.max' => 'Nomor telepon tambahan maksimal :max karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal :max karakter.',
            'email.unique' => 'Email sudah terdaftar.',

            'jenjang_pendidikan_terakhir.in' => 'Jenjang pendidikan terakhir tidak valid.',
            'nama_pendidikan_terakhir.max' => 'Nama pendidikan terakhir maksimal :max karakter.',
            'anak_keberapa.integer' => 'Urutan anak harus berupa angka.',
            'anak_keberapa.min' => 'Urutan anak minimal :min.',
            'dari_saudara.integer' => 'Jumlah saudara harus berupa angka.',
            'dari_saudara.min' => 'Jumlah saudara minimal :min.',
            'tinggal_bersama.max' => 'Keterangan tinggal bersama maksimal :max karakter.',

            // Khadam
            'keterangan.required' => 'Keterangan khadam wajib diisi.',
            'keterangan.string' => 'Keterangan khadam harus berupa teks.',
            'tanggal_mulai.required' => 'Tanggal mulai khadam wajib diisi.',
            'tanggal_mulai.date' => 'Tanggal mulai khadam harus berupa tanggal yang valid.',

            'smartcard' => 'nullable|string',
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
