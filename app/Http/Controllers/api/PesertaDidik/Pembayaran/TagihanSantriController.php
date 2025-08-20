<?php

namespace App\Http\Controllers\api\PesertaDidik\Pembayaran;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\PesertaDidik\Pembayaran\TagihanSantriService;
use App\Http\Requests\PesertaDidik\Pembayaran\TagihanSantriRequest;

class TagihanSantriController extends Controller
{
    private TagihanSantriService $service;

    public function __construct(TagihanSantriService $service)
    {
        $this->service = $service;
    }

    public function assign(TagihanSantriRequest $request)
    {
        $result = $this->service->assignToSantri($request->validated(), Auth::id());
        return response()->json($result, $result['success'] ? 200 : 500);
    }
}
