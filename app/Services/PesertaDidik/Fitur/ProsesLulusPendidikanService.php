<?php

namespace App\Services\PesertaDidik\Fitur;

use App\Models\Biodata;
use Illuminate\Http\Request;
use App\Models\RiwayatPendidikan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProsesLulusPendidikanService
{
    public function prosesLulus(array $data)
    {
        $now = now();
        $userId = Auth::id();
        $bioIds = $data['biodata_id'];

        // Ambil semua nama lengkap berdasarkan biodata_id
        $biodataList = Biodata::whereIn('id', $bioIds)->pluck('nama', 'id');

        // Ambil semua riwayat aktif untuk biodata_id yang diberikan
        $riwayatAktif = RiwayatPendidikan::whereIn('biodata_id', $bioIds)
            ->where('status', 'aktif')
            ->latest('id')
            ->get()
            ->keyBy('biodata_id');

        $dataGagal = [];
        $dataBerhasil = [];

        foreach ($bioIds as $bioId) {
            $rp = $riwayatAktif->get($bioId);
            $nama = $biodataList[$bioId] ?? 'Tidak diketahui';

            if (is_null($rp) || !is_null($rp->tanggal_keluar)) {
                $dataGagal[] = [
                    'nama' => $nama,
                    'message' => 'Riwayat pendidikan tidak ditemukan atau sudah keluar.',
                ];
                continue;
            }

            // Update status jadi lulus
            $rp->update([
                'status' => 'lulus',
                'tanggal_keluar' => $now,
                'updated_at' => $now,
                'updated_by' => $userId,
            ]);

            $dataBerhasil[] = [
                'nama' => $nama,
                'message' => 'Berhasil di-set lulus.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Proses set lulus selesai.',
            'data_berhasil' => $dataBerhasil,
            'data_gagal' => $dataGagal,
        ];
    }

    public function batalLulus(array $data)
    {
        $now = now();
        $userId = Auth::id();
        $bioIds = $data['biodata_id'];

        // Ambil semua nama lengkap berdasarkan biodata_id
        $biodataList = Biodata::whereIn('id', $bioIds)->pluck('nama', 'id');

        // Ambil semua riwayat pendidikan yang statusnya lulus dan tanggal keluar tidak null
        $riwayatLulus = RiwayatPendidikan::whereIn('biodata_id', $bioIds)
            ->where('status', 'lulus')
            ->whereNotNull('tanggal_keluar')
            ->latest('id')
            ->get()
            ->keyBy('biodata_id');

        $dataGagal = [];
        $dataBerhasil = [];

        foreach ($bioIds as $bioId) {
            $rp = $riwayatLulus->get($bioId);
            $nama = $biodataList[$bioId] ?? 'Tidak diketahui';

            if (is_null($rp)) {
                $dataGagal[] = [
                    'nama' => $nama,
                    'message' => 'Riwayat pendidikan tidak ditemukan atau belum lulus.',
                ];
                continue;
            }

            // Update status jadi aktif dan hapus tanggal keluar
            $rp->update([
                'status' => 'aktif',
                'tanggal_keluar' => null,
                'updated_at' => $now,
                'updated_by' => $userId,
            ]);

            $dataBerhasil[] = [
                'nama' => $nama,
                'message' => 'Status lulus berhasil dibatalkan.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Proses batal lulus selesai.',
            'data_berhasil' => $dataBerhasil,
            'data_gagal' => $dataGagal,
        ];
    }

    public function listDataLulus(Request $request)
    {
        // 1) Sub‐query: tanggal_keluar riwayat_pendidikan alumni terakhir per santri
        $rpLast = DB::table('riwayat_pendidikan')
            ->select('biodata_id', DB::raw('MAX(tanggal_keluar) AS max_tanggal_keluar'))
            ->where('status', 'lulus')
            ->groupBy('biodata_id');

        return DB::table('biodata as b')
            ->leftjoin('status_peserta_didik AS spd', 'spd.biodata_id', '=', 'b.id')
            ->leftJoinSub($rpLast, 'lr', fn($j) => $j->on('lr.biodata_id', '=', 'b.id'))
            ->leftjoin('riwayat_pendidikan as rp', fn($j) => $j->on('rp.biodata_id', '=', 'lr.biodata_id')->on('rp.tanggal_keluar', '=', 'lr.max_tanggal_keluar'))
            ->leftJoin('lembaga as l', 'rp.lembaga_id', '=', 'l.id')
            ->where('spd.status_pelajar', 'lulus')
            ->where(fn($q) => $q->whereNull('b.deleted_at')
                ->whereNull('s.deleted_at')
                ->whereNull('rp.deleted_at'))
            ->select([
                'b.id as biodata_id',
                'b.nama',
                'rp.no_induk',
                'l.nama_lembaga',
                'rp.status',
            ])
            ->orderBy('rp.updated_at', 'desc');
    }
}
