<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\Biodata;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BiodataService
{

    // public function store(array $input): array
    // {
    //     $userId = Auth::id();
    //     if (! $userId) {
    //         return [
    //             'status'  => false,
    //             'message' => 'Pengguna tidak terautentikasi.',
    //         ];
    //     }

    //     return DB::transaction(function () use ($input, $userId) {
    //         $biodata = Biodata::create([
    //             'no_passport'                  => $input['no_passport']                  ?? null,
    //             'nik'                          => $input['nik']                          ?? null,
    //             'nama'                         => $input['nama'],
    //             'jenis_kelamin'                => $input['jenis_kelamin']               ?? null,
    //             'tanggal_lahir'                => isset($input['tanggal_lahir'])
    //                 ? Carbon::parse($input['tanggal_lahir'])
    //                 : null,
    //             'tempat_lahir'                 => $input['tempat_lahir']                ?? null,
    //             'anak_keberapa'                => $input['anak_keberapa']               ?? null,
    //             'dari_saudara'                 => $input['dari_saudara']                ?? null,
    //             'tinggal_bersama'              => $input['tinggal_bersama']             ?? null,
    //             'jenjang_pendidikan_terakhir'  => $input['jenjang_pendidikan_terakhir'] ?? null,
    //             'nama_pendidikan_terakhir'     => $input['nama_pendidikan_terakhir']    ?? null,
    //             'no_telepon'                   => $input['no_telepon'],
    //             'no_telepon_2'                 => $input['no_telepon_2']                ?? null,
    //             'email'                        => $input['email'],
    //             'negara_id'                    => $input['negara_id']                   ?? null,
    //             'provinsi_id'                  => $input['provinsi_id']                 ?? null,
    //             'kabupaten_id'                 => $input['kabupaten_id']                ?? null,
    //             'kecamatan_id'                 => $input['kecamatan_id']                ?? null,
    //             'jalan'                        => $input['jalan']                       ?? null,
    //             'kode_pos'                     => $input['kode_pos']                    ?? null,
    //             'wafat'                        => $input['wafat']                       ?? false,
    //             'status'                       => true,
    //             'created_by'                   => $userId,
    //         ]);

    //         return [
    //             'status' => true,
    //             'data'   => $biodata,
    //         ];
    //     });
    // }

    public function show(string $bioId): array
    {
        $biodata = Biodata::find($bioId);

        if (! $biodata) {
            return [
                'status'  => false,
                'message' => 'Data tidak ditemukan.',
            ];
        }

        return [
            'status' => true,
            'data'   => $biodata,
        ];
    }


    public function update(array $input, string $bioId): array
    {
        // Cari data berdasarkan ID
        $biodata = Biodata::find($bioId);

        // Jika data tidak ditemukan
        if (!$biodata) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan.',
            ];
        }

        // Update hanya data yang dikirimkan
        $biodata->no_passport                 = $input['no_passport'] ?? $biodata->no_passport;
        $biodata->nik                         = $input['nik'] ?? $biodata->nik;
        $biodata->no_kk                         = $input['no_kk'] ?? $biodata->no_kk;
        $biodata->nama                        = $input['nama'] ?? $biodata->nama;
        $biodata->jenis_kelamin               = $input['jenis_kelamin'] ?? $biodata->jenis_kelamin;
        $biodata->tanggal_lahir               = isset($input['tanggal_lahir']) ? \Carbon\Carbon::parse($input['tanggal_lahir']) : $biodata->tanggal_lahir;
        $biodata->tempat_lahir                = $input['tempat_lahir'] ?? $biodata->tempat_lahir;
        $biodata->anak_keberapa               = $input['anak_keberapa'] ?? $biodata->anak_keberapa;
        $biodata->dari_saudara                = $input['dari_saudara'] ?? $biodata->dari_saudara;
        $biodata->tinggal_bersama             = $input['tinggal_bersama'] ?? $biodata->tinggal_bersama;
        $biodata->jenjang_pendidikan_terakhir = $input['jenjang_pendidikan_terakhir'] ?? $biodata->jenjang_pendidikan_terakhir;
        $biodata->nama_pendidikan_terakhir    = $input['nama_pendidikan_terakhir'] ?? $biodata->nama_pendidikan_terakhir;
        $biodata->no_telepon                  = $input['no_telepon'] ?? $biodata->no_telepon;
        $biodata->no_telepon_2                = $input['no_telepon_2'] ?? $biodata->no_telepon_2;
        $biodata->email                       = $input['email'] ?? $biodata->email;
        $biodata->negara_id                   = $input['negara_id'] ?? $biodata->negara_id;
        $biodata->provinsi_id                 = $input['provinsi_id'] ?? $biodata->provinsi_id;
        $biodata->kabupaten_id                = $input['kabupaten_id'] ?? $biodata->kabupaten_id;
        $biodata->kecamatan_id                = $input['kecamatan_id'] ?? $biodata->kecamatan_id;
        $biodata->jalan                       = $input['jalan'] ?? $biodata->jalan;
        $biodata->kode_pos                    = $input['kode_pos'] ?? $biodata->kode_pos;
        $biodata->wafat                       = $input['wafat'] ?? $biodata->wafat;

        // Cek apakah ada data yang berubah
        if (!$biodata->isDirty()) {
            return [
                'status' => false,
                'message' => 'Tidak ada perubahan data.',
            ];
        }

        // Set siapa yang mengupdate
        $biodata->updated_by = Auth::id();

        // Simpan perubahan
        $biodata->save();

        return [
            'status' => true,
            'data' => $biodata,
        ];
    }
}

// $batchUuid = Str::uuid()->toString();
// ->tap(function ($activity) use ($batchUuid) {
                //     if ($activity) {
                //         $activity->batch_uuid = $batchUuid;
                //     }
                // })
