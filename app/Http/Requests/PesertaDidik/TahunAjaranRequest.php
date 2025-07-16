<?php

namespace App\Http\Requests\PesertaDidik;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TahunAjaranRequest extends FormRequest
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
        $today = now()->toDateString();
        $routeParam = $this->route('id');
        $id = is_object($routeParam) ? $routeParam->id : $routeParam;

        return [
            'tahun_ajaran' => [
                'required',
                'string',
                'max:9',
                Rule::unique('tahun_ajaran', 'tahun_ajaran')->ignore($id),
            ],
            'tanggal_mulai' => [
                'required',
                'date',
                'after_or_equal:' . $today,
            ],
            'tanggal_selesai' => [
                'required',
                'date',
                'after_or_equal:tanggal_mulai',
            ],
            'status' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh kurang dari hari ini.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'tahun_ajaran.unique' => 'Tahun ajaran sudah ada.',
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
