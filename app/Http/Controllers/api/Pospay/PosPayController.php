<?php

namespace App\Http\Controllers\api\Pospay;

use App\Http\Controllers\Controller;
use App\Http\Requests\PosPay\InquiryRequest;
use App\Http\Requests\PosPay\PaymentRequest;
use App\Services\PosPay\PospayService;

class PosPayController extends Controller
{
    protected PospayService $pospayservice;

    public function __construct(PospayService $pospayservice)
    {
        $this->pospayservice = $pospayservice;
    }

    public function inquiry(InquiryRequest $request)
    {
        $result = $this->pospayservice->processInquiry($request);
        return response()->json($result);
    }

    public function payment(PaymentRequest $request)
    {
        $result = $this->pospayservice->processPayment($request);
        return response()->json($result);
    }

    // public function inquiry(PospayRequest $request)
    // {
    //     // Cari data tagihan di database
    //     $tagihan = TagihanSantri::where('santri_id', $request->BillKey1)->first();

    //     if ($tagihan) {
    //         // Format response sukses
    //         $response = [
    //             'RequestID' => $request->RequestID,
    //             'KodeBiller' => $request->KodeBiller,
    //             'TerminalCode' => $request->TerminalCode,
    //             'TerminalName' => $request->TerminalName,
    //             'BillKey1' => $request->BillKey1,
    //             'BillKey2' => $request->BillKey2,
    //             'BillKey3' => $request->BillKey3,
    //             'ResponseCode' => '000',
    //             'ResponseMessage' => 'SUKSES INQUIRY',
    //             'BillDetail' => [
    //                 'NoPelanggan' => $tagihan->id_santri,
    //                 'NamaPelanggan' => $tagihan->nama_santri,
    //                 'Alamat' => $tagihan->alamat,
    //                 'JumlahBilling' => '1',
    //                 'BillingAmount' => $tagihan->jumlah_tagihan,
    //                 'AdminPOS' => '0',
    //                 'TotalAmount' => $tagihan->jumlah_tagihan,
    //                 'Info1' => $tagihan->periode,
    //                 'Info2' => $tagihan->kelas,
    //             ],
    //         ];
    //         return response()->json($response);
    //     } else {
    //         // Format response gagal
    //         $response = [
    //             'RequestID' => $request->RequestID,
    //             'KodeBiller' => $request->KodeBiller,
    //             'TerminalCode' => $request->TerminalCode,
    //             'TerminalName' => $request->TerminalName,
    //             'BillKey1' => $request->BillKey1,
    //             'BillKey2' => $request->BillKey2,
    //             'BillKey3' => $request->BillKey3,
    //             'ResponseCode' => '101', // Contoh kode gagal
    //             'ResponseMessage' => 'Tagihan tidak ditemukan',
    //         ];
    //         return response()->json($response, 404);
    //     }
    // }

    // public function payment(PaymentRequest $request)
    // {
    //     // Validasi request
    //     $validatedData = $request->validate([
    //         'RequestID' => 'required',
    //         'KodeBiller' => 'required',
    //         'TerminalCode' => 'required',
    //         'TerminalName' => 'required',
    //         'BillKey1' => 'required',
    //         'PaymentCode' => 'required',
    //         'PaymentAmount' => 'required',
    //     ]);

    //     // Cari tagihan di database
    //     $tagihan = Tagihan::where('id_santri', $request->BillKey1)->first();

    //     if ($tagihan) {
    //         // Update status tagihan menjadi lunas
    //         $tagihan->status = 'lunas';
    //         $tagihan->save();

    //         // Format response sukses
    //         $response = [
    //             'RequestID' => $request->RequestID,
    //             'KodeBiller' => $request->KodeBiller,
    //             'TerminalCode' => $request->TerminalCode,
    //             'TerminalName' => $request->TerminalName,
    //             'BillKey1' => $request->BillKey1,
    //             'BillKey2' => $request->BillKey2,
    //             'BillKey3' => $request->BillKey3,
    //             'ResponseCode' => '000',
    //             'ResponseMessage' => 'SUKSES PAYMENT',
    //             'PaymentCode' => $request->PaymentCode,
    //             'PaymentAmount' => $request->PaymentAmount
    //         ];
    //         return response()->json($response);
    //     } else {
    //         // Format response gagal
    //         $response = [
    //             'RequestID' => $request->RequestID,
    //             'KodeBiller' => $request->KodeBiller,
    //             'TerminalCode' => $request->TerminalCode,
    //             'TerminalName' => $request->TerminalName,
    //             'BillKey1' => $request->BillKey1,
    //             'BillKey2' => $request->BillKey2,
    //             'BillKey3' => $request->BillKey3,
    //             'ResponseCode' => '102',
    //             'ResponseMessage' => 'Tagihan tidak ditemukan',
    //         ];
    //         return response()->json($response, 404);
    //     }
    // }
}
