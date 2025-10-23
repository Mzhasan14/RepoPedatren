<?php

namespace App\Services\Keluarga;

use App\Models\Biodata;
use App\Models\Keluarga;
use Illuminate\Support\Facades\Auth;
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
            'data' => [
                'id' => $kel->id,
                'biodata_id' => $kel->biodata->id,
                'no_kk' => $kel->no_kk,
                'nama' => $kel->biodata->nama,
                'hubungan' => optional(optional($kel->orangTua)->hubunganKeluarga)->nama_status,
                'status_wali' => optional($kel->orangTua)->wali ?? null,
                'status' => $kel->status,
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
                'no_kk' => $input['no_kk'],
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
        if (! isset($input['no_kk'])) {
            return [
                'status' => false,
                'message' => 'Kolom no_kk harus diisi.',
            ];
        }

        $keluarga = Keluarga::find($id);

        if (! $keluarga) {
            return [
                'status' => false,
                'message' => 'Data keluarga tidak ditemukan.',
                'data' => null,
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
            'data' => $new,
        ];
    }

    public function changeFamilyCard($biodataId, array $validated)
    {
        $noKkBaru = trim($validated['no_kk_baru'] ?? '');

        if (!$biodataId || !$noKkBaru) {
            return [
                'success' => false,
                'message' => 'No KK baru wajib diisi.',
                'status'  => 400,
            ];
        }

        $noKkLama = DB::table('keluarga')
            ->where('id_biodata', $biodataId)
            ->value('no_kk');

        if (!$noKkLama) {
            return [
                'success' => false,
                'message' => 'Nomor KK lama tidak ditemukan untuk biodata tersebut.',
                'status'  => 404,
            ];
        }

        if ($noKkLama === $noKkBaru) {
            return [
                'success' => false,
                'message' => 'Nomor KK baru tidak boleh sama dengan nomor KK lama.',
                'status'  => 400,
            ];
        }

        $kkExist = DB::table('keluarga')
            ->where('no_kk', $noKkBaru)
            ->exists();

        if ($kkExist) {
            return [
                'success' => false,
                'message' => 'Nomor KK baru sudah digunakan oleh keluarga lain.',
                'status'  => 409,
            ];
        }

        $anggota = DB::table('keluarga')
            ->where('no_kk', $noKkLama)
            ->pluck('id_biodata');

        if ($anggota->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada anggota keluarga dengan No KK lama tersebut.',
                'status'  => 404,
            ];
        }

        DB::transaction(function () use ($anggota, $noKkBaru, $noKkLama, $biodataId) {
            DB::table('keluarga')
                ->whereIn('id_biodata', $anggota)
                ->update([
                    'no_kk' => $noKkBaru,
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            activity('perubahan_kartu_keluarga')
                ->causedBy(Auth::user())
                ->performedOn(Biodata::find($biodataId))
                ->withProperties([
                    'biodata_id' => $biodataId,
                    'no_kk_lama' => $noKkLama,
                    'no_kk_baru' => $noKkBaru,
                    'jumlah_anggota_terupdate' => $anggota->count(),
                    'daftar_anggota' => $anggota,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->event('update_no_kk')
                ->log(sprintf(
                    'Perubahan Nomor KK dari %s ke %s untuk %d anggota keluarga.',
                    $noKkLama,
                    $noKkBaru,
                    $anggota->count()
                ));
        });

        return [
            'success' => true,
            'message' => 'Nomor KK berhasil diperbarui untuk seluruh anggota keluarga.',
            'data' => [
                'no_kk_lama' => $noKkLama,
                'no_kk_baru' => $noKkBaru,
                'jumlah_anggota_terupdate' => $anggota->count(),
            ],
            'status' => 200,
        ];
    }
}
