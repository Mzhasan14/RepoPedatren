<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\Santri;
use App\Models\Biodata;
use App\Models\Keluarga;
use App\Models\Pendidikan;
use App\Models\DomisiliSantri;
use Illuminate\Support\Carbon;
use App\Models\Pendidikan\Rombel;
use Illuminate\Support\Facades\DB;
use App\Models\Kewilayahan\Wilayah;
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
        $biodata = Biodata::with(['keluarga', 'berkas.jenisBerkas'])->find($bioId);

        if (! $biodata) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan.',
            ];
        }

        // Ambil data pekerjaan dan penghasilan dari orang_tua_wali jika ada
        $orangTuaWali = \App\Models\OrangTuaWali::where('id_biodata', $biodata->id)->first();

        $pekerjaan = $orangTuaWali ? $orangTuaWali->pekerjaan : null;
        $penghasilan = $orangTuaWali ? $orangTuaWali->penghasilan : null;

        // Cari berkas dengan jenis "Pas foto"
        $pasFoto = $biodata->berkas
            ->firstWhere(fn($berkas) => $berkas->jenisBerkas?->nama_jenis_berkas === 'Pas Foto');

        return [
            'status' => true,
            'data' => [
                'id' => $biodata->id,
                'no_passport' => $biodata->no_passport,
                'no_kk' => optional($biodata->keluarga->sortByDesc('created_at')->first())->no_kk,
                'nik' => $biodata->nik,
                'nama' => $biodata->nama,
                'jenis_kelamin' => $biodata->jenis_kelamin,
                'tanggal_lahir' => $biodata->tanggal_lahir
                    ? Carbon::parse($biodata->tanggal_lahir)->format('Y-m-d')
                    : null,
                'tempat_lahir' => $biodata->tempat_lahir,
                'anak_keberapa' => $biodata->anak_keberapa,
                'dari_saudara' => $biodata->dari_saudara,
                'tinggal_bersama' => $biodata->tinggal_bersama,
                'jenjang_pendidikan_terakhir' => $biodata->jenjang_pendidikan_terakhir,
                'nama_pendidikan_terakhir' => $biodata->nama_pendidikan_terakhir,
                'no_telepon' => $biodata->no_telepon,
                'no_telepon_2' => $biodata->no_telepon_2,
                'email' => $biodata->email,
                'negara_id' => $biodata->negara_id,
                'provinsi_id' => $biodata->provinsi_id,
                'kabupaten_id' => $biodata->kabupaten_id,
                'kecamatan_id' => $biodata->kecamatan_id,
                'jalan' => $biodata->jalan,
                'kode_pos' => $biodata->kode_pos,
                'wafat' => (bool) $biodata->wafat,
                'pas_foto_url' => $pasFoto ? url($pasFoto->file_path) : null,
                // Tambahkan dua baris di bawah ini
                'pekerjaan' => $pekerjaan,
                'penghasilan' => $penghasilan,
            ],
        ];
    }


    private function checkGenderConsistency($bioId, $newGender)
    {
        // Konversi 'L' => 'putra', 'P' => 'putri'
        $genderText = ($newGender == 'l') ? 'putra' : 'putri';

        // Cek Pendidikan → Rombel → gender_rombel
        $pendidikan = Pendidikan::where('biodata_id', $bioId)->first();
        $genderRombel = null;
        if ($pendidikan && $pendidikan->rombel_id) {
            // Pastikan relasi Rombel ada
            $rombel = Rombel::find($pendidikan->rombel_id);
            if ($rombel && $rombel->gender_rombel) {
                $genderRombel = $rombel->gender_rombel;
            }
        }
        if ($genderRombel && $genderRombel !== $genderText) {
            return [
                'status' => false,
                'message' => 'Jenis kelamin tidak konsisten dengan rombel saat ini (' . $genderRombel . ').',
            ];
        }

        // Cari Santri berdasarkan biodata_id
        $santri = Santri::where('biodata_id', $bioId)->first();

        $domisili = null;
        $kategoriWilayah = null;

        if ($santri) {
            $domisili = DomisiliSantri::where('santri_id', $santri->id)->first();
        }

        if ($domisili && $domisili->wilayah_id) {
            $wilayah = Wilayah::find($domisili->wilayah_id);
            if ($wilayah && $wilayah->kategori) {
                $kategoriWilayah = $wilayah->kategori;
            }
        }

        if ($kategoriWilayah && $kategoriWilayah !== $genderText) {
            return [
                'status' => false,
                'message' => 'Jenis kelamin tidak konsisten dengan wilayah saat ini (' . $kategoriWilayah . ').',
            ];
        }

        return ['status' => true];
    }

    public function update(array $input, string $bioId): array
    {
        // Cari data berdasarkan ID
        $biodata = Biodata::find($bioId);

        // Jika data tidak ditemukan
        if (! $biodata) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan.',
            ];
        }

        // Cek jika ada perubahan jenis_kelamin
        $jenisKelaminBaru = $input['jenis_kelamin'] ?? $biodata->jenis_kelamin;
        if ($jenisKelaminBaru !== $biodata->jenis_kelamin) {
            $cekGender = $this->checkGenderConsistency($bioId, $jenisKelaminBaru);
            if (! $cekGender['status']) {
                return [
                    'status' => false,
                    'message' => $cekGender['message'],
                ];
            }
        }

        $noKK = Keluarga::where('id_biodata', $bioId)->value('no_kk');

        if (! $noKK && ! empty($input['no_kk'])) {
            // Belum ada, maka buat baru
            Keluarga::create([
                'no_kk' => $input['no_kk'],
                'id_biodata' => $bioId,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
                'status' => true,
            ]);
        } elseif (! empty($input['no_kk']) && $input['no_kk'] !== $noKK) {
            // Sudah ada, tapi nilainya berbeda → update
            Keluarga::where('id_biodata', $bioId)->update([
                'no_kk' => $input['no_kk'],
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);
        }

        // Update hanya data yang dikirimkan
        $biodata->no_passport = $input['no_passport'] ?? $biodata->no_passport;
        $biodata->nik = $input['nik'] ?? $biodata->nik;
        $biodata->nama = $input['nama'] ?? $biodata->nama;
        $biodata->jenis_kelamin = $jenisKelaminBaru;
        $biodata->tanggal_lahir = isset($input['tanggal_lahir']) ? \Carbon\Carbon::parse($input['tanggal_lahir']) : $biodata->tanggal_lahir;
        $biodata->tempat_lahir = $input['tempat_lahir'] ?? $biodata->tempat_lahir;
        $biodata->anak_keberapa = $input['anak_keberapa'] ?? $biodata->anak_keberapa;
        $biodata->dari_saudara = $input['dari_saudara'] ?? $biodata->dari_saudara;
        $biodata->tinggal_bersama = $input['tinggal_bersama'] ?? $biodata->tinggal_bersama;
        $biodata->jenjang_pendidikan_terakhir = $input['jenjang_pendidikan_terakhir'] ?? $biodata->jenjang_pendidikan_terakhir;
        $biodata->nama_pendidikan_terakhir = $input['nama_pendidikan_terakhir'] ?? $biodata->nama_pendidikan_terakhir;
        $biodata->no_telepon = $input['no_telepon'] ?? $biodata->no_telepon;
        $biodata->no_telepon_2 = $input['no_telepon_2'] ?? $biodata->no_telepon_2;
        $biodata->email = $input['email'] ?? $biodata->email;
        $biodata->negara_id = $input['negara_id'] ?? $biodata->negara_id;
        $biodata->provinsi_id = $input['provinsi_id'] ?? $biodata->provinsi_id;
        $biodata->kabupaten_id = $input['kabupaten_id'] ?? $biodata->kabupaten_id;
        $biodata->kecamatan_id = $input['kecamatan_id'] ?? $biodata->kecamatan_id;
        $biodata->jalan = $input['jalan'] ?? $biodata->jalan;
        $biodata->kode_pos = $input['kode_pos'] ?? $biodata->kode_pos;
        $biodata->kode_pos = $input['smartcard'] ?? $biodata->smartcard;
        $biodata->wafat = (bool) $input['wafat'] ?? $biodata->wafat;

        if (! $biodata->isDirty()) {
            return [
                'status' => false,
                'message' => 'Tidak ada perubahan data.',
            ];
        }

        $biodata->updated_by = Auth::id();

        $biodata->save();

        $orangTuaWali = \App\Models\OrangTuaWali::where('id_biodata', $biodata->id)->first();
        if ($orangTuaWali) {
            $updateData = [];
            if (array_key_exists('pekerjaan', $input)) {
                $updateData['pekerjaan'] = $input['pekerjaan'];
            }
            if (array_key_exists('penghasilan', $input)) {
                $updateData['penghasilan'] = $input['penghasilan'];
            }
            if (!empty($updateData)) {
                $orangTuaWali->update($updateData);
            }
        }

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
