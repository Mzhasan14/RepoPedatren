<?php

namespace App\Services\PesertaDidik\Transaksi;

use Exception;
use Carbon\Carbon;
use App\Models\Kartu;
use App\Models\Saldo;
use App\Models\Outlet;
use App\Models\Santri;
use App\Models\Kategori;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TransaksiService
{
    /**
     * Scan kartu -> mengembalikan data santri & saldo
     */
    public function scanCard(string $uid, ?string $pin): array
    {
        try {
            $kartu = Kartu::with('santri.biodata')->where('uid_kartu', $uid)->first();

            if (!$kartu) {
                return ['success' => false, 'message' => 'Kartu tidak ditemukan.', 'status' => 404];
            }

            if (!$kartu->aktif) {
                return ['success' => false, 'message' => 'Kartu tidak aktif.', 'status' => 403];
            }

            if ($kartu->tanggal_expired && Carbon::parse($kartu->tanggal_expired)->isPast()) {
                return ['success' => false, 'message' => 'Kartu kadaluarsa.', 'status' => 403];
            }

            if ($kartu->pin && !Hash::check($pin, $kartu->pin)) {
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
                        'nama' => $santri->biodata->nama ?? null,
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
    public function createTransaction(string $uid, int $outletId, int $kategoriId, float $totalBayar, ?string $pin): array
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

            if ($kartu->pin && !Hash::check($pin, $kartu->pin)) {
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
                ->where('user_id', Auth::id())
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
            $saldo->updated_by = Auth::id();
            $saldo->save();

            // simpan transaksi
            $transaksi = Transaksi::create([
                'santri_id' => $santri->id,
                'outlet_id' => $outletId,
                'kategori_id' => $kategoriId,
                'total_bayar' => $totalBayar,
                'tanggal' => Carbon::now(),
                'created_by' => Auth::id(),
                'status' => true,
            ]);

            DB::commit();

            // Kembalikan data transaksi sederhana
            return [
                'success' => true,
                'data' => [
                    'transaksi_id' => $transaksi->id,
                    'santri_id' => $santri->id,
                    'nama_santri' => $santri->biodata->nama ?? null,
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
                'user_id' => Auth::id() ?? null
            ]);
            return ['success' => false, 'message' => 'Terjadi kesalahan saat memproses transaksi. Silakan coba lagi.', 'status' => 500];
        }
    }

    /**
     * List transaksi sesuai role:
     * - Super Admin => semua transaksi
     * - Non-superadmin => hanya outlet yg dimiliki user
     */
    public function listTransactions(array $filters = [], int $perPage = 25)
    {
        try {
            $user = Auth::user();

            // Cek apakah user terdaftar di detail_user_outlet
            $outletIds = DB::table('detail_user_outlet')
                ->where('user_id', $user->id)
                ->where('status', true)
                ->pluck('outlet_id')
                ->toArray();

            if (empty($outletIds)) {
                return [
                    'success' => false,
                    'message' => 'User tidak terdaftar di outlet manapun.',
                    'status'  => 403,
                ];
            }

            // Query utama
            $query = Transaksi::with([
                'santri' => function ($q) {
                    $q->select('id', 'nis', 'biodata_id')
                        ->with([
                            'biodata:id,nama',
                            'kartu:id,santri_id,uid_kartu'
                        ]);
                },
                'outlet:id,nama_outlet',
                'kategori:id,nama_kategori'
            ])->whereIn('outlet_id', $outletIds)
                ->orderByDesc('transaksi.tanggal');

            // Filter tambahan
            if (!empty($filters['santri_id'])) $query->where('santri_id', (int)$filters['santri_id']);
            if (!empty($filters['outlet_id'])) $query->where('outlet_id', (int)$filters['outlet_id']);
            if (!empty($filters['kategori_id'])) $query->where('kategori_id', (int)$filters['kategori_id']);
            if (!empty($filters['date_from'])) $query->whereDate('tanggal', '>=', $filters['date_from']);
            if (!empty($filters['date_to'])) $query->whereDate('tanggal', '<=', $filters['date_to']);
            if (!empty($filters['q'])) {
                $q = $filters['q'];
                $query->whereHas(
                    'santri.biodata',
                    fn($qBuild) =>
                    $qBuild->where('nama', 'like', "%{$q}%")
                        ->orWhere('nis', 'like', "%{$q}%")
                );
            }

            // Pagination
            $results = $query->paginate($perPage);

            // Map collection dari paginator
            $data = $results->getCollection()->map(function ($item) {
                return [
                    'id'       => $item->id,
                    'outlet'   => $item->outlet,
                    'kategori' => $item->kategori,
                    'total_bayar' => (float)$item->total_bayar,
                    'tanggal'  => $item->tanggal,
                    'santri'   => $item->santri ? [
                        'id'      => $item->santri->id,
                        'nis'     => $item->santri->nis,
                        'biodata' => $item->santri->biodata,
                        'kartu' => $item->santri->kartu ? ['uid_kartu' => $item->santri->kartu->uid_kartu] : [],
                    ] : null,
                ];
            });

            // Kembalikan paginator dengan collection yang sudah dimodifikasi
            $results->setCollection($data);

            return [
                'success'      => true,
                'status'       => 200,
                'total_data'   => $results->total(),
                'current_page' => $results->currentPage(),
                'per_page'     => $results->perPage(),
                'total_pages'  => $results->lastPage(),
                'data'         => $results->items(),
            ];
        } catch (\Throwable $e) {
            Log::error('TransactionService@listTransactions error: ' . $e->getMessage(), [
                'exception' => $e,
                'filters'   => $filters,
                'user_id'   => Auth::id()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil transaksi.',
                'status'  => 500
            ];
        }
    }
}
