<?php

namespace App\Services\Keluarga;

use App\Models\Keluarga;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class KeluargaService
{

    public function show(int $id): array
    {
        $kel = Keluarga::with(['biodata', 'orangTua.hubunganKeluarga'])->find($id);
        if (! $kel) {
            return ['status' => false, 'message' => 'Data tidak ditemukan.'];
        }

        return [
            'status' => true,
            'data'   => [
                'id'             => $kel->id,
                'biodata_id'    => $kel->biodata->id,
                'no_kk'       => $kel->no_kk,
                'nama'   => $kel->biodata->nama,
                'hubungan'   => optional(optional($kel->orangTua)->hubunganKeluarga)->nama_status,
                'status'     => $kel->status
            ],
        ];
    }

    public function update(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $keluarga = Keluarga::find($id);

            if (! $keluarga) {
                return ['status' => false, 'message' => 'Data keluarga tidak ditemukan.'];
            }

            $keluarga->update([
                'no_kk'     => $input['no_kk'],
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            return [
                'status' => true,
                'message' => 'Data keluarga berhasil diperbarui.',
                'data' => $keluarga,
            ];
        });
    }

    public function pindahKkBaru(string $biodataId, string $noKkBaru): array
    {
        $keluarga = Keluarga::where('id_biodata', $biodataId)->first();

        if (! $keluarga) {
            return ['status' => false, 'message' => 'Data anggota keluarga tidak ditemukan.'];
        }

        DB::transaction(function () use ($biodataId, $noKkBaru) {
            Keluarga::where('id_biodata', $biodataId)->update([
                'no_kk' => $noKkBaru,
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);
        });

        return ['status' => true, 'message' => 'Anggota keluarga berhasil dipindahkan ke KK baru.'];
    }

    public function pindahkanSeluruhKk(array $input, int $id): array
    {
        if (!isset($input['no_kk'])) {
            return [
                'status' => false,
                'message' => 'Kolom no_kk harus diisi.'
            ];
        }

        $keluarga = Keluarga::find($id);

        if (! $keluarga) {
            return [
                'status' => false,
                'message' => 'Data keluarga tidak ditemukan.',
                'data'  => null
            ];
        }

        $noKkLama = $keluarga->no_kk;
        $noKkBaru = $input['no_kk'];

        $new = DB::transaction(function () use ($noKkLama, $noKkBaru) {
            Keluarga::where('no_kk', $noKkLama)->update([
                'no_kk' => $noKkBaru,
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);
        });

        // Ambil data keluarga terbaru yang sudah pindah KK
        $new = Keluarga::where('no_kk', $noKkBaru)->get();

        return [
            'status' => true,
            'message' => 'Seluruh anggota keluarga berhasil dipindahkan ke KK baru.',
            'data' => $new
        ];
    }
}