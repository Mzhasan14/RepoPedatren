<?php

namespace App\Services\PesertaDidik\Transaksi;

use App\Models\Saldo;
use App\Models\SaldoTransaksi;
use App\Models\OrangTuaWali;
use App\Models\Keluarga;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Http\UploadedFile;

class SaldoService
{
    public function requestTopUp(string $santriId, float $nominal, UploadedFile $buktiTransfer)
    {
        try {
            $user = Auth::user();
            $biodataId = $user->biodata_id;

            // cek apakah user terdaftar sebagai wali
            $wali = OrangTuaWali::where('id_biodata', $biodataId)
                ->where('wali', true)
                ->where('status', true)
                ->first();

            if (!$wali) {
                throw new Exception('Anda bukan wali santri.');
            }

            // ambil no_kk wali
            $kkOrangTua = Keluarga::where('id_biodata', $biodataId)
                ->where('status', true)
                ->first();

            if (!$kkOrangTua || empty($kkOrangTua->no_kk)) {
                throw new Exception('Data keluarga tidak ditemukan.');
            }

            // cek apakah santri termasuk dalam KK yang sama
            $santriDalamKK = Keluarga::where('no_kk', $kkOrangTua->no_kk)
                ->where('id_biodata', $santriId)
                ->where('status', true)
                ->first();

            if (!$santriDalamKK) {
                throw new Exception('Santri ini bukan anggota keluarga Anda.');
            }

            // simpan bukti transfer
            $filePath = $buktiTransfer->store('bukti_transfer');

            // buat transaksi top-up
            $transaksi = SaldoTransaksi::create([
                'santri_id' => $santriId,
                'orang_tua_wali_id' => $wali->id,
                'nominal' => $nominal,
                'metode_pembayaran' => 'transfer_bank',
                'bukti_transfer' => $filePath,
                'status' => 'pending'
            ]);

            return $transaksi;
        } catch (Exception $e) {
            Log::error('SaldoService@requestTopUp error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function approveTopUp(int $transaksiId)
    {
        DB::beginTransaction();
        try {
            $transaksi = SaldoTransaksi::findOrFail($transaksiId);

            if ($transaksi->status !== 'pending') {
                throw new Exception('Transaksi sudah diproses sebelumnya.');
            }

            $transaksi->status = 'approved';
            $transaksi->approved_by = Auth::id();
            $transaksi->approved_at = now();
            $transaksi->save();

            $saldo = Saldo::firstOrCreate(
                ['santri_id' => $transaksi->santri_id],
                ['saldo' => 0, 'created_by' => Auth::id()]
            );

            $saldo->saldo += $transaksi->nominal;
            $saldo->updated_by = Auth::id();
            $saldo->save();

            DB::commit();
            return $transaksi;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('SaldoService@approveTopUp error: ' . $e->getMessage());
            throw $e;
        }
    }
}
