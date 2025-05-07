<?php

namespace App\Services\PesertaDidik\Formulir;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BiodataService
{

    public function edit(string $bioId)
    {
        $biodata = DB::table('biodata')
            ->where('id', $bioId)
            ->select(
                'id',
                'no_passport',
                'nik',
                'nama',
                'jenis_kelamin',
                'tanggal_lahir',
                'tempat_lahir',
                'anak_keberapa',
                'dari_saudara',
                'tinggal_bersama',
                'jenjang_pendidikan_terakhir',
                'nama_pendidikan_terakhir',
                'no_telepon',
                'no_telepon_2',
                'email',
                'negara_id',
                'provinsi_id',
                'kabupaten_id',
                'kecamatan_id',
                'jalan',
                'kode_pos',
                'wafat',
            )
            ->first();

        if (!$biodata) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }
        return ['status' => true, 'data' => $biodata];
    }

    public function update(array $data, string $bioId)
    {
        $biodata = DB::table('biodata')->where('id', $bioId)->first();
        
        if (!$biodata) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        $biodataUpdate = [
            'negara_id'                   => $data['negara_id'],
            'provinsi_id'                 => $data['provinsi_id'] ?? null,
            'kabupaten_id'                => $data['kabupaten_id'] ?? null,
            'kecamatan_id'               => $data['kecamatan_id'] ?? null,
            'jalan'                       => $data['jalan'] ?? null,
            'kode_pos'                    => $data['kode_pos'] ?? null,
            'nama'                        => $data['nama'],
            'no_passport'                 => $data['no_passport'] ?? null,
            'jenis_kelamin'               => $data['jenis_kelamin'],
            'tanggal_lahir'               => $data['tanggal_lahir'],
            'tempat_lahir'                => $data['tempat_lahir'],
            'nik'                         => $data['nik'] ?? null,
            'no_telepon'                  => $data['no_telepon'],
            'no_telepon_2'                => $data['no_telepon_2'] ?? null,
            'email'                       => $data['email'],
            'jenjang_pendidikan_terakhir' => $data['jenjang_pendidikan_terakhir'] ?? null,
            'nama_pendidikan_terakhir'    => $data['nama_pendidikan_terakhir'] ?? null,
            'anak_keberapa'               => $data['anak_keberapa'] ?? null,
            'dari_saudara'                => $data['dari_saudara'] ?? null,
            'tinggal_bersama'             => $data['tinggal_bersama'] ?? null,
        ];

        $hasChanged = false;
        foreach ($biodataUpdate as $key => $val) {
            if ($biodata->$key != $val) {
                $hasChanged = true;
                break;
            }
        }

        if ($hasChanged) {
            $biodataUpdate['updated_by'] = Auth::id();
            $biodataUpdate['updated_at'] = now();
            $newData = DB::table('biodata')->where('id', $bioId)->update($biodataUpdate);
            return ['status' => true, 'data' => $newData];
        }

        return ['status' => false, 'message' => 'Tidak ada perubahan data'];
    }
}
