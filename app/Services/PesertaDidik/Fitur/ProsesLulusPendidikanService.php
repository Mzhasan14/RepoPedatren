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
                    'angkatan_id' => $pd->angkatan,
                    'status' => 'lulus',
                    'tanggal_masuk' => $pd->tanggal_masuk,
                    'tanggal_keluar' => $now,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Hapus data aktif dari pendidikan
                $pd->delete();

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
        $bioIds = $data['biodata_id'];

        $biodataList = Biodata::whereIn('id', $bioIds)->pluck('nama', 'id');

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
                    'message' => 'Riwayat lulus tidak ditemukan.',
                ];
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

                // Insert kembali ke tabel pendidikan sebagai aktif
                Pendidikan::create([
                    'biodata_id' => $rp->biodata_id,
                    'angkatan_id' => $rp->angkatan,
                    'status' => 'aktif',
                    'tanggal_masuk' => $rp->tanggal_masuk,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::commit();

                $dataBerhasil[] = [
                    'nama' => $nama,
                    'message' => 'Status lulus berhasil dibatalkan.',
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
