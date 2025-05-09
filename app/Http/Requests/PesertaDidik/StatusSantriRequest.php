<?php

namespace App\Http\Requests\PesertaDidik;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StatusSantriRequest extends FormRequest
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
        $biodataId = DB::table('santri')->where('id', $id)->value('id');
        return [
            'nis' => [
                'nullable',
                'string',
                Rule::unique('santri', 'nis')->ignore($biodataId),
            ],
            'tanggal_masuk' => 'required|date',
            'tanggal_keluar' => 'nullable|date',
            'status' => 'nullable'
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
