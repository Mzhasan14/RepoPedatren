<?php

namespace App\Services\PesertaDidik\OrangTua;

use Exception;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TransaksiAnakService
{
    public function getTransaksiAnak(array $filters = [], int $perPage = 25)
    {
        try {
            $user = Auth::user();
            $bioId = $user->biodata_id;

            // 🔹 Ambil nomor KK orang tua
            $noKk = DB::table('keluarga as k')
                ->where('k.id_biodata', $bioId)
                ->value('no_kk');

            if (!$noKk) {
                return [
                    'success' => false,
                    'message' => 'Data keluarga tidak ditemukan.',
                    'data' => null,
                    'status' => 404,
                ];
            }

            // 🔹 Ambil semua anak dari KK yang sama, exclude ortu
            $anak = DB::table('keluarga as k')
                ->join('biodata as b', 'k.id_biodata', '=', 'b.id')
                ->join('santri as s', 'b.id', '=', 's.biodata_id')
                ->leftJoin('orang_tua_wali as otw', 'b.id', '=', 'otw.id_biodata')
                ->select('s.id as santri_id')
                ->whereNull('otw.id_biodata')
                ->where('k.no_kk', $noKk)
                ->where('k.id_biodata', '!=', $bioId)
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
            $dataAnak = $anak->firstWhere('santri_id', $filters['santri_id'] ?? null);

            if (!$dataAnak) {
                return [
                    'success' => false,
                    'message' => 'Santri tidak valid untuk user ini.',
                    'data'    => null,
                    'status'  => 403,
                ];
            }

            // 🔹 Query transaksi khusus santri_id ini
            $query = Transaksi::with([
                'santri:id,nis,biodata_id',
                'santri.biodata:id,nama',
                'santri.kartu:id,santri_id,uid_kartu',
                'outlet:id,nama_outlet',
                'kategori:id,nama_kategori',
                'userOutlet:id,user_id,outlet_id'
            ])
                ->where('santri_id', $dataAnak->santri_id) // ✅ fix di sini
                ->orderByDesc('tanggal');

            // 🔹 Filter tambahan
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
                        'kartu' => $item->santri->kartu ? [
                            'uid_kartu' => $item->santri->kartu->uid_kartu
                        ] : [],
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
            Log::error('TransactionService@getTransaksiAnak error: ' . $e->getMessage(), [
                'exception' => $e,
                'filters' => $filters,
                'user_id' => Auth::id()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil transaksi.',
                'status' => 500
            ];
        }
    }
}
