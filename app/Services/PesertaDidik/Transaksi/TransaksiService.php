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
use App\Models\TransaksiSaldo;
use App\Models\DetailUserOutlet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TransaksiService
{
    /**
     * Scan kartu -> mengembalikan data santri & saldo
     */
    public function scanCard(string $santriId, ?string $pin): array
    {
        try {
            $kartu = Kartu::with('santri.biodata')
                ->where('kartu.santri_id', $santriId)
                ->first();

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

            $santri = Santri::join('biodata as b', 'b.id', 'santri.biodata_id')->where('santri.id', $santriId)->select('santri.id', 'santri.nis', 'b.nama as nama')->first();

            if (!$santri) {
                return ['success' => false, 'message' => 'Data santri tidak ditemukan.', 'status' => 404];
            }

            $saldo = Saldo::where('santri_id', $santriId)->first();

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
     * Buat transaksi: deduct saldo & simpan transaksi
     */
    public function createTransaction(int $outletId, string $uid, int $kategoriId, float $totalBayar, ?string $pin): array
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            if (! $user->hasRole('superadmin')) {
                // Ambil outlet aktif dari user yang login
                $userOutlet = DetailUserOutlet::where('user_id', $user->id)
                    ->where('status', true)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$userOutlet) {
                    return $this->fail('Anda tidak terhubung ke outlet manapun atau outlet tidak aktif.', 403);
                }

                $outletId = $userOutlet->outlet_id;
            } else {
                
                $outletId = $outletId;
            }

            // Validasi kartu
            $kartu = Kartu::with('santri.biodata')->where('uid_kartu', $uid)->first();
            if (!$kartu) return $this->fail('Kartu tidak ditemukan.', 404);
            if (!$kartu->aktif) return $this->fail('Kartu tidak aktif.', 403);
            if ($kartu->tanggal_expired && Carbon::parse($kartu->tanggal_expired)->isPast())
                return $this->fail('Kartu kadaluarsa.', 403);
            if ($kartu->pin && !Hash::check($pin, $kartu->pin))
                return $this->fail('PIN salah.', 401);

            $santri = $kartu->santri;
            if (!$santri) return $this->fail('Data santri tidak ditemukan.', 404);

            // Validasi outlet
            $outlet = Outlet::withoutTrashed()->find($outletId);
            if (!$outlet || !$outlet->status) return $this->fail('Outlet tidak ditemukan atau tidak aktif.', 404);

            // Validasi kategori
            $kategori = Kategori::withoutTrashed()->find($kategoriId);
            if (!$kategori || !$kategori->status) return $this->fail('Kategori tidak ditemukan atau tidak aktif.', 404);

            $outletHasKategori = DB::table('outlet_kategori')
                ->where('outlet_id', $outletId)
                ->where('kategori_id', $kategoriId)
                ->where('status', true)
                ->exists();

            if (!$outletHasKategori) return $this->fail('Kategori tidak tersedia di outlet ini.', 422);

            // Lock saldo
            $saldo = Saldo::where('santri_id', $santri->id)->lockForUpdate()->first();
            if (!$saldo) return $this->fail('Saldo santri tidak ditemukan.', 404);
            if (bccomp($saldo->saldo, $totalBayar, 2) < 0) return $this->fail('Saldo tidak cukup.', 402);

            // Update saldo
            $saldo->saldo = bcsub($saldo->saldo, $totalBayar, 2);
            $saldo->updated_by = $user->id;
            $saldo->save();

            // Buat transaksi utama
            $transaksi = Transaksi::create([
                'santri_id' => $santri->id,
                'outlet_id' => $outletId,
                'kategori_id' => $kategoriId,
                'user_outlet_id' => $user->hasRole('superadmin') ? null : $userOutlet->id,
                'total_bayar' => $totalBayar,
                'tanggal' => Carbon::now(),
                'created_by' => $user->id,
            ]);

            // Rekap ke transaksi_saldo
            TransaksiSaldo::create([
                'santri_id' => $santri->id,
                'outlet_id' => $outletId,
                'kategori_id' => $kategoriId,
                'user_outlet_id' => $user->hasRole('superadmin') ? null : $userOutlet->id,
                'tipe' => 'debit',
                'jumlah' => $totalBayar,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            activity('transaksi')
                ->causedBy(Auth::user())
                ->performedOn($transaksi)
                ->withProperties([
                    'uid_kartu' => $uid,
                    'santri_id' => $santri->id,
                    'outlet_id' => $outletId,
                    'kategori_id' => $kategoriId,
                    'total_bayar' => $totalBayar,
                    'sisa_saldo' => (float)$saldo->saldo,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->event('success')
                ->log('Transaksi berhasil dibuat');

            DB::commit();

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
                'kategori_id' => $kategoriId,
                'user_id' => Auth::id() ?? null
            ]);
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses transaksi. Silakan coba lagi.',
                'status' => 500
            ];
        }
    }


    /**
     * List transaksi sesuai role user
     */
    public function listTransactions(array $filters = [], int $perPage = 25)
    {
        try {
            $query = Transaksi::with([
                'santri:id,nis,biodata_id',
                'santri.biodata:id,nama',
                'santri.kartu:id,santri_id,uid_kartu',
                'outlet:id,nama_outlet',
                'kategori:id,nama_kategori',
                'userOutlet:id,user_id,outlet_id'
            ])->orderByDesc('tanggal');

            if (!empty($filters['santri_id'])) {
                $query->where('santri_id', $filters['santri_id']);
            }
            if (!empty($filters['outlet_id'])) {
                $query->where('outlet_id', $filters['outlet_id']);
            }
            if (!empty($filters['kategori_id'])) {
                $query->where('kategori_id', $filters['kategori_id']);
            }
            if (!empty($filters['date_from'])) {
                $query->whereDate('tanggal', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->whereDate('tanggal', '<=', $filters['date_to']);
            }
            if (!empty($filters['q'])) {
                $q = $filters['q'];

                $query->where(function ($sub) use ($q) {
                    $sub->whereHas('santri.biodata', function ($qb) use ($q) {
                        $qb->whereRaw("MATCH(nama) AGAINST(? IN BOOLEAN MODE)", [$q]);
                    });
                    $sub->orWhereHas('santri', fn($qb) => $qb->where('nis', $q));
                });
            }

            // 🔹 Hitung total pembayaran sebelum paginate
            $totalPembayaran = (clone $query)->sum('total_bayar');

            $results = $query->paginate($perPage);

            $data = $results->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'outlet' => $item->outlet,
                    'kategori' => $item->kategori,
                    'total_bayar' => (float)$item->total_bayar,
                    'tanggal' => $item->tanggal,
                    'santri' => $item->santri ? [
                        'id' => $item->santri->id,
                        'nis' => $item->santri->nis,
                        'biodata' => $item->santri->biodata,
                        'kartu' => $item->santri->kartu ? ['uid_kartu' => $item->santri->kartu->uid_kartu] : [],
                    ] : null,
                ];
            });

            $results->setCollection($data);

            return [
                'success' => true,
                'status' => 200,
                'total_data' => $results->total(),
                'current_page' => $results->currentPage(),
                'per_page' => $results->perPage(),
                'total_pages' => $results->lastPage(),
                'total_pembayaran' => (float)$totalPembayaran,
                'data' => $results->items(),
            ];
        } catch (Exception $e) {
            Log::error('TransactionService@listTransactions error: ' . $e->getMessage(), [
                'exception' => $e,
                'filters' => $filters,
                'user_id' => Auth::id()
            ]);

            return ['success' => false, 'message' => 'Terjadi kesalahan saat mengambil transaksi.', 'status' => 500];
        }
    }

    public function transaksiToko(array $filters = [], int $perPage = 25)
    {
        try {
            $query = Transaksi::with([
                'santri:id,nis,biodata_id',
                'santri.biodata:id,nama',
                'santri.kartu:id,santri_id,uid_kartu',
                'outlet:id,nama_outlet',
                'kategori:id,nama_kategori',
                'userOutlet:id,user_id,outlet_id'
            ])
                ->where('outlet_id', $filters['outlet_id'])
                ->orderByDesc('tanggal');

            if (!empty($filters['santri_id'])) {
                $query->where('santri_id', $filters['santri_id']);
            }
            if (!empty($filters['kategori_id'])) {
                $query->where('kategori_id', $filters['kategori_id']);
            }
            if (!empty($filters['date_from'])) {
                $query->whereDate('tanggal', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->whereDate('tanggal', '<=', $filters['date_to']);
            }
            if (!empty($filters['q'])) {
                $q = $filters['q'];

                $query->where(function ($sub) use ($q) {
                    $sub->whereHas('santri.biodata', function ($qb) use ($q) {
                        $qb->whereRaw("MATCH(nama) AGAINST(? IN BOOLEAN MODE)", [$q]);
                    });
                    $sub->orWhereHas('santri', fn($qb) => $qb->where('nis', $q));
                });
            }

            // 🔹 Hitung total pembayaran sebelum paginate
            $totalPembayaran = (clone $query)->sum('total_bayar');

            $results = $query->paginate($perPage);

            $data = $results->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'outlet' => $item->outlet,
                    'kategori' => $item->kategori,
                    'total_bayar' => (float)$item->total_bayar,
                    'tanggal' => $item->tanggal,
                    'santri' => $item->santri ? [
                        'id' => $item->santri->id,
                        'nis' => $item->santri->nis,
                        'biodata' => $item->santri->biodata,
                        'kartu' => $item->santri->kartu ? ['uid_kartu' => $item->santri->kartu->uid_kartu] : [],
                    ] : null,
                ];
            });

            $results->setCollection($data);

            return [
                'success' => true,
                'status' => 200,
                'total_data' => $results->total(),
                'current_page' => $results->currentPage(),
                'per_page' => $results->perPage(),
                'total_pages' => $results->lastPage(),
                'total_pembayaran' => (float)$totalPembayaran,
                'data' => $results->items(),
            ];
        } catch (Exception $e) {
            Log::error('TransactionService@listTransactions error: ' . $e->getMessage(), [
                'exception' => $e,
                'filters' => $filters,
                'user_id' => Auth::id()
            ]);

            return ['success' => false, 'message' => 'Terjadi kesalahan saat mengambil transaksi.', 'status' => 500];
        }
    }

    private function fail(string $msg, int $status): array
    {
        DB::rollBack();
        return ['success' => false, 'message' => $msg, 'status' => $status];
    }
}
