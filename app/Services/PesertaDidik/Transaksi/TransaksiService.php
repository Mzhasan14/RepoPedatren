<?php

namespace App\Services\PesertaDidik\Transaksi;

use App\Models\Kartu;
use App\Models\Santri;
use App\Models\Saldo;
use App\Models\Outlet;
use App\Models\Kategori;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class TransaksiService
{
    /**
     * Scan kartu -> mengembalikan data santri & saldo
     */
    public function scanCard(string $uid, ?string $pin, $user): array
    {
        try {
            $kartu = Kartu::with('santri')->where('uid_kartu', $uid)->first();

            if (!$kartu) {
                return ['success' => false, 'message' => 'Kartu tidak ditemukan.', 'status' => 404];
            }

            if (!$kartu->aktif) {
                return ['success' => false, 'message' => 'Kartu tidak aktif.', 'status' => 403];
            }

            if ($kartu->tanggal_expired && Carbon::parse($kartu->tanggal_expired)->isPast()) {
                return ['success' => false, 'message' => 'Kartu kadaluarsa.', 'status' => 403];
            }

            if ($kartu->pin && $pin !== $kartu->pin) {
                return ['success' => false, 'message' => 'PIN salah.', 'status' => 401];
            }

            $santri = $kartu->santri;
            if (!$santri) {
                return ['success' => false, 'message' => 'Data santri tidak ditemukan.', 'status' => 404];
            }

            $saldo = Saldo::where('santri_id', $santri->id)->first();

            return [
                'success' => true,
                'data' => [
                    'kartu' => [
                        'id' => $kartu->id,
                        'uid_kartu' => $kartu->uid_kartu,
                        'aktif' => (bool)$kartu->aktif,
                        'tanggal_terbit' => $kartu->tanggal_terbit,
                        'tanggal_expired' => $kartu->tanggal_expired,
                    ],
                    'santri' => [
                        'id' => $santri->id,
                        'nama' => $santri->nama ?? null,
                        'nis' => $santri->nis ?? null,
                    ],
                    'saldo' => $saldo ? (float)$saldo->saldo : 0.00
                ]
            ];
        } catch (Exception $e) {
            Log::error('ScanCard error: ' . $e->getMessage(), ['exception' => $e]);
            return ['success' => false, 'message' => 'Terjadi kesalahan saat scan kartu. Silakan coba lagi.', 'status' => 500];
        }
    }

    /**
     * Buat transaksi: deduct saldo & simpan transaksi. Gunakan DB transaction & lockForUpdate pada saldo.
     */
    public function createTransaction(string $uid, int $outletId, int $kategoriId, float $totalBayar, ?string $pin, $user): array
    {
        DB::beginTransaction();
        try {
            // cari kartu + santri
            $kartu = Kartu::with('santri')->where('uid_kartu', $uid)->first();
            if (!$kartu) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Kartu tidak ditemukan.', 'status' => 404];
            }

            if (!$kartu->aktif) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Kartu tidak aktif.', 'status' => 403];
            }

            if ($kartu->tanggal_expired && Carbon::parse($kartu->tanggal_expired)->isPast()) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Kartu kadaluarsa.', 'status' => 403];
            }

            if ($kartu->pin && $pin !== $kartu->pin) {
                DB::rollBack();
                return ['success' => false, 'message' => 'PIN salah.', 'status' => 401];
            }

            $santri = $kartu->santri;
            if (!$santri) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Data santri tidak ditemukan.', 'status' => 404];
            }

            // Validasi outlet: apakah user boleh transaksi di outlet ini?
            $outlet = Outlet::find($outletId);
            if (!$outlet || !$outlet->status) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Outlet tidak ditemukan atau tidak aktif.', 'status' => 404];
            }

            // cek apakah user (penjual) mengelola outlet ini (detail_user_outlet)
            $allowed = DB::table('detail_user_outlet')
                ->where('user_id', $user->id)
                ->where('outlet_id', $outletId)
                ->where('status', true)
                ->exists();

            if (!$allowed) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Anda tidak memiliki akses ke outlet ini.', 'status' => 403];
            }

            // Validasi kategori aktif dan outlet-kategori relation
            $kategori = Kategori::find($kategoriId);
            if (!$kategori || !$kategori->status) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Kategori tidak ditemukan atau tidak aktif.', 'status' => 404];
            }

            $outletHasKategori = DB::table('outlet_kategori')
                ->where('outlet_id', $outletId)
                ->where('kategori_id', $kategoriId)
                ->where('status', true)
                ->exists();

            if (!$outletHasKategori) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Kategori tidak tersedia di outlet ini.', 'status' => 422];
            }

            // Ambil saldo dengan lockForUpdate
            $saldo = Saldo::where('santri_id', $santri->id)->lockForUpdate()->first();

            if (!$saldo) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Saldo santri tidak ditemukan.', 'status' => 404];
            }

            // cek cukup
            if (bccomp($saldo->saldo, $totalBayar, 2) < 0) { // saldo < totalBayar
                DB::rollBack();
                return ['success' => false, 'message' => 'Saldo tidak cukup.', 'status' => 402];
            }

            // kurangi saldo
            $saldo->saldo = bcsub($saldo->saldo, $totalBayar, 2);
            $saldo->updated_by = $user->id;
            $saldo->save();

            // simpan transaksi
            $transaksi = Transaksi::create([
                'santri_id' => $santri->id,
                'outlet_id' => $outletId,
                'kategori_id' => $kategoriId,
                'total_bayar' => $totalBayar,
                'tanggal' => Carbon::now(),
                'created_by' => $user->id,
                'status' => true,
            ]);

            DB::commit();

            // Kembalikan data transaksi sederhana
            return [
                'success' => true,
                'data' => [
                    'transaksi_id' => $transaksi->id,
                    'santri_id' => $santri->id,
                    'nama_santri' => $santri->nama ?? null,
                    'total_bayar' => (float)$totalBayar,
                    'sisa_saldo' => (float)$saldo->saldo,
                    'tanggal' => $transaksi->tanggal,
                ]
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('CreateTransaction error: ' . $e->getMessage(), [
                'exception' => $e,
                'uid' => $uid,
                'outlet_id' => $outletId,
                'kategori_id' => $kategoriId,
                'user_id' => $user->id ?? null
            ]);
            return ['success' => false, 'message' => 'Terjadi kesalahan saat memproses transaksi. Silakan coba lagi.', 'status' => 500];
        }
    }
    
    /**
     * List semua transaksi (global) -> batasi 25 per halaman default
     * Support filter: santri_id, outlet_id, kategori_id, date_from (Y-m-d), date_to (Y-m-d), q (nama/nis)
     */
    public function listTransactions(array $filters = [], int $perPage = 25)
    {
        try {
            $query = Transaksi::with(['santri', 'outlet', 'kategori'])
                ->orderByDesc('tanggal');

            // filter by santri
            if (!empty($filters['santri_id'])) {
                $query->where('santri_id', (int)$filters['santri_id']);
            }

            // filter by outlet
            if (!empty($filters['outlet_id'])) {
                $query->where('outlet_id', (int)$filters['outlet_id']);
            }

            // filter by kategori
            if (!empty($filters['kategori_id'])) {
                $query->where('kategori_id', (int)$filters['kategori_id']);
            }

            // date range
            if (!empty($filters['date_from'])) {
                $query->whereDate('tanggal', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->whereDate('tanggal', '<=', $filters['date_to']);
            }

            // free text search on santri.nama or santri.nis
            if (!empty($filters['q'])) {
                $q = $filters['q'];
                $query->whereHas('santri', function ($qBuild) use ($q) {
                    $qBuild->where('nama', 'like', "%{$q}%")
                        ->orWhere('nis', 'like', "%{$q}%");
                });
            }

            return $query->paginate($perPage);
        } catch (\Throwable $e) {
            // log dan lempar exception agar controller bisa tangani
            Log::error('TransactionService@listTransactions error: ' . $e->getMessage(), [
                'exception' => $e,
                'filters' => $filters
            ]);
            throw $e;
        }
    }
}
