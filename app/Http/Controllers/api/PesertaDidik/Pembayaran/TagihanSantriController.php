<?php

namespace App\Http\Controllers\api\PesertaDidik\Pembayaran;

use App\Models\Tagihan;
use App\Models\TagihanSantri;
use App\Http\Controllers\Controller;
use App\Services\PesertaDidik\Pembayaran\TagihanSantriService;
use App\Http\Requests\PesertaDidik\Pembayaran\TagihanSantriRequest;

class TagihanSantriController extends Controller
{
    protected TagihanSantriService $service;

    public function __construct(TagihanSantriService $service)
    {
        $this->service = $service;
    }

    // List semua tagihan
    public function index()
    {
        $data = Tagihan::withCount('tagihanSantri')->get();
        return response()->json($data);
    }

    // Detail satu tagihan + daftar santri yang kena tagihan
    public function show($id)
    {
        $tagihan = Tagihan::with(['tagihanSantri.santri'])->findOrFail($id);
        return response()->json($tagihan);
    }

    // Generate tagihan santri
    public function generate(TagihanSantriRequest $request)
    {
        $result = $this->service->generate(
            $request->tagihan_id,
            $request->periode,
            $request->only('jenis_kelamin')
        );

        return response()->json([
            'message'             => 'Tagihan santri berhasil digenerate.',
            'total_santri_biasa'  => $result['total_santri_biasa'],
            'total_anak_pegawai'  => $result['total_anak_pegawai'],
            'total_bersaudara'    => $result['total_bersaudara'],
            'total_khadam'        => $result['total_khadam'],
            'total_putra'         => $result['total_putra'],
            'total_putri'         => $result['total_putri'],
        ]);
    }



    // List semua tagihan per santri (opsional tambahan)
    public function listBySantri($santriId)
    {
        $tagihanSantri = TagihanSantri::with('tagihan')
            ->where('santri_id', $santriId)
            ->get();

        return response()->json($tagihanSantri);
    }
}
