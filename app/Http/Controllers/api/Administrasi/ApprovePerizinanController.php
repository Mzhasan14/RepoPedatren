<?php

namespace App\Http\Controllers\api\Administrasi;

use App\Http\Controllers\Controller;
use App\Models\Perizinan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovePerizinanController extends Controller
{
    public function approveByBiktren($id)
    {
        $perizinan = Perizinan::find($id);

        if (! $perizinan) {
            return response()->json(['message' => 'Perizinan tidak ditemukan']);
        }

        $perizinan->approved_by_biktren = true;
        $perizinan->biktren_id = Auth::id();

        if ($perizinan->approved_by_kamtib) {
            $perizinan->status = 'sudah berada diluar pondok';
        }

        $perizinan->save();

        return response()->json(['message' => 'Disetujui oleh biktren']);
    }

    public function TolakByBiktren(Request $request, $id)
    {
        $perizinan = Perizinan::find($id);

        if (! $perizinan) {
            return response()->json(['message' => 'Perizinan tidak ditemukan'], 404);
        }

        if ($perizinan->status !== 'sedang proses izin') {
            return response()->json([
                'message' => 'Aksi penolakan hanya berlaku untuk perizinan yang masih dalam proses.'
            ], 400);
        }

        $request->validate([
            'keterangan' => 'required|string|max:255',
        ]);

        $perizinan->update([
            'approved_by_biktren' => false,
            'status'     => 'perizinan ditolak',
            'biktren_id' => Auth::id(),
            'keterangan' => $request->input('keterangan'),
        ]);

        return response()->json([
            'message' => 'Perizinan ditolak oleh biktren',
            'data'    => $perizinan
        ]);
    }


    public function TolakByKamtib(Request $request, $id)
    {
        $perizinan = Perizinan::find($id);

        if (! $perizinan) {
            return response()->json(['message' => 'Perizinan tidak ditemukan'], 404);
        }
        if ($perizinan->status !== 'sedang proses izin') {
            return response()->json([
                'message' => 'Aksi penolakan hanya berlaku untuk perizinan yang masih dalam proses.'
            ], 400);
        }
        $request->validate([
            'keterangan' => 'required|string|max:255',
        ]);

        $perizinan->update([
            'approved_by_kamtib' => false,
            'status'     => 'perizinan ditolak',
            'kamtib_id'  => Auth::id(),
            'keterangan' => $request->input('keterangan'),
        ]);

        return response()->json(['message' => 'Perizinan ditolak oleh kamtib']);
    }

    public function approveByKamtib($id)
    {
        $perizinan = Perizinan::find($id);

        if (! $perizinan) {
            return response()->json(['message' => 'Perizinan tidak ditemukan']);
        }

        $perizinan->approved_by_kamtib = true;
        $perizinan->kamtib_id = Auth::id();

        if ($perizinan->approved_by_biktren) {
            $perizinan->status = 'sudah berada diluar pondok';
        }

        $perizinan->save();

        return response()->json(['message' => 'Disetujui oleh kamtib']);
    }

    public function approveByPengasuh($id)
    {
        $perizinan = Perizinan::find($id);

        if (! $perizinan) {
            return response()->json(['message' => 'Perizinan tidak ditemukan']);
        }

        // Cek apakah user adalah pengasuh yang berwenang (opsional)
        if (Auth::id() !== $perizinan->pengasuh_id) {
            return response()->json(['message' => 'Anda tidak berwenang menyetujui perizinan ini'], 403);
        }

        $perizinan->approved_by_pengasuh = true;
        $perizinan->pengasuh_id = Auth::id();

        $perizinan->save();

        return response()->json(['message' => 'Disetujui oleh pengasuh']);
    }
}
