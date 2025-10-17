<?php

namespace App\Services\PosPay;

use App\Models\Santri;
use App\Models\Saldo;
use App\Models\TransaksiSaldo;
use App\Models\VirtualAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PospayService
{
    public function processInquiry(object $request): array
    {
        $va = (string) $request->BillKey1;

        if (empty($va)) {
            return [
                'RequestID'       => $request->RequestID,
                'KodeBiller'      => $request->KodeBiller,
                'TerminalCode'    => $request->TerminalCode,
                'TerminalName'    => $request->TerminalName,
                'BillKey1'        => $request->BillKey1,
                'ResponseCode' => '404',
                'ResponseMessage' => 'Billkey tidak boleh kosong',
            ];
        }

        $santri = VirtualAccount::join('santri as s', 'virtual_accounts.santri_id', 's.id')
            ->join('biodata as b', 's.biodata_id', 'b.id')
            ->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
            ->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
            ->leftJoin('negara as ng', 'b.negara_id', '=', 'ng.id')
            ->select(
                's.id as santri_id',
                'b.nama as nama_santri',
                DB::raw("
                    CONCAT_WS(', ',
                        CONCAT('Kec. ', IFNULL(kc.nama_kecamatan, '')),
                        CONCAT('Kab. ', IFNULL(kb.nama_kabupaten, '')),
                        CONCAT('Prov. ', IFNULL(pv.nama_provinsi, '')),
                        IFNULL(ng.nama_negara, '')
                    ) AS alamat_santri
                ")
            )
            ->where('virtual_accounts.va_number', $va)
            ->first();

        if (! $santri) {
            return [
                'RequestID' => $request->RequestID,
                'ResponseCode' => '404',
                'ResponseMessage' => 'Billkey tidak valid',
            ];
        }

        return [
            'RequestID'       => $request->RequestID,
            'KodeBiller'      => $request->KodeBiller,
            'TerminalCode'    => $request->TerminalCode,
            'TerminalName'    => $request->TerminalName,
            'BillKey1'        => $request->BillKey1,
            'BillKey2'        => $request->BillKey2,
            'BillKey3'        => $request->BillKey3,
            'ResponseCode'    => '000',
            'ResponseMessage' => 'SUKSES INQUIRY',
            'BillDetail'      => [
                'NoPelanggan'   => $santri->santri_id,
                'NamaPelanggan' => $santri->nama_santri,
                'Alamat'        => $santri->alamat_santri,
                'JumlahBilling' => null,
                'BillingAmount' => null,
                'AdminPOS'      => 3000,
                'TotalAmount'   => null,
                'Info1'         => null,
                'Info2'         => null,
                'Info3'         => null,
                'Info4'         => null,
                'Info5'         => null,
                'Info6'         => null,
                'Info7'         => null,
                'Info8'         => null,
                'Info9'         => null,
                'Info10'         => null,
                'JenisBilling'  => 'OPEN',
            ],
        ];
    }

    public function processPayment(object $request): array
    {
        $va = (string) $request->BillKey1;
        $nominal = (float) $request->PaymentAmount;

        try {
            $santriID = VirtualAccount::where('va_number', $va)->value('santri_id');

            if (!$santriID) {
                return [
                    'RequestID'       => $request->RequestID ?? null,
                    'KodeBiller'      => $request->KodeBiller ?? null,
                    'TerminalCode'    => $request->TerminalCode ?? null,
                    'TerminalName'    => $request->TerminalName ?? null,
                    'BillKey1'        => $request->BillKey1 ?? null,
                    'BillKey2'        => $request->BillKey2 ?? null,
                    'BillKey3'        => $request->BillKey3 ?? null,
                    'ResponseCode'    => '404',
                    'ResponseMessage' => 'Billkey tidak valid',
                ];
            }

            DB::transaction(function () use ($santriID, $nominal) {
                $saldo = Saldo::firstOrCreate(
                    ['santri_id' => $santriID],
                    ['saldo' => 0]
                );

                $saldo->increment('saldo', $nominal);

                TransaksiSaldo::create([
                    'santri_id'      => $santriID,
                    'uid_kartu'      => null,
                    'outlet_id'      => null,
                    'kategori_id'    => null,
                    'user_outlet_id' => null,
                    'tipe'           => 'topup',
                    'jumlah'         => $nominal,
                    'keterangan'     => 'Topup saldo melalui Pospay sebesar Rp' . number_format($nominal, 0, ',', '.'),
                ]);
            });

            return [
                'RequestID'       => $request->RequestID,
                'KodeBiller'      => $request->KodeBiller,
                'TerminalCode'    => $request->TerminalCode,
                'TerminalName'    => $request->TerminalName,
                'BillKey1'        => $request->BillKey1,
                'BillKey2'        => $request->BillKey2,
                'BillKey3'        => $request->BillKey3,
                'ResponseCode'    => '000',
                'ResponseMessage' => 'SUKSES PAYMENT',
                'PaymentCode'     => $request->PaymentCode,
                'PaymentAmount'   => $request->PaymentAmount,
            ];
        } catch (Exception $e) {
            Log::error('Terjadi error saat payment', [
                'error'   => $e->getMessage(),
                'request' => $request,
            ]);

            return [
                'RequestID'       => $request->RequestID ?? null,
                'KodeBiller'      => $request->KodeBiller ?? null,
                'TerminalCode'    => $request->TerminalCode ?? null,
                'TerminalName'    => $request->TerminalName ?? null,
                'BillKey1'        => $request->BillKey1 ?? null,
                'BillKey2'        => $request->BillKey2 ?? null,
                'BillKey3'        => $request->BillKey3 ?? null,
                'ResponseCode'    => '500',
                'ResponseMessage' => 'Terjadi kesalahan sistem',
            ];
        }
    }
}