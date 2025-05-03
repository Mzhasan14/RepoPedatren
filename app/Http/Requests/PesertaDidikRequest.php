<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PesertaDidikRequest extends FormRequest
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
            'negara_id' => 'required|exists:negara,id',
            'provinsi_id' => 'nullable|exists:provinsi,id',
            'kabupaten_id' => 'nullable|exists:kabupaten,id',
            'kecamatan_id' => 'nullable|exists:kecamatan,id',
            'jalan' => 'nullable|string',
            'kode_pos' => 'nullable|string',
            'nama' => 'required|string|max:100',
            'no_passport' => 'nullable|string',
            'jenis_kelamin' => 'required|in:l,p',
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'required|string|max:50',
            'nik' => 'nullable|digits:16',
            'no_telepon' => 'required|string|max:20',
            'no_telepon_2' => 'nullable|string|max:20',
            'email' => 'required|email|max:100|unique:biodata,email',
            'jenjang_pendidikan_terakhir' => 'nullable|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
            'nama_pendidikan_terakhir' => 'nullable|string',
            'anak_keberapa' => 'nullable|integer|min:1',
            'dari_saudara' => 'nullable|integer|min:1',
            'tinggal_bersama' => 'nullable|string|max:40',
            // pendidikan
            'lembaga_id' => 'required|exists:lembaga,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'rombel_id' => 'nullable|exists:rombel,id',
            // domisili
            'wilayah_id' => 'nullable|exists:wilayah,id',
            'blok_id'    => 'nullable|exists:blok,id',
            'kamar_id'   => 'nullable|exists:kamar,id',
            // status
            'status_biodata' => 'nullable|boolean',
            'status_santri' => 'nullable|in:aktif,do,berhenti,alumni',
            'status_riwayat_domisili' => 'nullable|in:aktif,do,berhenti,alumni,pindah',
            'status_riwayat_pendidikan' => 'nullable|in:aktif,do,berhenti,alumni,pindah',
        ];
    }

    public function messages(): array
    {
        return [
            'negara_id.required' => 'Field negara harus diisi.',
            'negara_id.exists' => 'Negara yang dipilih tidak valid.',
            'provinsi_id.exists' => 'Provinsi yang dipilih tidak valid.',
            'kabupaten_id.exists' => 'Kabupaten yang dipilih tidak valid.',
            'kecamatan_id.exists' => 'Kecamatan yang dipilih tidak valid.',
            'jalan.string' => 'Field jalan harus berupa teks.',
            'kode_pos.string' => 'Field kode pos harus berupa teks.',
            'nama.required' => 'Field nama harus diisi.',
            'nama.string' => 'Field nama harus berupa teks.',
            'nama.max' => 'Panjang nama maksimal 100 karakter.',
            'no_passport.string' => 'Field passport harus berupa teks.',
            'jenis_kelamin.required' => 'Field jenis kelamin harus diisi.',
            'jenis_kelamin.in' => 'Field jenis kelamin harus bernilai salah satu dari: l, p.',
            'tanggal_lahir.required' => 'Field tanggal lahir harus diisi.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'tempat_lahir.required' => 'Field tempat lahir harus diisi.',
            'tempat_lahir.string' => 'Field tempat lahir harus berupa teks.',
            'tempat_lahir.max' => 'Panjang tempat lahir maksimal 50 karakter.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit angka.',
            'no_telepon.required' => 'Field nomor telepon harus diisi.',
            'no_telepon.string' => 'Field nomor telepon harus berupa teks.',
            'no_telepon.max' => 'Panjang nomor telepon maksimal 20 karakter.',
            'no_telepon_2.string' => 'Field nomor telepon 2 harus berupa teks.',
            'no_telepon_2.max' => 'Panjang nomor telepon 2 maksimal 20 karakter.',
            'email.required' => 'Field email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Panjang email maksimal 100 karakter.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'jenjang_pendidikan_terakhir.in' => 'Jenjang pendidikan terakhir tidak valid.',
            'nama_pendidikan_terakhir.string' => 'Field nama pendidikan terakhir harus berupa teks.',
            'anak_keberapa.integer' => 'Field anak keberapa harus berupa angka.',
            'anak_keberapa.min' => 'Field anak keberapa minimal bernilai 1.',
            'dari_saudara.integer' => 'Field dari saudara harus berupa angka.',
            'dari_saudara.min' => 'Field dari saudara minimal bernilai 1.',
            'tinggal_bersama.string' => 'Field tinggal bersama harus berupa teks.',
            'tinggal_bersama.max' => 'Panjang tinggal bersama maksimal 40 karakter.',
            'lembaga_id.required' => 'Field lembaga harus diisi.',
            'lembaga_id.exists' => 'Lembaga yang dipilih tidak valid.',
            'jurusan_id.exists' => 'Jurusan yang dipilih tidak valid.',
            'kelas_id.exists' => 'Kelas yang dipilih tidak valid.',
            'rombel_id.exists' => 'Rombel yang dipilih tidak valid.',
            'wilayah_id.exists' => 'Wilayah yang dipilih tidak valid.',
            'blok_id.exists' => 'Blok yang dipilih tidak valid.',
            'kamar_id.exists' => 'Kamar yang dipilih tidak valid.',
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
