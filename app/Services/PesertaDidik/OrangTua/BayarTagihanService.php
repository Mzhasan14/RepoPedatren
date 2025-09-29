<?php

namespace App\Services\PesertaDidik\OrangTua;

use Carbon\Carbon;
use App\Models\Saldo;
use App\Models\Santri;
use App\Models\TagihanSantri;
use App\Models\TransaksiSaldo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BayarTagihanService
{

    public function bayar($request)
    {
        return DB::transaction(function () use ($request) {
            $user = Auth::user();

            // Ambil tagihan
            $tagihan = TagihanSantri::where('id', $request['tagihan_santri_id'])
                ->where('status', '!=', 'lunas')
                ->lockForUpdate()
                ->first();

            if (!$tagihan) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Tagihan tidak ditemukan atau sudah lunas.'
                ];
            }

            // Ambil saldo santri
            $saldo = Saldo::where('santri_id', $tagihan->santri_id)
                ->lockForUpdate()
                ->first();

            if (!$saldo) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Saldo santri tidak ditemukan.'
                ];
            }

            // Validasi user (selain superadmin harus valid)
            if (!$user->hasRole('superadmin')) {
                $santri = Santri::where('id', $tagihan->santri_id)->first();
                if (!$santri) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => 'User tidak valid untuk melakukan pembayaran.'
                    ];
                }
            }

            // Hitung sisa tagihan
            $sisaTagihan = $tagihan->sisa > 0 ? $tagihan->sisa : $tagihan->nominal;

            // Tentukan jumlah bayar
            if (!empty($request['jumlah_bayar'])) {
                $jumlahBayar = $request['jumlah_bayar'];

                if ($jumlahBayar <= 0) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => 'Jumlah bayar tidak valid.'
                    ];
                }

                if ($jumlahBayar > $sisaTagihan) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => 'Jumlah bayar melebihi sisa tagihan.'
                    ];
                }

                if ($saldo->saldo < $jumlahBayar) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => 'Saldo santri tidak mencukupi untuk membayar sebagian.'
                    ];
                }
            } else {
                $jumlahBayar = $sisaTagihan;

                if ($saldo->saldo < $jumlahBayar) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => 'Saldo santri tidak mencukupi untuk melunasi tagihan.'
                    ];
                }
            }

            // Update tagihan
            $tagihan->sisa = $sisaTagihan - $jumlahBayar;
            $tagihan->status = $tagihan->sisa == 0 ? 'lunas' : 'sebagian';
            $tagihan->tanggal_bayar = Carbon::now();
            $tagihan->save();

            // Simpan saldo lama (untuk log)
            $saldoLama = $saldo->saldo;

            // Update saldo
            $saldo->saldo -= $jumlahBayar;
            $saldo->save();

            // Insert ke transaksi saldo
            $transaksi = TransaksiSaldo::create([
                'santri_id'     => $tagihan->santri_id,
                'tagihan_id'    => $tagihan->id,
                'jenis'         => 'debet',
                'nominal'       => $jumlahBayar,
                'saldo_akhir'   => $saldo->saldo,
                'keterangan'    => 'Pembayaran tagihan #' . $tagihan->id,
                'created_by'    => $user->id,
            ]);

            // Activity log pakai Spatie
            activity('transaksi_saldo')
                ->causedBy($user)
                ->performedOn($saldo)
                ->withProperties([
                    'santri_id'     => $tagihan->santri_id,
                    'tipe'          => 'debet',
                    'jumlah'        => $jumlahBayar,
                    'saldo_sebelum' => $saldoLama,
                    'saldo_sesudah' => $saldo->saldo,
                    'transaksi_id'  => $transaksi->id,
                    'tagihan_id'    => $tagihan->id,
                    'ip'            => request()->ip(),
                    'user_agent'    => request()->userAgent(),
                ])
                ->event('success')
                ->log("Pembayaran tagihan #{$tagihan->id} sebesar Rp{$jumlahBayar} berhasil");

            return [
                'success' => true,
                'data' => [
                    'tagihan_id'     => $tagihan->id,
                    'santri_id'      => $tagihan->santri_id,
                    'dibayar'        => $jumlahBayar,
                    'sisa_tagihan'   => $tagihan->sisa,
                    'status_tagihan' => $tagihan->status,
                    'sisa_saldo'     => $saldo->saldo,
                ],
                'message' => $tagihan->status == 'lunas'
                    ? 'Tagihan berhasil dilunasi.'
                    : 'Pembayaran sebagian berhasil diproses.'
            ];
        });
    }

    /**
     * Proses pembayaran tagihan santri.
     *
     * $request harus minimal berisi:
     * - 'tagihan_santri_id' (required)
     * - 'jumlah_bayar' (optional) => jika tidak diberikan, otomatis gunakan min(saldo, sisaTagihan)
     *
     * @param array $request
     * @return array
     */
    // public function bayar(array $request): array
    // {
    //     return DB::transaction(function () use ($request) {
    //         $user = Auth::user();

    //         // Ambil tagihan (lock untuk update)
    //         $tagihan = TagihanSantri::where('id', $request['tagihan_santri_id'] ?? null)
    //             ->where('status', '!=', 'lunas')
    //             ->lockForUpdate()
    //             ->first();

    //         if (!$tagihan) {
    //             return [
    //                 'success' => false,
    //                 'message' => 'Tagihan tidak ditemukan atau sudah lunas.',
    //                 'data' => null,
    //             ];
    //         }

    //         // Ambil saldo santri (lock untuk update)
    //         $saldo = Saldo::where('santri_id', $tagihan->santri_id)
    //             ->lockForUpdate()
    //             ->first();

    //         if (!$saldo || !$saldo->status || (float) $saldo->saldo <= 0) {
    //             return [
    //                 'success' => false,
    //                 'message' => 'Saldo santri tidak mencukupi atau tidak aktif.',
    //                 'data' => null,
    //             ];
    //         }

    //         // Validasi user non-superadmin: pastikan dia berhak membayar tagihan ini (no_kk sama & santri sesuai)
    //         if ($user && !$user->hasRole('superadmin')) {
    //             $santri = Santri::join('biodata as b', 'santri.biodata_id', '=', 'b.id')
    //                 ->join('keluarga as k', 'b.id', '=', 'k.id_biodata')
    //                 ->where('k.no_kk', $user->no_kk)
    //                 ->where('santri.id', $tagihan->santri_id)
    //                 ->first();

    //             if (!$santri) {
    //                 return [
    //                     'success' => false,
    //                     'message' => 'User tidak valid untuk melakukan pembayaran tagihan ini.',
    //                     'data' => null,
    //                 ];
    //             }
    //         }

    //         // Tentukan sisa tagihan (jika kolom sisa > 0 gunakan itu, kalau tidak gunakan nominal)
    //         $sisaTagihan = (float) ($tagihan->sisa > 0 ? $tagihan->sisa : $tagihan->nominal);
    //         $availableSaldo = (float) $saldo->saldo;

    //         // Dukungan optional: jika user memberikan jumlah yang ingin dibayar
    //         $requestedAmount = array_key_exists('jumlah_bayar', $request) ? (float) $request['jumlah_bayar'] : null;

    //         if ($requestedAmount !== null) {
    //             if ($requestedAmount <= 0) {
    //                 return [
    //                     'success' => false,
    //                     'message' => 'Jumlah bayar harus lebih dari 0.',
    //                     'data' => null,
    //                 ];
    //             }
    //             if ($requestedAmount > $availableSaldo) {
    //                 return [
    //                     'success' => false,
    //                     'message' => 'Saldo tidak mencukupi untuk jumlah yang diminta.',
    //                     'data' => null,
    //                 ];
    //             }
    //             if ($requestedAmount > $sisaTagihan) {
    //                 return [
    //                     'success' => false,
    //                     'message' => 'Jumlah bayar melebihi sisa tagihan.',
    //                     'data' => null,
    //                 ];
    //             }
    //             $jumlahBayar = $requestedAmount;
    //         } else {
    //             // Default: bayar sebanyak mungkin dari saldo (sampai lunas atau habis)
    //             $jumlahBayar = min($availableSaldo, $sisaTagihan);
    //         }

    //         // Update tagihan
    //         $tagihan->sisa = $sisaTagihan - $jumlahBayar;
    //         $tagihan->status = ($tagihan->sisa == 0) ? 'lunas' : 'sebagian';
    //         $tagihan->tanggal_bayar = Carbon::now();
    //         $tagihan->updated_by = $user->id ?? null;

    //         // Tambahkan catatan singkat ke keterangan (jejak pembayaran tanpa tabel baru)
    //         $who = $user->name ?? 'system';
    //         $whoId = $user->id ?? 'NULL';
    //         $note = sprintf(
    //             "[%s] Pembayaran Rp %s oleh %s (user_id:%s). Sisa setelah bayar: Rp %s.",
    //             Carbon::now()->toDateTimeString(),
    //             number_format($jumlahBayar, 2, ',', '.'),
    //             $who,
    //             $whoId,
    //             number_format($tagihan->sisa, 2, ',', '.')
    //         );

    //         $existingKeterangan = trim((string) $tagihan->keterangan);
    //         $tagihan->keterangan = $existingKeterangan === '' ? $note : ($existingKeterangan . "\n" . $note);

    //         $tagihan->save();

    //         // Update saldo
    //         $saldo->saldo = $availableSaldo - $jumlahBayar;
    //         $saldo->updated_by = $user->id ?? null;
    //         $saldo->save();

    //         // Cek apakah pembayaran melewati tanggal jatuh tempo
    //         $terlambat = false;
    //         if ($tagihan->tanggal_jatuh_tempo && Carbon::now()->gt($tagihan->tanggal_jatuh_tempo)) {
    //             $terlambat = true;
    //         }

    //         return [
    //             'success' => true,
    //             'message' => 'Pembayaran berhasil diproses.',
    //             'data' => [
    //                 'tagihan_id' => $tagihan->id,
    //                 'santri_id' => $tagihan->santri_id,
    //                 'dibayar' => $jumlahBayar,
    //                 'sisa_tagihan' => $tagihan->sisa,
    //                 'status_tagihan' => $tagihan->status,
    //                 'sisa_saldo' => $saldo->saldo,
    //                 'terlambat' => $terlambat,
    //             ],
    //         ];
    //     });
    // }
    // public function bayar($request)
    // {
    //     return DB::transaction(function () use ($request) {
    //         $user = Auth::user();

    //         // Ambil tagihan
    //         $tagihan = TagihanSantri::where('id', $request['tagihan_santri_id'])
    //             ->where('status', '!=', 'lunas')
    //             ->lockForUpdate() // kunci biar aman dari race condition
    //             ->first();

    //         if (!$tagihan) {
    //             return [
    //                 'success' => false,
    //                 'data' => null,
    //                 'message' => 'Tagihan tidak ditemukan atau sudah lunas.'
    //             ];
    //         }

    //         // Ambil saldo santri
    //         $saldo = Saldo::where('santri_id', $tagihan->santri_id)
    //             ->lockForUpdate()
    //             ->first();

    //         if (!$saldo || $saldo->saldo <= 0) {
    //             return [
    //                 'success' => false,
    //                 'data' => null,
    //                 'message' => 'Saldo santri tidak mencukupi.'
    //             ];
    //         }

    //         if (!$user->hasRole('superadmin')) {
    //             $santri = Santri::join('biodata as b', 'santri.biodata_id', '=', 'b.id')
    //                 ->join('keluarga as k', 'b.id', '=', 'k.id_biodata')->where('k.no_kk', $user->no_kk)->first();
    //             if (!$santri) {
    //                 return [
    //                     'success' => false,
    //                     'data' => null,
    //                     'message' => 'User tidak valid untuk melakukan pembayaran.'
    //                 ];
    //             }
    //         }

    //         // Hitung nominal yang harus dibayar
    //         $sisaTagihan = $tagihan->sisa > 0 ? $tagihan->sisa : $tagihan->nominal;
    //         $jumlahBayar = min($saldo->saldo, $sisaTagihan);

    //         // Update tagihan
    //         $tagihan->sisa = $sisaTagihan - $jumlahBayar;
    //         $tagihan->status = $tagihan->sisa == 0 ? 'lunas' : 'sebagian';
    //         $tagihan->tanggal_bayar = Carbon::now();
    //         $tagihan->save();

    //         // Update saldo
    //         $saldo->saldo -= $jumlahBayar;
    //         $saldo->save();

    //         return [
    //             'success' => true,
    //             'data' => [
    //                 'tagihan_id' => $tagihan->id,
    //                 'santri_id' => $tagihan->santri_id,
    //                 'dibayar' => $jumlahBayar,
    //                 'sisa_tagihan' => $tagihan->sisa,
    //                 'status_tagihan' => $tagihan->status,
    //                 'sisa_saldo' => $saldo->saldo,
    //             ],
    //             'message' => 'Pembayaran berhasil diproses.'
    //         ];
    //     });
    // }
}
