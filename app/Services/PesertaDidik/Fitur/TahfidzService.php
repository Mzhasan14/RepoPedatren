<?php

namespace App\Services\PesertaDidik\Fitur;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TahfidzService
{
    public function setoranTahfidz(array $data)
    {
        DB::beginTransaction();

        try {
            // Insert setoran
            $setoranId = DB::table('tahfidz')->insertGetId([
                'santri_id' => $data['santri_id'],
                'tahun_ajaran_id' => $data['tahun_ajaran_id'],
                'tanggal' => $data['tanggal'],
                'jenis_setoran' => $data['jenis_setoran'],
                'surat' => $data['surat'] ?? null,
                'ayat_mulai' => $data['ayat_mulai'] ?? null,
                'ayat_selesai' => $data['ayat_selesai'] ?? null,
                'nilai' => $data['nilai'],
                'catatan' => $data['catatan'] ?? null,
                'status' => $data['status'],
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($data['status'] === 'tuntas' && $data['jenis_setoran'] === 'baru') {
                $suratUnik = DB::table('tahfidz')
                    ->where('santri_id', $data['santri_id'])
                    ->where('tahun_ajaran_id', $data['tahun_ajaran_id'])
                    ->where('status', 'tuntas')
                    ->where('jenis_setoran', 'baru')
                    ->distinct()
                    ->pluck('surat');

                $totalSurat = $suratUnik->count();

                $persentase = ($totalSurat / 114) * 100;

                DB::table('rekap_tahfidz')->updateOrInsert(
                    [
                        'santri_id' => $data['santri_id'],
                        'tahun_ajaran_id' => $data['tahun_ajaran_id'],
                    ],
                    [
                        'total_surat' => $totalSurat,
                        'persentase_khatam' => round($persentase, 2),
                        'updated_at' => now(),
                        'created_by' => Auth::id(),
                    ]
                );
            }

            DB::commit();
            return $setoranId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal simpan setoran: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function listSetoran(Request $id)
    {
        $query = DB::table('tahfidz as t')
            ->leftJoin('santri', 't.santri_id', '=', 'santri.id')
            ->leftJoin('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->leftJoin('tahun_ajaran', 't.tahun_ajaran_id', '=', 'tahun_ajaran.id')
            ->select(
                'biodata.nama as santri_nama',
                't.tanggal',
                't.jenis_setoran',
                DB::raw("
                    CONCAT(
                        t.surat,
                        ' ',
                        t.ayat_mulai,
                        CASE
                            WHEN t.ayat_selesai IS NOT NULL AND t.ayat_selesai != t.ayat_mulai THEN CONCAT('-', t.ayat_selesai)
                            ELSE ''
                        END
                    ) AS surat
                "),
                't.nilai',
                't.catatan',
                't.status'
            )
            ->where('t.santri_id', $id)
            ->where(fn($q) => $q->whereNull('biodata.deleted_at')
                ->whereNull('santri.deleted_at'))
            // ->where('santri.status', 'aktif')
            ->orderBy('t.id', 'desc');

        return $query->get();
    }

    public function listRekap(Request $id)
    {
        $query = DB::table('rekap_tahfidz as rt')
            ->join('santri', 'rt.santri_id', '=', 'santri.id')
            ->leftjoin('domisili_santri as ds', 'santri.id', '=', 'ds.santri_id')
            ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->leftjoin('pendidikan as pd', 'santri.id', '=', 'pd.biodata_id')
            ->join('tahun_ajaran', 'rt.tahun_ajaran_id', '=', 'tahun_ajaran.id')
            ->select('santri.nis', 'biodata.nama as santri_nama', 'rt.total_surat', 'rt.persentase_khatam')
            ->where('rt.santri_id', $id)
            ->where('santri.status', 'aktif')
            ->orderBy('rt.id', 'desc');

        return $query->get();
    }

    public function getAllRekap(Request $request)
    {
        $query = DB::table('rekap_tahfidz as rt')
            ->join('santri as s', 'rt.santri_id', '=', 's.id')
            ->leftJoin('domisili_santri as ds', 's.id', '=', 'ds.santri_id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftJoin('pendidikan as pd', 's.id', '=', 'pd.biodata_id')
            ->join('tahun_ajaran', 'rt.tahun_ajaran_id', '=', 'tahun_ajaran.id')
            ->select('s.id', 's.nis', 'b.nama as s_nama', 'rt.total_surat', 'rt.persentase_khatam')
            ->groupBy('s.id', 's.nis', 'b.nama', 'rt.total_surat', 'rt.persentase_khatam', 'rt.id')
            ->orderBy('rt.id', 'desc');

        if (! $request->filled('tahun_ajaran')) {
            return $query;
        }

        if ($request->filled('tahun_ajaran')) {
            $query->where('rt.tahun_ajaran', $request->tahun_ajaran);
        }

        return $query;
    }

    public function formatData($results)
    {
        return collect($results->items())->map(function ($item) {
            return [
                'santri_id'         => $item->id,
                'nis'               => $item->nis,
                'nama_santri'       => $item->s_nama,
                'total_surat'       => $item->total_surat,
                'persentase_khatam' => $item->persentase_khatam,
            ];
        });
    }
}
