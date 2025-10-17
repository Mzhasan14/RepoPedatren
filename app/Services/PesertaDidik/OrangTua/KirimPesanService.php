<?php

namespace App\Services\PesertaDidik\OrangTua;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KirimPesanService
{
    public function SendMessage(array $request)
    {
        $user = Auth::user();
        $noKk = $user->no_kk;

        $ListSantri = DB::table('santri as s')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->join('keluarga as k', 'k.id_biodata', '=', 'b.id')
            ->where('k.no_kk', $noKk)
            ->select('s.id as santri_id', 'b.nama as nama_lengkap')
            ->get();

        if ($ListSantri->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada data anak yang ditemukan.',
                'data' => null,
                'status' => 404,
            ];
        }

        $dataAnak = $ListSantri->firstWhere('santri_id', $request['santri_id'] ?? null);
        if (!$dataAnak) {
            return [
                'success' => false,
                'message' => 'Santri tidak valid untuk user ini.',
                'data' => null,
                'status' => 403,
            ];
        }

        $OrtuID = DB::table('biodata as b')
            ->join('keluarga as k', 'k.id_biodata', '=', 'b.id')
            ->join('orang_tua_wali as otw', 'otw.id_biodata', '=', 'b.id')
            ->where('k.no_kk', $noKk)
            ->select('otw.id as ortu_id')
            ->first();

        if (!$OrtuID) {
            return [
                'success' => false,
                'message' => 'Data orang tua tidak ditemukan.',
                'data' => null,
                'status' => 404,
            ];
        }

        $Send = DB::table('pesan_santri')->insert([
            'orangtua_id' => $OrtuID->ortu_id,
            'santri_id'   => $dataAnak->santri_id,
            'pesan'       => $request['pesan'] ?? null,
            'created_by'  => $user->id,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        if ($Send) {
            activity('pesan_santri_create')
                ->causedBy($user)
                ->withProperties([
                    'orangtua_id'   => $OrtuID->ortu_id,
                    'santri_id'     => $dataAnak->santri_id,
                    'nama_santri'   => $dataAnak->nama_lengkap,
                    'pesan'         => $request['pesan'],
                    'ip'            => request()->ip(),
                    'user_agent'    => request()->userAgent(),
                    'waktu_kirim'   => now()->toDateTimeString(),
                ])
                ->event('create_pesan_santri')
                ->log("Pesan dari orang tua berhasil dikirim ke santri {$dataAnak->nama_lengkap}");
        }

        return [
            'success' => $Send,
            'message' => $Send ? 'Pesan berhasil dikirim.' : 'Gagal mengirim pesan.',
            'data' => [
                'orangtua_id' => $OrtuID->ortu_id,
                'santri_id'   => $dataAnak->santri_id,
                'pesan'       => $request->pesan ?? null,
            ],
            'status' => $Send ? 200 : 500,
        ];
    }

    public function ReadMessageOrtu(array $request)
    {
        $user = Auth::user();
        $noKk = $user->no_kk;

        $ListSantri = DB::table('santri as s')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->join('keluarga as k', 'k.id_biodata', '=', 'b.id')
            ->where('k.no_kk', $noKk)
            ->select('s.id as santri_id', 'b.nama as nama_lengkap')
            ->get();

        if ($ListSantri->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada data anak yang ditemukan.',
                'data' => null,
                'status' => 404,
            ];
        }

        $dataAnak = $ListSantri->firstWhere('santri_id', $request['santri_id'] ?? null);
        if (!$dataAnak) {
            return [
                'success' => false,
                'message' => 'Santri tidak valid untuk user ini.',
                'data' => null,
                'status' => 403,
            ];
        }

        $PesanList = DB::table('pesan_santri as p')
            ->join('orang_tua_wali as o', 'o.id', '=', 'p.orangtua_id')
            ->join('biodata as b', 'b.id', '=', 'o.id_biodata')
            ->where('p.santri_id', $dataAnak->santri_id)
            ->select(
                'p.id',
                'p.status',
                'p.tanggal_baca',
                'p.pesan',
                'p.created_at'
            )
            ->orderBy('p.created_at', 'desc')
            ->get();

        if ($PesanList->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Belum ada pesan dari orang tua untuk santri ini.',
                'data' => [],
                'status' => 404,
            ];
        }

        return [
            'success' => true,
            'message' => 'Data pesan berhasil diambil.',
            'data' => $PesanList,
            'status' => 200,
        ];
    }
}
