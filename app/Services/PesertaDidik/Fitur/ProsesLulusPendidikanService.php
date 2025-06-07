<?php

namespace App\Services\PesertaDidik\Fitur;

use App\Models\Biodata;
use App\Models\Pendidikan;
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

        $biodataList = Biodata::whereIn('id', $bioIds)->pluck('nama', 'id');

        $pdAktif = Pendidikan::whereIn('biodata_id', $bioIds)
            ->where('status', 'aktif')
            ->latest('id')
            ->get()
            ->keyBy('biodata_id');

        $dataGagal = [];
        $dataBerhasil = [];

        foreach ($bioIds as $bioId) {
            $pd = $pdAktif->get($bioId);
            $nama = $biodataList[$bioId] ?? 'Tidak diketahui';

            if (is_null($pd)) {
                $dataGagal[] = [
                    'nama' => $nama,
                    'message' => 'Pendidikan aktif tidak ditemukan.',
                ];
                continue;
            }

            try {
                DB::beginTransaction();

                // Insert ke riwayat dengan status lulus
                RiwayatPendidikan::create([
                    'biodata_id' => $pd->biodata_id,
                    'lembaga_id' => $pd->lembaga_id,
                    'jurusan_id' => $pd->jurusan_id ?? null,
                    'kelas_id' => $pd->kelas_id ?? null,
                    'rombel_id' => $pd->rombel_id ?? null,
                    'no_induk' => $pd->no_induk ?? null,
                    'angkatan_id' => $pd->angkatan_id,
                    'status' => 'lulus',
                    'tanggal_masuk' => $pd->tanggal_masuk,
                    'tanggal_keluar' => $now,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Hapus data aktif dari pendidikan
                $pd->update([
                    'status' => 'lulus',
                    'updated_by' => $userId,
                    'updated_at' => $now
                ]);

                DB::commit();

                $dataBerhasil[] = [
                    'nama' => $nama,
                    'message' => 'Berhasil di-set lulus.',
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

    public function batalLulus(array $data)
    {
        $now = now();
        $userId = Auth::id();
        $bioIds = $data['biodata_id'] ?? [];

        if (empty($bioIds)) {
            return [
                'success' => false,
                'message' => 'Tidak ada data biodata_id yang dikirim.',
                'data_berhasil' => [],
                'data_gagal' => [],
            ];
        }

        $biodataList = Biodata::whereIn('id', $bioIds)->pluck('nama', 'id');

        // Ambil riwayat pendidikan yang berstatus lulus
        $riwayatLulus = RiwayatPendidikan::whereIn('biodata_id', $bioIds)
            ->where('status', 'lulus')
            ->whereNotNull('tanggal_keluar')
            ->latest('id')
            ->get()
            ->keyBy('biodata_id');

        // Ambil data pendidikan yang berstatus lulus
        $pendidikanLulus = Pendidikan::whereIn('biodata_id', $bioIds)
            ->where('status', 'lulus')
            ->latest('id')
            ->get()
            ->keyBy('biodata_id');

        $dataBerhasil = collect();
        $dataGagal = collect();

        foreach ($bioIds as $bioId) {
            $nama = $biodataList[$bioId] ?? 'Tidak diketahui';
            $rp = $riwayatLulus->get($bioId);
            $pd = $pendidikanLulus->get($bioId);

            if (!$rp) {
                $dataGagal->push([
                    'nama' => $nama,
                    'message' => 'Riwayat lulus tidak ditemukan.',
                ]);
                continue;
            }

            if (!$pd) {
                $dataGagal->push([
                    'nama' => $nama,
                    'message' => 'Data pendidikan berstatus lulus tidak ditemukan.',
                ]);
                continue;
            }

            // Cek apakah tanggal_keluar masih dalam 30 hari terakhir
            $daysDiff = $rp->tanggal_keluar->diffInDays($now);
            if ($daysDiff > 30) {
                $dataGagal->push([
                    'nama' => $nama,
                    'message' => 'Pembatalan tidak dapat dilakukan karena tanggal keluar melebihi batas waktu 30 hari.',
                ]);
                continue;
            }

            try {
                DB::beginTransaction();

                // Update riwayat menjadi batal_lulus
                $rp->update([
                    'status' => 'batal_lulus',
                    'tanggal_keluar' => null,
                    'updated_at' => $now,
                    'updated_by' => $userId,
                ]);

                // Update pendidikan dari lulus menjadi aktif
                $pd->update([
                    'status' => 'aktif',
                    'updated_at' => $now,
                    'updated_by' => $userId,
                ]);

                DB::commit();

                $dataBerhasil->push([
                    'nama' => $nama,
                    'message' => 'Status lulus berhasil dibatalkan.',
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                $dataGagal->push([
                    'nama' => $nama,
                    'message' => 'Gagal membatalkan lulus: ' . $e->getMessage(),
                ]);
            }
        }

        return [
            'success' => true,
            'message' => 'Proses batal lulus selesai.',
            'data_berhasil' => $dataBerhasil->all(),
            'data_gagal' => $dataGagal->all(),
        ];
    }


    public function listDataLulus(Request $request)
    {
        $tanggalBatas = now()->subDays(30)->toDateString(); // tanggal 30 hari yang lalu

        // 1) Sub-query: tanggal_keluar terakhir untuk masing-masing biodata yang lulus
        $rpLast = DB::table('riwayat_pendidikan')
            ->select('biodata_id', DB::raw('MAX(tanggal_keluar) AS max_tanggal_keluar'))
            ->where('status', 'lulus')
            ->groupBy('biodata_id');

        $query = DB::table('biodata as b')
            ->leftJoinSub(
                $rpLast,
                'lr',
                fn($j) =>
                $j->on('lr.biodata_id', '=', 'b.id')
            )
            ->leftJoin(
                'riwayat_pendidikan as rp',
                fn($j) =>
                $j->on('rp.biodata_id', '=', 'lr.biodata_id')
                    ->on('rp.tanggal_keluar', '=', 'lr.max_tanggal_keluar')
            )
            ->leftJoin('lembaga as l', 'rp.lembaga_id', '=', 'l.id')
            ->where('rp.status', 'lulus')
            ->whereDate('rp.tanggal_keluar', '>=', $tanggalBatas) // maksimal 30 hari
            ->whereNull('b.deleted_at')
            ->whereNull('rp.deleted_at')
            ->select([
                'rp.id as riwayat_id',
                'b.id as biodata_id',
                'b.nama',
                'rp.no_induk',
                'rp.status',
                'rp.tanggal_keluar',
            ])
            ->orderBy('rp.updated_at', 'desc');

        return $query;
    }


    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'id'       => $item->riwayat_id,
            'biodata_id'       => $item->biodata_id,
            'no_induk'  => $item->no_induk ?? '-',
            'nama'             => $item->nama,
            'status' => $item->status,
        ]);
    }
}
