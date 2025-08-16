<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
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
            // User core
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'password' => $this->isMethod('post') ? ['required', 'string', 'min:8'] : ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string'], // bisa ditambah exists jika perlu
            'status' => ['required', 'boolean'],

            // Biodata
            'biodata_id' => ['nullable', 'uuid', 'exists:biodata,id'],
            'biodata' => ['required', 'array'],
            'biodata.negara_id' => ['required', 'integer', 'exists:negara,id'],
            'biodata.provinsi_id' => ['required', 'integer', 'exists:provinsi,id'],
            'biodata.kabupaten_id' => ['required', 'integer', 'exists:kabupaten,id'],
            'biodata.kecamatan_id' => ['required', 'integer', 'exists:kecamatan,id'],
            'biodata.jalan' => ['required', 'string', 'max:255'],
            'biodata.kode_pos' => ['required', 'string', 'max:10'],
            'biodata.nama' => ['required', 'string', 'max:100'],
            'biodata.nik' => ['nullable', 'required_without_all:biodata.passport', 'digits:16'],
            'biodata.no_kk' => ['nullable', 'required_without_all:biodata.passport', 'digits:16'],
            'biodata.passport' => ['nullable', 'required_without_all:biodata.nik,biodata.no_kk', 'string', 'max:20'],
            'biodata.jenis_kelamin' => ['required', 'in:l,p'],
            'biodata.tanggal_lahir' => ['required', 'date'],
            'biodata.tempat_lahir' => ['required', 'string', 'max:50'],
            'biodata.no_telepon' => ['required', 'string', 'max:20'],
            'biodata.no_telepon_2' => ['nullable', 'string', 'max:20'],
            'biodata.email' => ['nullable', 'email', 'max:100', Rule::unique('biodata', 'email')->ignore($userId)],

            'biodata.jenjang_pendidikan_terakhir' => ['nullable', Rule::in(['paud', 'sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2'])],
            'biodata.nama_pendidikan_terakhir' => ['nullable', 'string', 'max:100'],
            'biodata.anak_keberapa' => ['nullable', 'integer', 'min:1'],
            'biodata.dari_saudara' => ['nullable', 'integer', 'min:1'],
            'biodata.tinggal_bersama' => ['nullable', 'string', 'max:40'],
        ];
    }

    public function messages(): array
    {
        return [
            'biodata.nik.digits' => 'NIK harus 16 angka.',
            'biodata.nik.required_without_all' => 'NIK atau No KK / Passport harus diisi.',
            'biodata.no_kk.digits' => 'No KK harus 16 angka.',
            'biodata.passport.required_without_all' => 'Passport harus diisi jika NIK dan No KK kosong.',
            'biodata.email.unique' => 'Email biodata sudah digunakan.',
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
