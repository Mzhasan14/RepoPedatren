<?php

namespace App\Services\PesertaDidik\Fitur;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class NadhomanService
{
    public function setoranNadhoman(array $data)
    {
        DB::beginTransaction();

        try {
            // Insert setoran
            $setoranId = DB::table('nadhoman')->insertGetId([
                'santri_id' => $data['santri_id'],
                'tahun_ajaran_id' => $data['tahun_ajaran_id'],
                'kitab_id' => $data['kitab_id'],
                'tanggal' => $data['tanggal'],
                'jenis_setoran' => $data['jenis_setoran'],
                'bait_mulai' => $data['bait_mulai'] ?? null,
                'bait_selesai' => $data['bait_selesai'] ?? null,
                'nilai' => $data['nilai'],
                'catatan' => $data['catatan'] ?? null,
                'status' => $data['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update Rekap hanya jika tuntas & baru
            if ($data['status'] === 'tuntas' && $data['jenis_setoran'] === 'baru') {
                $total = DB::table('nadhoman')
                    ->where('santri_id', $data['santri_id'])
                    ->where('tahun_ajaran_id', $data['tahun_ajaran_id'])
                    ->where('kitab_id', $data['kitab_id'])
                    ->where('status', 'tuntas')
                    ->where('jenis_setoran', 'baru')
                    ->count();

                DB::table('rekap_nadhoman')->updateOrInsert(
                    [
                        'santri_id' => $data['santri_id'],
                        'tahun_ajaran_id' => $data['tahun_ajaran_id'],
                        'kitab_id' => $data['kitab_id'],
                    ],
                    [
                        'total_setoran' => $total,
                        'updated_at' => now(),
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

    public function listSetoran(array $filters)
    {
        $query = DB::table('nadhoman')
            ->leftjoin('santri', 'nadhoman.santri_id', '=', 'santri.id')
            ->leftjoin('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->leftjoin('tahun_ajaran', 'nadhoman.tahun_ajaran_id', '=', 'tahun_ajaran.id')
            ->select('biodata.nama as santri_nama', 'tahun_ajaran.tahun_ajaran as tahun_ajaran_nama')
            ->where('nadhoman.santri_id', $filters['id'] ?? null)
            ->where('santri.status', 'aktif')
            ->orderBy('nadhoman.id', 'desc');

        if (!empty($filters['tahun_ajaran_id'])) {
            $query->where('tahun_ajaran_id', $filters['tahun_ajaran_id']);
        }
        if (!empty($filters['santri_id'])) {
            $query->where('santri_id', $filters['santri_id']);
        }
        if (!empty($filters['kitab_id'])) {
            $query->where('kitab_id', $filters['kitab_id']);
        }

        return $query->get();
    }

    public function listRekap(array $filters)
    {
        $query = DB::table('rekap_nadhoman')
            ->with(['santri', 'kitab', 'tahunAjaran'])
            ->orderBy('total_setoran', 'desc');

        if (!empty($filters['tahun_ajaran_id'])) {
            $query->where('tahun_ajaran_id', $filters['tahun_ajaran_id']);
        }
        if (!empty($filters['santri_id'])) {
            $query->where('santri_id', $filters['santri_id']);
        }
        if (!empty($filters['kitab_id'])) {
            $query->where('kitab_id', $filters['kitab_id']);
        }

        return $query->get();
    }
}
