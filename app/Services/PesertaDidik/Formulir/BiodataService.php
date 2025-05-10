<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\Biodata;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BiodataService
{
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Validasi input data
            if (empty($data['nama']) || empty($data['no_telepon']) || empty($data['email'])) {
                return [
                    'status' => false,
                    'message' => 'Nama, nomor telepon, dan email harus diisi.',
                    'data' => null
                ];
            }

            if (!Auth::id()) {
                return [
                    'status' => false,
                    'message' => 'Pengguna tidak terautentikasi',
                    'data' => null
                ];
            }

            // Buat entri biodata baru
            $biodata = Biodata::create([
                'no_passport'                 => $data['no_passport'] ?? null,
                'nik'                          => $data['nik'] ?? null,
                'nama'                         => $data['nama'],
                'jenis_kelamin'               => $data['jenis_kelamin'],
                'tanggal_lahir'               => $data['tanggal_lahir'],
                'tempat_lahir'                => $data['tempat_lahir'],
                'anak_keberapa'               => $data['anak_keberapa'] ?? null,
                'dari_saudara'                => $data['dari_saudara'] ?? null,
                'tinggal_bersama'             => $data['tinggal_bersama'] ?? null,
                'jenjang_pendidikan_terakhir' => $data['jenjang_pendidikan_terakhir'] ?? null,
                'nama_pendidikan_terakhir'    => $data['nama_pendidikan_terakhir'] ?? null,
                'no_telepon'                  => $data['no_telepon'],
                'no_telepon_2'                => $data['no_telepon_2'] ?? null,
                'email'                       => $data['email'],
                'negara_id'                   => $data['negara_id'],
                'provinsi_id'                 => $data['provinsi_id'] ?? null,
                'kabupaten_id'                => $data['kabupaten_id'] ?? null,
                'kecamatan_id'                => $data['kecamatan_id'] ?? null,
                'jalan'                       => $data['jalan'] ?? null,
                'kode_pos'                    => $data['kode_pos'] ?? null,
                'wafat'                       => $data['wafat'] ?? false,
                'status'                      => true,
                'created_by'                  => Auth::id(),
                'created_at'                  => now(),
                'updated_at'                  => now(),
            ]);

            // Log activity untuk menyimpan biodata baru
            // $batchUuid = Str::uuid()->toString();

            activity('biodata_create')
                ->performedOn($biodata)
                ->withProperties([
                    'new_attributes' => $biodata->getAttributes(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                // ->tap(function ($activity) use ($batchUuid) {
                //     if ($activity) {
                //         $activity->batch_uuid = $batchUuid;
                //     }
                // })
                ->event('create_biodata')
                ->log('Biodata baru berhasil disimpan');

            $newBio = Biodata::find($biodata->id);

            return [
                'status' => true,
                'data' => $newBio
            ];
        });
    }


    public function edit(string $bioId)
    {
        $biodata = Biodata::select(
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
            'wafat'
        )
            ->find($bioId);

        if (!$biodata) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $biodata];
    }

    public function update(array $data, string $bioId)
    {
        return DB::transaction(function () use ($data, $bioId) {
            $biodata = Biodata::find($bioId);

            if (!$biodata) {
                return [
                    'status' => false,
                    'message' => 'Data tidak ditemukan',
                    'data' => null
                ];
            }

            $biodataUpdate = [
                'negara_id'                   => $data['negara_id'],
                'provinsi_id'                 => $data['provinsi_id'] ?? null,
                'kabupaten_id'                => $data['kabupaten_id'] ?? null,
                'kecamatan_id'                => $data['kecamatan_id'] ?? null,
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

            $biodata->fill($biodataUpdate);

            if (!$biodata->isDirty()) {
                return [
                    'status' => false,
                    'message' => 'Tidak ada perubahan data',
                    'data' => null
                ];
            }

            if (!Auth::id()) {
                return [
                    'status' => false,
                    'message' => 'Pengguna tidak terautentikasi',
                    'data' => null
                ];
            }

            $biodataBefore = $biodata->getOriginal();
            $biodata->updated_by = Auth::id();
            $biodata->updated_at = now();
            $biodata->save();

            // $batchUuid = Str::uuid()->toString();

            activity('biodata_update')
                ->performedOn($biodata)
                ->withProperties([
                    'before' => $biodataBefore,
                    'after' => $biodataUpdate,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                // ->tap(function ($activity) use ($batchUuid) {
                //     if ($activity) {
                //         $activity->batch_uuid = $batchUuid;
                //     }
                // })
                ->event('update_biodata')
                ->log('Biodata telah berhasil diperbarui');

            return [
                'status' => true,
                'data' => $biodata
            ];
        });
    }
}
