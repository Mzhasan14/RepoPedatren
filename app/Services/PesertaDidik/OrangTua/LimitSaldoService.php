<?php

namespace App\Services\PesertaDidik\OrangTua;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LimitSaldoService
{
    public function setLimitSaldo(int $santriId, ?float $limitSaldo, bool $takTerbatas): array
    {
        try {
            return DB::transaction(function () use ($santriId, $limitSaldo, $takTerbatas) {

                $user = Auth::user();
                $noKk = $user->no_kk;

                // 🔹 Ambil semua anak dari KK yang sama, exclude ortu
                $anak = DB::table('keluarga as k')
                    ->join('biodata as b', 'k.id_biodata', '=', 'b.id')
                    ->join('santri as s', 'b.id', '=', 's.biodata_id')
                    ->select('s.id as santri_id')
                    ->where('k.no_kk', $noKk)
                    ->get();

                if ($anak->isEmpty()) {
                    return [
                        'success' => false,
                        'message' => 'Tidak ada data anak yang ditemukan.',
                        'data' => null,
                        'status' => 404,
                    ];
                }

                // 🔹 Cek apakah santri_id request valid
                $dataAnak = $anak->firstWhere('santri_id', $santriId ?? null);

                if (!$dataAnak) {
                    return [
                        'success' => false,
                        'message' => 'Santri tidak valid untuk user ini.',
                        'data'    => null,
                        'status'  => 403,
                    ];
                }

                // Ambil kartu santri
                $kartu = DB::table('kartu')->where('santri_id', $santriId)->where('aktif', true)->first();
                if (!$kartu) {
                    throw new Exception('Kartu aktif santri tidak ditemukan.');
                }

                // Tentukan nilai limit
                $limitFinal = $takTerbatas ? null : $limitSaldo;

                // Update kartu
                DB::table('kartu')->where('santri_id', $santriId)->update([
                    'limit_saldo' => $limitFinal,
                    'updated_by' => $user->id,
                    'updated_at' => now(),
                ]);

                return [
                    'success' => true,
                    'message' => $takTerbatas
                        ? 'Limit saldo diatur menjadi tak terbatas.'
                        : 'Limit saldo berhasil diperbarui.',
                    'limit_saldo' => $limitFinal,
                ];
            });
        } catch (Exception $e) {
            Log::error('Gagal memperbarui limit saldo.', [
                'santri_id' => $santriId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui limit saldo: ' . $e->getMessage(),
            ];
        }
    }
}
