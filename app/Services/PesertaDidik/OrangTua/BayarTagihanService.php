<?php

namespace App\Services\PesertaDidik\OrangTua;

use Carbon\Carbon;
use App\Models\Kartu;
use App\Models\Saldo;
use App\Models\Santri;
use App\Models\TagihanSantri;
use App\Models\TransaksiSaldo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class BayarTagihanService
{

    public function bayar($request)
    {
        return DB::transaction(function () use ($request) {
            $user = Auth::user();

            if (!isset($request['password']) || !Hash::check($request['password'], $user->password)) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Password tidak valid. Silakan coba lagi.'
                ];
            }

            // Ambil tagihan santri beserta relasi
            $tagihanSantri = TagihanSantri::with('tagihan')
                ->where('id', $request['tagihan_santri_id'])
                ->where('status', '!=', 'lunas')
                ->lockForUpdate()
                ->first();

            if (!$tagihanSantri) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Tagihan tidak ditemukan atau sudah lunas.'
                ];
            }

            // Ambil / buat saldo santri
            $saldo = Saldo::where('santri_id', $tagihanSantri->santri_id)
                ->lockForUpdate()
                ->first();

            if (!$saldo) {
                $saldo = Saldo::create([
                    'santri_id'  => $tagihanSantri->santri_id,
                    'saldo'      => 0,
                    'status'     => true,
                    'created_by' => $user->id,
                ]);
            }

            if (!$saldo) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Saldo santri tidak ditemukan.'
                ];
            }

            // Validasi user (non-superadmin harus valid)
            if (!$user->hasRole('superadmin')) {
                $santri = Santri::find($tagihanSantri->santri_id);
                if (!$santri) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => 'User tidak valid untuk melakukan pembayaran.'
                    ];
                }
            }

            // Jumlah bayar = total_tagihan - total_potongan (kalau mau pastikan bersih)
            $jumlahBayar = $tagihanSantri->total_tagihan - $tagihanSantri->total_potongan;

            // Cek saldo cukup
            if ($saldo->saldo < $jumlahBayar) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Saldo santri tidak mencukupi untuk melunasi tagihan.'
                ];
            }

            // Update tagihan santri → lunas
            $tagihanSantri->update([
                'status'         => 'lunas',
                'tanggal_bayar'  => now(),
                'updated_by'     => $user->id,
            ]);

            // Simpan saldo lama (untuk log)
            $saldoLama = $saldo->saldo;

            // Update saldo santri
            $saldo->update([
                'saldo' => $saldo->saldo - $jumlahBayar,
                'updated_by' => $user->id,
            ]);

            // Ambil kartu aktif
            $uidKartu = Kartu::where('santri_id', $tagihanSantri->santri_id)
                ->where('aktif', true)
                ->value('uid_kartu');

            // Catat transaksi saldo
            $transaksi = TransaksiSaldo::create([
                'santri_id'      => $tagihanSantri->santri_id,
                'outlet_id'      => null,
                'kategori_id'    => null,
                'user_outlet_id' => null,
                'uid_kartu'      => $uidKartu,
                'tipe'           => 'debit',
                'jumlah'         => $jumlahBayar,
                'keterangan'     => 'Pembayaran tagihan ' . $tagihanSantri->tagihan->nama_tagihan . ' oleh Orangtua',
                'created_by'     => $user->id,
            ]);

            // Log aktivitas
            activity('transaksi_saldo')
                ->causedBy($user)
                ->performedOn($saldo)
                ->withProperties([
                    'santri_id'     => $tagihanSantri->santri_id,
                    'tipe'          => 'debit',
                    'jumlah'        => $jumlahBayar,
                    'saldo_sebelum' => $saldoLama,
                    'saldo_sesudah' => $saldo->saldo,
                    'transaksi_id'  => $transaksi->id,
                    'tagihan_id'    => $tagihanSantri->tagihan_id,
                    'ip'            => request()->ip(),
                    'user_agent'    => request()->userAgent(),
                ])
                ->event('success')
                ->log("Pembayaran tagihan {$tagihanSantri->tagihan->nama_tagihan} sebesar Rp{$jumlahBayar} berhasil");

            return [
                'success' => true,
                'data' => [
                    'tagihan_id'     => $tagihanSantri->tagihan_id,
                    'santri_id'      => $tagihanSantri->santri_id,
                    'dibayar'        => $jumlahBayar,
                    'status_tagihan' => $tagihanSantri->status,
                    'sisa_saldo'     => $saldo->saldo,
                ],
                'message' => 'Tagihan berhasil dilunasi.'
            ];
        });
    }
}
