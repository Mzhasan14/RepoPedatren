<?php

namespace App\Services\PesertaDidik\Transaksi;

use Exception;
use Carbon\Carbon;
use App\Models\Kartu;
use App\Models\Saldo;
use App\Models\Outlet;
use App\Models\TagihanSantri;
use App\Models\TransaksiSaldo;
use App\Models\DetailUserOutlet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SaldoService
{
    public function topup(string $metode, int $santri_id, float $jumlah, string $pin, int $userId): array
    {
        return $this->process($metode, $santri_id, $jumlah, $pin, $userId, 'topup', 1);
    }

    public function tarik(string $metode, int $santri_id, float $jumlah, string $pin, int $userId): array
    {
        return $this->process($metode, $santri_id, $jumlah, $pin, $userId, 'debit', 2);
    }

    private function process(string $metode, int $santri_id, float $jumlah, string $pin, int $userId, string $tipe, int $kategoriId): array
    {
        DB::beginTransaction();
        try {
            // 1. Validasi outlet koperasi pesantren
            $outlet = Outlet::where('nama_outlet', 'koperasi pesantren')
                ->where('status', true)
                ->first();

            if (!$outlet) {
                return [
                    'status'  => false,
                    'message' => 'Outlet koperasi pesantren tidak tersedia saat ini.'
                ];
            }

            $user = Auth::user();

            if (! $user->hasRole('superadmin')) {
                $userOutlet = DetailUserOutlet::where('user_id', $userId)
                    ->where('outlet_id', $outlet->id)
                    ->where('status', true)
                    ->first();

                if (!$userOutlet) {
                    return [
                        'status'  => false,
                        'message' => 'Anda tidak memiliki akses untuk melakukan transaksi di outlet koperasi pesantren.'
                    ];
                }
            }

            // $uidKartu = null; // default

            // if ($metode === 'scan') {
            // Cari kartu berdasarkan santri_id
            $kartu = Kartu::where('santri_id', $santri_id)
                ->select('uid_kartu', 'pin', 'aktif')
                ->first();

            if (!$kartu) {
                return [
                    'status'  => false,
                    'message' => 'Kartu tidak terdaftar.'
                ];
            }

            if (!$kartu->aktif) {
                return [
                    'status'  => false,
                    'message' => 'Kartu tidak aktif.'
                ];
            }

            // Validasi PIN
            if (!Hash::check($pin, $kartu->pin)) {
                return [
                    'status'  => false,
                    'message' => 'Pin yang Anda masukkan salah.'
                ];
            }

            $uidKartu = $kartu->uid_kartu; // simpan uid_kartu
            // }

            // 3. Ambil atau buat saldo santri
            $saldo = Saldo::firstOrCreate(
                ['santri_id' => $santri_id],
                ['saldo' => 0, 'created_by' => $userId]
            );

            // 4. Validasi saldo untuk penarikan
            if ($tipe === 'debit' && $saldo->saldo < $jumlah) {
                return [
                    'status'  => false,
                    'message' => 'Saldo santri tidak mencukupi untuk melakukan penarikan.'
                ];
            }

            $saldoLama = $saldo->saldo;

            // 5. Update saldo
            if ($tipe === 'topup') {
                $saldo->saldo += $jumlah;
            } elseif ($tipe === 'debit') {
                $saldo->saldo -= $jumlah;
            }
            $saldoBaru = $saldo->saldo;
            $saldo->updated_by = $userId;
            $saldo->save();

            // Insert transaksi utama
            $transaksi = TransaksiSaldo::create([
                'santri_id'      => $santri_id,
                'uid_kartu'      => $uidKartu,
                'outlet_id'      => $outlet->id,
                'kategori_id'    => $kategoriId,
                'user_outlet_id' => $user->hasRole('superadmin') ? null : $userOutlet->id,
                'tipe'           => $tipe,
                'jumlah'         => $jumlah,
                'keterangan'     => $tipe === 'topup'
                    ? "Topup saldo sebesar Rp{$jumlah}"
                    : "Tarik saldo sebesar Rp{$jumlah}",
            ]);

            // === 6. Jika topup, langsung potong tagihan santri yang sudah jatuh tempo === 
            $tagihanDipotong = false;

            if ($tipe === 'topup' && $saldo->saldo > 0) {
                $today = Carbon::today();

                $tagihans = TagihanSantri::where('santri_id', $santri_id)
                    ->where('status', 'pending')
                    ->whereDate('tanggal_jatuh_tempo', '<=', $today) // hanya yang sudah jatuh tempo
                    ->orderBy('tanggal_jatuh_tempo', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach ($tagihans as $tagihan) {
                    if ($saldo->saldo <= 0) break;

                    if ($saldo->saldo >= $tagihan->total_tagihan) {
                        $saldo->saldo -= $tagihan->total_tagihan;
                        $saldo->save();

                        $tagihan->status        = 'lunas';
                        $tagihan->tanggal_bayar = Carbon::now();
                        $tagihan->save();

                        // Catat pembayaran otomatis
                        TransaksiSaldo::create([
                            'santri_id'      => $santri_id,
                            'uid_kartu'      => $uidKartu,
                            'outlet_id'      => $outlet->id,
                            'kategori_id'    => $kategoriId,
                            'user_outlet_id' => $user->hasRole('superadmin') ? null : $userOutlet->id,
                            'tipe'           => 'debit',
                            'jumlah'         => $tagihan->total_tagihan,
                            'keterangan'     => "Pembayaran otomatis tagihan #{$tagihan->id} sebesar Rp{$tagihan->total_tagihan} dari saldo topup",
                        ]);

                        $tagihanDipotong = true;
                    }
                }
            }

            // Log aktivitas
            activity('transaksi_saldo')
                ->causedBy(Auth::user())
                ->performedOn($saldo)
                ->withProperties([
                    'santri_id'     => $santri_id,
                    'tipe'          => $tipe,
                    'jumlah'        => $jumlah,
                    'saldo_sebelum' => $saldoLama,
                    'saldo_sesudah' => $saldo->saldo,
                    'transaksi_id'  => $transaksi->id,
                    'kategori_id'   => $kategoriId,
                    'outlet_id'     => $outlet->id,
                    'ip'            => request()->ip(),
                    'user_agent'    => request()->userAgent(),
                ])
                ->event('success')
                ->log("Transaksi saldo {$tipe} sebesar Rp{$jumlah} berhasil");

            DB::commit();

            // Pesan response
            if ($tipe === 'topup') {
                $message = $tagihanDipotong
                    ? 'Saldo berhasil ditambahkan dan otomatis dipakai untuk melunasi tagihan.'
                    : 'Saldo berhasil ditambahkan.';
            } else {
                $message = 'Saldo berhasil ditarik.';
            }

            return [
                'status'    => true,
                'message'   => $message,
                'saldo'     => $saldo->saldo,
                'transaksi' => $transaksi
            ];
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('Model not found: ' . $e->getMessage());

            return [
                'status'  => false,
                'message' => 'Data yang diminta tidak ditemukan.'
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Transaksi saldo gagal: ' . $e->getMessage(), [
                'santri_id' => $santri_id,
                'user_id'   => $userId,
                'trace'     => $e->getTraceAsString()
            ]);

            return [
                'status'  => false,
                'message' => 'Terjadi kesalahan pada sistem. Silakan coba lagi atau hubungi petugas.'
            ];
        }
    }



    // public function requestTopUp(string $santriId, float $nominal, UploadedFile $buktiTransfer)
    // {
    //     try {
    //         $user = Auth::user();
    //         $biodataId = $user->biodata_id;

    //         // cek apakah user terdaftar sebagai wali
    //         $wali = OrangTuaWali::where('id_biodata', $biodataId)
    //             ->where('wali', true)
    //             ->where('status', true)
    //             ->first();

    //         if (!$wali) {
    //             throw new Exception('Anda bukan wali santri.');
    //         }

    //         // ambil no_kk wali
    //         $kkOrangTua = Keluarga::where('id_biodata', $biodataId)
    //             ->where('status', true)
    //             ->first();

    //         if (!$kkOrangTua || empty($kkOrangTua->no_kk)) {
    //             throw new Exception('Data keluarga tidak ditemukan.');
    //         }

    //         // cek apakah santri termasuk dalam KK yang sama
    //         $santriDalamKK = Keluarga::where('no_kk', $kkOrangTua->no_kk)
    //             ->where('id_biodata', $santriId)
    //             ->where('status', true)
    //             ->first();

    //         if (!$santriDalamKK) {
    //             throw new Exception('Santri ini bukan anggota keluarga Anda.');
    //         }

    //         // simpan bukti transfer
    //         $filePath = $buktiTransfer->store('bukti_transfer');

    //         // buat transaksi top-up
    //         $transaksi = SaldoTransaksi::create([
    //             'santri_id' => $santriId,
    //             'orang_tua_wali_id' => $wali->id,
    //             'nominal' => $nominal,
    //             'metode_pembayaran' => 'transfer_bank',
    //             'bukti_transfer' => $filePath,
    //             'status' => 'pending'
    //         ]);

    //         return $transaksi;
    //     } catch (Exception $e) {
    //         Log::error('SaldoService@requestTopUp error: ' . $e->getMessage());
    //         throw $e;
    //     }
    // }

    // public function approveTopUp(int $transaksiId)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $transaksi = SaldoTransaksi::findOrFail($transaksiId);

    //         if ($transaksi->status !== 'pending') {
    //             throw new Exception('Transaksi sudah diproses sebelumnya.');
    //         }

    //         $transaksi->status = 'approved';
    //         $transaksi->approved_by = Auth::id();
    //         $transaksi->approved_at = now();
    //         $transaksi->save();

    //         $saldo = Saldo::firstOrCreate(
    //             ['santri_id' => $transaksi->santri_id],
    //             ['saldo' => 0, 'created_by' => Auth::id()]
    //         );

    //         $saldo->saldo += $transaksi->nominal;
    //         $saldo->updated_by = Auth::id();
    //         $saldo->save();

    //         DB::commit();
    //         return $transaksi;
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         Log::error('SaldoService@approveTopUp error: ' . $e->getMessage());
    //         throw $e;
    //     }
    // }
}
