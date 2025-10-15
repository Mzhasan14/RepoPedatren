<?php

namespace App\Http\Requests\PosPay;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class InquiryRequest extends FormRequest
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
            'RequestID' => 'required|string|max:32',
            'KodeBiller' => 'required|string|max:5',
            'TerminalCode' => 'required|string|max:15',
            'TerminalName' => 'required|string|max:150',
            'BillKey1' => 'required|string|max:32',
            'BillKey2' => 'nullable|string|max:32',
            'BillKey3' => 'nullable|string|max:32',
        ];
    }

    public function messages(): array
    {
        return [
            // RequestID
            'RequestID.required' => 'Request ID wajib diisi.',
            'RequestID.string'   => 'Request ID harus berupa teks.',
            'RequestID.max'      => 'Request ID maksimal 32 karakter.',

            // KodeBiller
            'KodeBiller.required' => 'Kode Biller wajib diisi.',
            'KodeBiller.string'   => 'Kode Biller harus berupa teks.',
            'KodeBiller.max'      => 'Kode Biller maksimal 5 karakter.',

            // TerminalCode
            'TerminalCode.required' => 'Kode Terminal wajib diisi.',
            'TerminalCode.string'   => 'Kode Terminal harus berupa teks.',
            'TerminalCode.max'      => 'Kode Terminal maksimal 15 karakter.',

            // TerminalName
            'TerminalName.required' => 'Nama Terminal wajib diisi.',
            'TerminalName.string'   => 'Nama Terminal harus berupa teks.',
            'TerminalName.max'      => 'Nama Terminal maksimal 150 karakter.',

            // BillKey1
            'BillKey1.required' => 'BillKey1 wajib diisi.',
            'BillKey1.string'   => 'BillKey1 harus berupa teks.',
            'BillKey1.max'      => 'BillKey1 maksimal 32 karakter.',

            // BillKey2
            'BillKey2.string' => 'BillKey2 harus berupa teks.',
            'BillKey2.max'    => 'BillKey2 maksimal 32 karakter.',

            // BillKey3
            'BillKey3.string' => 'BillKey3 harus berupa teks.',
            'BillKey3.max'    => 'BillKey3 maksimal 32 karakter.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $response = response()->json([
            'message' => 'Validasi gagal. Mohon periksa kembali input Anda.',
            'errors' => $errors,
        ], 422);

        throw new HttpResponseException($response);
    }
}
