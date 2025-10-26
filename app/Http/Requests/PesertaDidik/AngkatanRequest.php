<?php

namespace App\Http\Requests\PesertaDidik;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AngkatanRequest extends FormRequest
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


    public function rules()
    {
        $routeParam = $this->route('id');
        $id = is_object($routeParam) ? $routeParam->id : $routeParam;

        return [
            'angkatan' => [
                'required',
                'string',
                'max:50',
                Rule::unique('angkatan', 'angkatan')
                    ->ignore($id)
                    ->where(function ($query) {
                        $query->where('kategori', $this->input('kategori'));
                    }),
            ],
            'kategori' => [
                'required',
                Rule::in(['santri', 'pelajar']),
            ],
            'tahun_ajaran_id' => [
                'nullable',
                'exists:tahun_ajaran,id',
            ],
            'status' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'angkatan.unique' => 'Angkatan sudah terdaftar untuk kategori tersebut.',
            'kategori.in' => 'Kategori hanya boleh santri atau pelajar.',
            'tahun_ajaran_id.exists' => 'Tahun ajaran tidak ditemukan.',
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
