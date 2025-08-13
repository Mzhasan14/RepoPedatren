<?php

namespace App\Http\Requests\PesertaDidik;

use App\Models\JadwalSholat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class JadwalSholatRequest extends FormRequest
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
            'sholat_id'      => 'required|exists:sholat,id',
            'jam_mulai'      => 'required|date_format:H:i:s',
            'jam_selesai'    => 'required|date_format:H:i:s|after:jam_mulai',
            'berlaku_mulai'  => 'required|date',
            'berlaku_sampai' => 'nullable|date|after_or_equal:berlaku_mulai',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $this->cekBentrok($validator);
        });
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

    protected function cekBentrok($validator)
    {
        $mulai = $this->berlaku_mulai;
        $sampai = $this->berlaku_sampai ?? $this->berlaku_mulai;

        $query = JadwalSholat::where('sholat_id', $this->sholat_id)
            ->where(function ($q) use ($mulai, $sampai) {
                $q->where('berlaku_mulai', '<=', $sampai)
                    ->where(function ($q2) use ($mulai) {
                        $q2->whereNull('berlaku_sampai')
                            ->orWhere('berlaku_sampai', '>=', $mulai);
                    });
            })
            ->where(function ($q) {
                $q->whereBetween('jam_mulai', [$this->jam_mulai, $this->jam_selesai])
                    ->orWhereBetween('jam_selesai', [$this->jam_mulai, $this->jam_selesai])
                    ->orWhere(function ($q2) {
                        $q2->where('jam_mulai', '<=', $this->jam_mulai)
                            ->where('jam_selesai', '>=', $this->jam_selesai);
                    });
            });

        if ($this->route('jadwal_sholat')) {
            $query->where('id', '!=', $this->route('jadwal_sholat')->id);
        }

        if ($query->exists()) {
            $validator->errors()->add('jam_mulai', 'Jadwal sholat bentrok dengan jadwal yang sudah ada.');
        }
    }
}
