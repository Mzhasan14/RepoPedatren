<?php

namespace App\Http\Requests\PesertaDidik;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SemesterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $routeParam = $this->route('id');
        $id = is_object($routeParam) ? $routeParam->id : $routeParam;

        return [
            'tahun_ajaran_id' => [
                'required',
                'exists:tahun_ajaran,id',
            ],
            'semester' => [
                'required',
                Rule::in(['ganjil', 'genap']),
                // Custom unique: kombinasi tahun_ajaran_id + semester harus unik
                Rule::unique('semester')->where(function ($query) {
                    return $query->where('tahun_ajaran_id', $this->tahun_ajaran_id)
                        ->where('semester', $this->semester);
                })->ignore($id),
            ],
            'status' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'tahun_ajaran_id.required' => 'Tahun ajaran wajib diisi.',
            'tahun_ajaran_id.exists' => 'Tahun ajaran tidak ditemukan.',
            'semester.in' => 'Semester harus ganjil atau genap.',
            'semester.unique' => 'Semester ini sudah terdaftar pada tahun ajaran yang sama.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Validasi gagal. Mohon periksa kembali input Anda.',
            'error' => $errors,
        ], 422);

        throw new HttpResponseException($response);
    }
}
