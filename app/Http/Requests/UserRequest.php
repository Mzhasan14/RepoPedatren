<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalisasi input sebelum validasi.
     */
    public function rules(): array
    {
        $userId = optional($this->route('user'))->id;

        return [
            // === USER CORE ===
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'password' => $this->isMethod('post')
                ? ['required', 'string', 'min:8']
                : ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string'],
            'status' => ['required', 'boolean'],

            // === BIODATA ===
            'biodata_id' => ['nullable', 'uuid', 'exists:biodata,id', 'required_without:biodata'],
            'biodata' => ['required_without:biodata_id', 'array'],

            // Field biodata hanya divalidasi jika ada (gunakan sometimes)
            'biodata.negara_id' => ['sometimes', 'required', 'integer', 'exists:negara,id'],
            'biodata.provinsi_id' => ['sometimes', 'required', 'integer', 'exists:provinsi,id'],
            'biodata.kabupaten_id' => ['sometimes', 'required', 'integer', 'exists:kabupaten,id'],
            'biodata.kecamatan_id' => ['sometimes', 'required', 'integer', 'exists:kecamatan,id'],
            'biodata.jalan' => ['sometimes', 'required', 'string', 'max:255'],
            'biodata.kode_pos' => ['sometimes', 'required', 'string', 'max:10'],
            'biodata.nama' => ['sometimes', 'required', 'string', 'max:100'],
            'biodata.nik' => ['sometimes', 'nullable', 'required_without_all:biodata.passport', 'digits:16'],
            'biodata.no_kk' => ['sometimes', 'nullable', 'required_without_all:biodata.passport', 'digits:16'],
            'biodata.passport' => ['sometimes', 'nullable', 'required_without_all:biodata.nik,biodata.no_kk', 'string', 'max:20'],
            'biodata.jenis_kelamin' => ['sometimes', 'required', 'in:l,p'],
            'biodata.tanggal_lahir' => ['sometimes', 'required', 'date'],
            'biodata.tempat_lahir' => ['sometimes', 'required', 'string', 'max:50'],
            'biodata.no_telepon' => ['sometimes', 'required', 'string', 'max:20'],
            'biodata.no_telepon_2' => ['sometimes', 'nullable', 'string', 'max:20'],
            'biodata.email' => [
                'sometimes',
                'nullable',
                'email',
                'max:100',
                Rule::unique('biodata', 'email')->ignore($userId),
            ],
            'biodata.jenjang_pendidikan_terakhir' => [
                'sometimes',
                'nullable',
                Rule::in(['paud', 'sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
            ],
            'biodata.nama_pendidikan_terakhir' => ['sometimes', 'nullable', 'string', 'max:100'],
            'biodata.anak_keberapa' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'biodata.dari_saudara' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'biodata.tinggal_bersama' => ['sometimes', 'nullable', 'string', 'max:40'],
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'status.required' => 'Status wajib diisi.',
            'status.boolean' => 'Status hanya boleh bernilai true/false.',

            'biodata_id.uuid' => 'Biodata ID harus berupa UUID.',
            'biodata_id.exists' => 'Biodata ID tidak ditemukan.',
            'biodata.required' => 'Biodata wajib diisi.',

            'biodata.negara_id.required' => 'Negara wajib dipilih.',
            'biodata.negara_id.exists' => 'Negara tidak ditemukan.',
            'biodata.provinsi_id.required' => 'Provinsi wajib dipilih.',
            'biodata.provinsi_id.exists' => 'Provinsi tidak ditemukan.',
            'biodata.kabupaten_id.required' => 'Kabupaten wajib dipilih.',
            'biodata.kabupaten_id.exists' => 'Kabupaten tidak ditemukan.',
            'biodata.kecamatan_id.required' => 'Kecamatan wajib dipilih.',
            'biodata.kecamatan_id.exists' => 'Kecamatan tidak ditemukan.',
            'biodata.jalan.required' => 'Alamat jalan wajib diisi.',
            'biodata.jalan.max' => 'Alamat jalan maksimal 255 karakter.',
            'biodata.kode_pos.required' => 'Kode pos wajib diisi.',
            'biodata.kode_pos.max' => 'Kode pos maksimal 10 karakter.',

            'biodata.nama.required' => 'Nama wajib diisi.',
            'biodata.nama.max' => 'Nama maksimal 100 karakter.',
            'biodata.nik.digits' => 'NIK harus 16 angka.',
            'biodata.nik.required_without_all' => 'NIK harus diisi jika No KK dan Passport kosong.',
            'biodata.no_kk.digits' => 'No KK harus 16 angka.',
            'biodata.no_kk.required_without_all' => 'No KK harus diisi jika NIK dan Passport kosong.',
            'biodata.passport.max' => 'Nomor passport maksimal 20 karakter.',
            'biodata.passport.required_without_all' => 'Passport harus diisi jika NIK dan No KK kosong.',
            'biodata.jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'biodata.jenis_kelamin.in' => 'Jenis kelamin hanya boleh L atau P.',
            'biodata.tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'biodata.tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'biodata.tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'biodata.tempat_lahir.max' => 'Tempat lahir maksimal 50 karakter.',
            'biodata.no_telepon.required' => 'Nomor telepon wajib diisi.',
            'biodata.no_telepon.max' => 'Nomor telepon maksimal 20 karakter.',
            'biodata.no_telepon_2.max' => 'Nomor telepon alternatif maksimal 20 karakter.',
            'biodata.email.email' => 'Format email biodata tidak valid.',
            'biodata.email.max' => 'Email biodata maksimal 100 karakter.',
            'biodata.email.unique' => 'Email biodata sudah digunakan.',

            'biodata.jenjang_pendidikan_terakhir.in' => 'Jenjang pendidikan tidak valid.',
            'biodata.nama_pendidikan_terakhir.max' => 'Nama pendidikan terakhir maksimal 100 karakter.',
            'biodata.anak_keberapa.integer' => 'Anak keberapa harus berupa angka.',
            'biodata.anak_keberapa.min' => 'Anak keberapa minimal 1.',
            'biodata.dari_saudara.integer' => 'Jumlah saudara harus berupa angka.',
            'biodata.dari_saudara.min' => 'Jumlah saudara minimal 1.',
            'biodata.tinggal_bersama.max' => 'Tinggal bersama maksimal 40 karakter.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validasi gagal.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
