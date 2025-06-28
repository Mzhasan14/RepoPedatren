<?php

namespace App\Http\Requests\PesertaDidik\formulir;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BiodataRequest extends FormRequest
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
        $id = $this->route('id');
        $biodataId = DB::table('biodata')->where('id', $id)->value('id');

        return [
            'no_passport' => [
                'nullable',
                'string',
                'required_without_all:nik,no_kk',
                Rule::unique('biodata', 'no_passport')->ignore($biodataId),
            ],
            'no_kk' => 'nullable|digits:16|required_without_all:no_passport',
            'nik' => [
                'nullable',
                'digits:16',
                'required_without_all:no_passport',
                Rule::unique('biodata', 'nik')->ignore($biodataId),
            ],
            'nama' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:l,p',
            'tanggal_lahir' => 'required|date',
            'tempat_lahir' => 'required|string|max:50',
            'anak_keberapa' => 'nullable|integer|min:1',
            'dari_saudara' => 'nullable|integer|min:1',

            'tinggal_bersama' => 'nullable|string|max:40',
            'jenjang_pendidikan_terakhir' => 'nullable|in:paud,sd/mi,smp/mts,sma/smk/ma,d3,d4,s1,s2',
            'nama_pendidikan_terakhir' => 'nullable|string',

            'no_telepon' => 'required|string|max:20',
            'no_telepon_2' => 'nullable|string|max:20',
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('biodata', 'email')->ignore($biodataId),
            ],

            'negara_id' => 'required|exists:negara,id',
            'provinsi_id' => 'nullable|exists:provinsi,id',
            'kabupaten_id' => 'nullable|exists:kabupaten,id',
            'kecamatan_id' => 'nullable|exists:kecamatan,id',

            'jalan' => 'nullable|string',
            'kode_pos' => 'nullable|string',
            'wafat' => 'required|integer|in:0,1',
            'pekerjaan' => 'nullable|string',
            'penghasilan' => 'nullable|string',
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
