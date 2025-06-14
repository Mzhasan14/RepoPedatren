<?php

namespace App\Services\PesertaDidik\Fitur;

use Carbon\Carbon;
use App\Models\Santri;
use App\Models\Biodata;
use App\Models\Pendidikan;
use Illuminate\Http\Request;
use App\Models\RiwayatPendidikan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProsesLulusSantriService
{
    public function prosesLulusSantri(array $data)
    {
        $now = now();
        $userId = Auth::id();
        $santriIds = $data['santri_id'];

        // Ambil data santri + nama dari relasi biodata
        $santriList = Santri::with('biodata')->whereIn('id', $santriIds)->get()->keyBy('id');
        $dataBerhasil = [];
        $dataGagal = [];

        foreach ($santriIds as $santriId) {
            $santri = $santriList->get($santriId);
            $nama = $santri?->biodata?->nama ?? 'Tidak diketahui';

            if (is_null($santri)) {
                $dataGagal[] = [
                    'nama' => $nama,
                    'message' => 'Data santri tidak ditemukan.',
                ];
                continue;
            }

            if ($santri->status !== 'aktif') {
                $dataGagal[] = [
                    'nama' => $nama,
                    'message' => 'Status santri bukan aktif.',
                ];
                continue;
            }

            try {
                DB::beginTransaction();

                $santri->update([
                    'status' => 'alumni',
                    'tanggal_keluar' => $now,
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);

                DB::commit();
                $dataBerhasil[] = [
                    'nama' => $nama,
                    'message' => 'Berhasil di-set lulus (alumni).',
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                $dataGagal[] = [
                    'nama' => $nama,
                    'message' => 'Gagal memproses lulus: ' . $e->getMessage(),
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Proses set lulus selesai.',
            'data_berhasil' => $dataBerhasil,
            'data_gagal' => $dataGagal,
        ];
    }


    public function batalLulusSantri(array $data)
    {
        $now = now();
        $userId = Auth::id();
        $santriIds = $data['santri_id'] ?? [];

        if (empty($santriIds)) {
            return [
                'success' => false,
                'message' => 'Tidak ada data santri_id yang dikirim.',
                'data_berhasil' => [],
                'data_gagal' => [],
            ];
        }

        $santriList = Santri::with('biodata')->whereIn('id', $santriIds)->get()->keyBy('id');
        $dataBerhasil = [];
        $dataGagal = [];

        foreach ($santriIds as $santriId) {
            $santri = $santriList->get($santriId);
            $nama = $santri?->biodata?->nama ?? 'Tidak diketahui';

            if (is_null($santri)) {
                $dataGagal[] = [
                    'nama' => $nama,
                    'message' => 'Data santri tidak ditemukan.',
                ];
                continue;
            }

            if ($santri->status !== 'alumni') {
                $dataGagal[] = [
                    'nama' => $nama,
                    'message' => 'Status santri bukan alumni.',
                ];
                continue;
            }

            // Cek tanggal_keluar maksimal 30 hari lalu
            if (!$santri->tanggal_keluar || $santri->tanggal_keluar->diffInDays($now) > 30) {
                $dataGagal[] = [
                    'nama' => $nama,
                    'message' => 'Pembatalan tidak dapat dilakukan karena tanggal keluar melebihi 30 hari.',
                ];
                continue;
            }

            try {
                DB::beginTransaction();

                $santri->update([
                    'status' => 'aktif',
                    'tanggal_keluar' => null,
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);

                DB::commit();
                $dataBerhasil[] = [
                    'nama' => $nama,
                    'message' => 'Status lulus (alumni) berhasil dibatalkan.',
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                $dataGagal[] = [
                    'nama' => $nama,
                    'message' => 'Gagal membatalkan lulus: ' . $e->getMessage(),
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Proses batal lulus selesai.',
            'data_berhasil' => $dataBerhasil,
            'data_gagal' => $dataGagal,
        ];
    }


    public function listSantriLulus(Request $request)
    {
        $tanggalBatas = now()->subDays(30)->toDateString(); // 30 hari ke belakang dari hari ini

        // Subquery dan join seperti sebelumnya
        $santriLast = DB::table('santri')
            ->select('biodata_id', DB::raw('MAX(tanggal_keluar) AS max_tanggal_keluar'))
            ->where('status', 'alumni')
            ->groupBy('biodata_id');

        $rdLast = DB::table('riwayat_domisili')
            ->select('santri_id', DB::raw('MAX(tanggal_keluar) AS max_tanggal_keluar'))
            ->where('status', 'keluar')
            ->groupBy('santri_id');

        $query = DB::table('biodata as b')
            ->leftJoinSub($santriLast, 'sl', fn($j) => $j->on('sl.biodata_id', '=', 'b.id'))
            ->leftJoin('santri as s', fn($j) => $j->on('s.biodata_id', '=', 'sl.biodata_id')->on('s.tanggal_keluar', '=', 'sl.max_tanggal_keluar'))
            ->leftJoinSub($rdLast, 'lr', fn($j) => $j->on('lr.santri_id', '=', 'b.id'))
            ->leftJoin('riwayat_domisili as rd', fn($j) => $j->on('rd.santri_id', '=', 'lr.santri_id')->on('rd.tanggal_keluar', '=', 'lr.max_tanggal_keluar'))
            ->leftJoin('wilayah as w', 'rd.wilayah_id', '=', 'w.id')
            ->where('s.status', 'alumni')
            ->whereDate('s.tanggal_keluar', '>=', $tanggalBatas) // maksimal 30 hari terakhir
            ->whereNull('b.deleted_at')
            ->whereNull('s.deleted_at')
            ->select([
                's.id as santri_id',
                'b.id as biodata_id',
                'b.nama',
                's.nis',
                's.status',
                's.tanggal_keluar'
            ])
            ->orderBy('s.updated_at', 'desc');

        return $query;
    }


    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'id' => $item->santri_id,
            'biodata_id' => $item->biodata_id,
            'nis' => $item->nis ?? '-',
            'nama' => $item->nama,
            'status' => $item->status,
        ]);
    }
}
