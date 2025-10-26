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
}
