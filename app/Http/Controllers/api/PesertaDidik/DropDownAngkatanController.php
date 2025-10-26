<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Http\Controllers\Controller;
use App\Models\Angkatan;

class DropDownAngkatanController extends Controller
{
    public function angkatanSantri()
    {
        $angkatan = Angkatan::where('kategori', 'santri')->where('status', true)->select('id', 'angkatan', 'kategori')->get();

        if ($angkatan->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data angkatan santri tidak ditemukan.',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data angkatan santri berhasil diambil.',
            'data' => $angkatan,
        ], 200);
    }

    public function angkatanPelajar()
    {
        $angkatan = Angkatan::where('kategori', 'pelajar')->where('status', true)->select('id', 'angkatan', 'kategori')->get();

        if ($angkatan->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data angkatan pelajar tidak ditemukan.',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data angkatan pelajar berhasil diambil.',
            'data' => $angkatan,
        ], 200);
    }
}
