<?php

namespace App\Services\PesertaDidik\Fitur;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NadhomanService
{
    public function setoranNadhoman(array $data)
    {
        DB::beginTransaction();

        try {
            // 1. Insert setoran nadhoman
            $setoranId = DB::table('nadhoman')->insertGetId([
                'santri_id'       => $data['santri_id'],
                'kitab_id'        => $data['kitab_id'],
                'tahun_ajaran_id' => $data['tahun_ajaran_id'],
                'tanggal'         => $data['tanggal'],
                'jenis_setoran'   => $data['jenis_setoran'],
                'bait_mulai'      => $data['bait_mulai'],
                'bait_selesai'    => $data['bait_selesai'],
                'nilai'           => $data['nilai'],
                'catatan'         => $data['catatan'] ?? null,
                'status'          => $data['status'],
                'created_by'      => Auth::id(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // 2. Update rekap jika tuntas & baru
            if ($data['status'] === 'tuntas' && $data['jenis_setoran'] === 'baru') {

                // Hitung total bait yang sudah tuntas (berdasarkan range bait)
                $totalBaitSelesai = DB::table('nadhoman')
                    ->where('santri_id', $data['santri_id'])
                    ->where('kitab_id', $data['kitab_id'])
                    ->where('tahun_ajaran_id', $data['tahun_ajaran_id'])
                    ->where('status', 'tuntas')
                    ->where('jenis_setoran', 'baru')
                    ->sum(DB::raw('(bait_selesai - bait_mulai + 1)'));

                // Ambil total bait dari tabel kitab
                $totalBaitKitab = DB::table('kitab')
                    ->where('id', $data['kitab_id'])
                    ->value('total_bait');

                $persentase = 0;
                if ($totalBaitKitab > 0) {
                    $persentase = ($totalBaitSelesai / $totalBaitKitab) * 100;
                }

                // Update atau insert ke rekap_nadhoman
                DB::table('rekap_nadhoman')->updateOrInsert(
                    [
                        'santri_id'       => $data['santri_id'],
                        'kitab_id'        => $data['kitab_id'],
                        'tahun_ajaran_id' => $data['tahun_ajaran_id'],
                    ],
                    [
                        'total_bait'         => $totalBaitSelesai,
                        'persentase_selesai' => round($persentase, 2),
                        'updated_at'         => now(),
                        'created_by'         => Auth::id(),
                    ]
                );
            }

            DB::commit();

            $santri = DB::table('santri')
                ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
                ->select('santri.nis', 'biodata.nama')
                ->where('santri.id', $data['santri_id'])
                ->first();

            activity('nadhoman')
                ->causedBy(Auth::user())
                ->performedOn(new \App\Models\Nadhoman(['id' => $setoranId]))
                ->withProperties([
                    'santri_id'  => $data['santri_id'],
                    'nis'        => $santri->nis ?? null,
                    'nama'    => $santri->nama ?? null,
                    'kitab_id'   => $data['kitab_id'],
                    'ip'         => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->event('create')
                ->log("Setoran nadhoman berhasil ditambahkan");
                
            return $setoranId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal simpan setoran nadhoman: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function listSetoran($santriId)
    {
        $query = DB::table('nadhoman as n')
            ->leftJoin('santri', 'n.santri_id', '=', 'santri.id')
            ->leftJoin('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->leftJoin('kitab', 'n.kitab_id', '=', 'kitab.id')
            ->leftJoin('tahun_ajaran', 'n.tahun_ajaran_id', '=', 'tahun_ajaran.id')
            ->select(
                'biodata.nama as santri_nama',
                'kitab.nama_kitab',
                'n.tanggal',
                'n.jenis_setoran',
                DB::raw("CONCAT(n.bait_mulai, '-', n.bait_selesai) AS bait"),
                'n.nilai',
                'n.catatan',
                'n.status'
            )
            ->where('n.santri_id', $santriId)
            ->where(fn($q) => $q->whereNull('biodata.deleted_at')
                ->whereNull('santri.deleted_at'))
            ->orderBy('n.id', 'desc');

        return $query->get();
    }

    public function listRekap($santriId)
    {
        $query = DB::table('rekap_nadhoman as rn')
            ->join('santri', 'rn.santri_id', '=', 'santri.id')
            ->leftJoin('domisili_santri as ds', 'santri.id', '=', 'ds.santri_id')
            ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->leftJoin('pendidikan as pd', 'santri.id', '=', 'pd.biodata_id')
            ->join('kitab', 'rn.kitab_id', '=', 'kitab.id')
            ->join('tahun_ajaran', 'rn.tahun_ajaran_id', '=', 'tahun_ajaran.id')
            ->select(
                'santri.nis',
                'biodata.nama as santri_nama',
                'kitab.nama_kitab',
                'rn.total_bait',
                'rn.persentase_selesai'
            )
            ->where('rn.santri_id', $santriId)
            ->where('santri.status', 'aktif')
            ->orderBy('rn.id', 'desc');

        return $query->get();
    }
    public function getSetoranDanRekapNadhoman($id)
    {
        // Query setoran nadhoman
        $nadhoman = DB::table('nadhoman as n')
            ->leftJoin('santri', 'n.santri_id', '=', 'santri.id')
            ->leftJoin('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->leftJoin('kitab', 'n.kitab_id', '=', 'kitab.id')
            ->leftJoin('tahun_ajaran', 'n.tahun_ajaran_id', '=', 'tahun_ajaran.id')
            ->leftJoin('users as u', 'n.created_by', '=', 'u.id')
            ->select(
                'biodata.nama as santri_nama',
                'kitab.nama_kitab',
                'n.tanggal',
                'n.jenis_setoran',
                DB::raw("
                    CONCAT(
                        n.bait_mulai,
                        CASE
                            WHEN n.bait_selesai IS NOT NULL AND n.bait_selesai != n.bait_mulai 
                            THEN CONCAT('-', n.bait_selesai)
                            ELSE ''
                        END
                    ) AS bait
                "),
                'n.nilai',
                'n.catatan',
                'n.status',
                'u.name as pencatat'
            )
            ->where('n.santri_id', $id)
            ->whereNull('biodata.deleted_at')
            ->whereNull('santri.deleted_at')
            ->orderBy('n.id', 'desc')
            ->get();

        // Query rekap nadhoman
        $rekap = DB::table('rekap_nadhoman as rn')
            ->join('santri', 'rn.santri_id', '=', 'santri.id')
            ->leftJoin('domisili_santri as ds', 'santri.id', '=', 'ds.santri_id')
            ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->leftJoin('pendidikan as pd', 'santri.id', '=', 'pd.biodata_id')
            ->join('kitab', 'rn.kitab_id', '=', 'kitab.id')
            ->join('tahun_ajaran', 'rn.tahun_ajaran_id', '=', 'tahun_ajaran.id')
            ->select(
                'santri.nis',
                'biodata.nama as santri_nama',
                'kitab.nama_kitab',
                'rn.total_bait',
                'rn.persentase_selesai'
            )
            ->where('rn.santri_id', $id)
            ->orderBy('rn.id', 'desc')
            ->get();

        return [
            'nadhoman'        => $nadhoman,
            'rekap_nadhoman'  => $rekap
        ];
    }

    public function getAllRekap(Request $request)
    {
        $query = DB::table('rekap_nadhoman as rn')
            ->join('santri as s', 'rn.santri_id', '=', 's.id')
            ->leftJoin('domisili_santri as ds', 's.id', '=', 'ds.santri_id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftJoin('pendidikan as pd', 's.id', '=', 'pd.biodata_id')
            ->join('kitab', 'rn.kitab_id', '=', 'kitab.id')
            ->join('tahun_ajaran', 'rn.tahun_ajaran_id', '=', 'tahun_ajaran.id')
            ->select(
                's.id',
                's.nis',
                'b.nama as s_nama',
                'kitab.nama_kitab',
                'rn.total_bait',
                'rn.persentase_selesai'
            )
            ->groupBy(
                's.id',
                's.nis',
                'b.nama',
                'kitab.nama_kitab',
                'rn.total_bait',
                'rn.persentase_selesai',
                'rn.id'
            )
            ->orderBy('rn.id', 'desc');

        if (! $request->filled('tahun_ajaran_id')) {
            return $query;
        }

        if ($request->filled('tahun_ajaran_id')) {
            $query->where('rn.tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        return $query;
    }

    public function formatData($results)
    {
        return collect($results->items())->map(function ($item) {
            return [
                'santri_id'          => $item->id,
                'nis'                => $item->nis,
                'nama_santri'        => $item->s_nama,
                'nama_kitab'         => $item->nama_kitab,
                'total_bait'         => $item->total_bait,
                'persentase_selesai' => $item->persentase_selesai,
            ];
        });
    }
}
