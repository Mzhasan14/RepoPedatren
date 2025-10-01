<?php

namespace App\Services\PesertaDidik\Fitur;

use App\Models\Nadhoman;
use Exception;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class NadhomanService
{
    public function setoranNadhoman(array $data)
    {
        DB::beginTransaction();

        try {
            $tahunAjaranId = TahunAjaran::where('status', true)
                ->orderByDesc('id')
                ->value('id');

            // --- VALIDASI SETORAN BARU ---
            if ($data['jenis_setoran'] === 'baru') {
                // 1. Cek apakah kitab ini sudah tuntas
                $kitabSudahTuntas = DB::table('nadhoman')
                    ->where('santri_id', $data['santri_id'])
                    ->where('kitab_id', $data['kitab_id'])
                    ->where('jenis_setoran', 'baru')
                    ->where('status', 'tuntas')
                    ->exists();

                if ($kitabSudahTuntas) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => "Kitab ini sudah selesai dituntaskan, tidak bisa setor ulang.",
                        'data'    => null,
                    ];
                }

                // 2. Cek apakah bait sudah pernah disetorkan (overlap)
                $sudahAda = DB::table('nadhoman')
                    ->where('santri_id', $data['santri_id'])
                    ->where('kitab_id', $data['kitab_id'])
                    ->where('jenis_setoran', 'baru')
                    ->where(function ($q) use ($data) {
                        $q->whereBetween('bait_mulai', [$data['bait_mulai'], $data['bait_selesai']])
                            ->orWhereBetween('bait_selesai', [$data['bait_mulai'], $data['bait_selesai']])
                            ->orWhere(function ($sub) use ($data) {
                                $sub->where('bait_mulai', '<=', $data['bait_mulai'])
                                    ->where('bait_selesai', '>=', $data['bait_selesai']);
                            });
                    })
                    ->exists();

                if ($sudahAda) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => "Bait {$data['bait_mulai']} - {$data['bait_selesai']} sudah pernah disetorkan, tidak bisa disetorkan lagi.",
                        'data'    => null,
                    ];
                }
            }

            // --- INSERT SETORAN ---
            $setoranId = DB::table('nadhoman')->insertGetId([
                'santri_id'       => $data['santri_id'],
                'kitab_id'        => $data['kitab_id'],
                'tahun_ajaran_id' => $tahunAjaranId,
                'tanggal'         => $data['tanggal'],
                'jenis_setoran'   => $data['jenis_setoran'],
                'bait_mulai'      => $data['bait_mulai'],
                'bait_selesai'    => $data['bait_selesai'],
                'nilai'           => $data['nilai'],
                'catatan'         => $data['catatan'] ?? null,
                'status'          => $data['status'], // sementara diinput user
                'created_by'      => Auth::id(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $totalBaitSelesai = DB::table('nadhoman')
                ->where('santri_id', $data['santri_id'])
                ->where('kitab_id', $data['kitab_id'])
                ->where('jenis_setoran', 'baru')
                ->sum(DB::raw('(bait_selesai - bait_mulai + 1)'));

            $totalBaitKitab = DB::table('kitab')
                ->where('id', $data['kitab_id'])
                ->value('total_bait');

            $persentase = 0;
            if ($totalBaitKitab > 0) {
                $persentase = ($totalBaitSelesai / $totalBaitKitab) * 100;
            }

            $statusSetoran = $data['status'];
            if ($persentase >= 100) {
                $statusSetoran = 'tuntas';
            }

            DB::table('rekap_nadhoman')->updateOrInsert(
                [
                    'santri_id' => $data['santri_id'],
                    'kitab_id'  => $data['kitab_id'],
                ],
                [
                    'total_bait'         => $totalBaitSelesai,
                    'persentase_selesai' => round($persentase, 2),
                    'updated_at'         => now(),
                    'created_by'         => Auth::id(),
                ]
            );

            DB::table('nadhoman')->where('id', $setoranId)->update([
                'status' => $statusSetoran,
                'updated_at' => now(),
            ]);

            DB::commit();

            // --- LOG AKTIVITAS ---
            $santri = DB::table('santri')
                ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
                ->select('santri.nis', 'biodata.nama')
                ->where('santri.id', $data['santri_id'])
                ->first();

            activity('nadhoman')
                ->causedBy(Auth::user())
                ->performedOn(new Nadhoman(['id' => $setoranId]))
                ->withProperties([
                    'santri_id'  => $data['santri_id'],
                    'nis'        => $santri->nis ?? null,
                    'nama'       => $santri->nama ?? null,
                    'kitab_id'   => $data['kitab_id'],
                    'ip'         => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->event('create')
                ->log("Setoran nadhoman berhasil ditambahkan");

            return [
                'success' => true,
                'message' => 'Setoran nadhoman berhasil disimpan.',
                'data'    => ['id' => $setoranId, 'status' => $statusSetoran, 'persentase' => round($persentase, 2)],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal simpan setoran nadhoman: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi.',
                'data'    => null,
            ];
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
                'tahun_ajaran.tahun_ajaran',
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

        $rekap = DB::table('rekap_nadhoman as rn')
            ->join('santri', 'rn.santri_id', '=', 'santri.id')
            ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->join('kitab', 'rn.kitab_id', '=', 'kitab.id')
            ->select(
                'santri.nis',
                'biodata.nama as santri_nama',
                'kitab.nama_kitab',
                'rn.total_bait',
                'rn.persentase_selesai'
            )
            ->where('rn.santri_id', $id)
            ->groupBy(
                'santri.nis',
                'biodata.nama',
                'kitab.nama_kitab',
                'rn.total_bait',
                'rn.persentase_selesai'
            )
            ->orderBy('rn.id', 'desc')
            ->get();

        return [
            'nadhoman'        => $nadhoman,
            'rekap_nadhoman'  => $rekap
        ];
    }

    public function getAllRekap(Request $request)
    {
        $query = DB::table('santri as s')
            ->leftJoin('rekap_nadhoman as rn', 'rn.santri_id', '=', 's.id')
            ->leftJoin('domisili_santri as ds', 's.id', '=', 'ds.santri_id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftJoin('pendidikan as pd', 's.id', '=', 'pd.biodata_id')
            ->leftJoin('kitab', 'rn.kitab_id', '=', 'kitab.id')
            ->select(
                's.id',
                's.nis',
                'b.nama as s_nama',
                DB::raw("GROUP_CONCAT(DISTINCT kitab.nama_kitab ORDER BY kitab.nama_kitab SEPARATOR ', ') as daftar_kitab")
            )
            ->groupBy(
                's.id',
                's.nis',
                'b.nama'
            )
            ->orderBy('s.id', 'desc');

        return $query;
    }

    public function formatData($results)
    {
        return collect($results->items())->map(function ($item) {
            return [
                'santri_id'          => $item->id,
                'nis'                => $item->nis,
                'nama_santri'        => $item->s_nama,
                'nama_kitab'         => $item->daftar_kitab,
            ];
        });
    }
}
