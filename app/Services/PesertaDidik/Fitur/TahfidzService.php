<?php

namespace App\Services\PesertaDidik\Fitur;

use Exception;
use App\Models\Tahfidz;
use App\Models\TahunAjaran;
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
            $tahunAjaranId = TahunAjaran::where('status', true)
                ->orderByDesc('id')
                ->value('id');

            // --- CEK SURAT TERAKHIR ---
            if ($data['jenis_setoran'] === 'baru') {
                $suratTerakhir = DB::table('tahfidz')
                    ->where('santri_id', $data['santri_id'])
                    ->where('jenis_setoran', 'baru')
                    ->orderByDesc('id')
                    ->first();

                if ($suratTerakhir && $suratTerakhir->status !== 'tuntas') {
                    throw new Exception(
                        "Surat terakhir belum tuntas, harap selesaikan terlebih dahulu."
                    );
                }
            }

            // --- INSERT SETORAN ---
            $setoranId = DB::table('tahfidz')->insertGetId([
                'santri_id'       => $data['santri_id'],
                'tahun_ajaran_id' => $tahunAjaranId,
                'tanggal'         => $data['tanggal'],
                'jenis_setoran'   => $data['jenis_setoran'],
                'surat'           => $data['jenis_setoran'] === 'baru' ? $data['surat'] : null,
                'ayat_mulai'      => $data['jenis_setoran'] === 'baru' ? $data['ayat_mulai'] : null,
                'ayat_selesai'    => $data['jenis_setoran'] === 'baru' ? $data['ayat_selesai'] : null,
                'juz_mulai'       => $data['jenis_setoran'] === 'murojaah' ? $data['juz_mulai'] : null,
                'juz_selesai'     => $data['jenis_setoran'] === 'murojaah' ? $data['juz_selesai'] : null,
                'nilai'           => $data['nilai'],
                'catatan'         => $data['catatan'] ?? null,
                'status'          => $data['jenis_setoran'] === 'murojaah' ? null : $data['status'],
                'created_by'      => Auth::id(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // --- UPDATE REKAP JIKA SETORAN BARU ---
            if ($data['jenis_setoran'] === 'baru') {
                $suratTuntas = DB::table('tahfidz')
                    ->where('santri_id', $data['santri_id'])
                    ->where('jenis_setoran', 'baru')
                    ->where('status', 'tuntas')
                    ->distinct()
                    ->pluck('surat');

                $totalSurat     = $suratTuntas->count();
                $persentase     = ($totalSurat / 114) * 100;
                $suratTersisa   = 114 - $totalSurat;
                $sisaPersentase = 100 - $persentase;

                // $jumlahSetoran = DB::table('tahfidz')
                //     ->where('santri_id', $data['santri_id'])
                //     ->where('jenis_setoran', 'baru')
                //     ->whereIn('surat', $suratTuntas)
                //     ->count();

                $jumlahSetoran = DB::table('tahfidz')
                    ->where('santri_id', $data['santri_id'])
                    ->where('jenis_setoran', 'baru')
                    ->count();

                $rataRataNilai = DB::table('tahfidz')
                    ->where('santri_id', $data['santri_id'])
                    ->where('jenis_setoran', 'baru')
                    ->whereIn('surat', $suratTuntas)
                    ->avg('nilai');

                $tanggalMulai = DB::table('tahfidz')
                    ->where('santri_id', $data['santri_id'])
                    ->where('jenis_setoran', 'baru')
                    ->min('tanggal');

                $tanggalSelesai = $totalSurat >= 114 ? now() : null;

                DB::table('rekap_tahfidz')->updateOrInsert(
                    [
                        'santri_id' => $data['santri_id'],
                    ],
                    [
                        'total_surat'       => $totalSurat,
                        'persentase_khatam' => round($persentase, 2),
                        'surat_tersisa'     => $suratTersisa,
                        'sisa_persentase'   => round($sisaPersentase, 2),
                        'jumlah_setoran'    => $jumlahSetoran,
                        'rata_rata_nilai'   => round($rataRataNilai, 2),
                        'tanggal_mulai'     => $tanggalMulai,
                        'tanggal_selesai'   => $tanggalSelesai,
                        'updated_at'        => now(),
                        'created_by'        => Auth::id(),
                        'updated_by'        => Auth::id(),
                    ]
                );
            }

            DB::commit();
            return $setoranId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal simpan setoran: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Lempar pesan sopan ke frontend
            throw new Exception($e->getMessage());
        }
    }


    public function getSetoranDanRekap($id)
    {
        $tahunAjaranId = TahunAjaran::where('status', true)
            ->orderByDesc('id')
            ->value('id');
        // Query setoran tahfidz
        $tahfidz = DB::table('tahfidz as t')
            ->leftJoin('santri', 't.santri_id', '=', 'santri.id')
            ->leftJoin('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->leftJoin('tahun_ajaran', 't.tahun_ajaran_id', '=', 'tahun_ajaran.id')
            ->leftJoin('users as u', 't.created_by', '=', 'u.id')
            ->select(
                'biodata.nama as santri_nama',
                't.tanggal',
                't.jenis_setoran',
                'tahun_ajaran.tahun_ajaran',
                DB::raw("
                CASE 
                    WHEN t.jenis_setoran = 'baru' THEN
                        CONCAT(
                            t.surat,
                            ' ',
                            t.ayat_mulai,
                            CASE
                                WHEN t.ayat_selesai IS NOT NULL AND t.ayat_selesai != t.ayat_mulai THEN CONCAT('-', t.ayat_selesai)
                                ELSE ''
                            END
                        )
                    WHEN t.jenis_setoran = 'murojaah' THEN
                        CONCAT('Juz ', t.juz_mulai, 
                            CASE
                                WHEN t.juz_selesai IS NOT NULL AND t.juz_selesai != t.juz_mulai THEN CONCAT('-', t.juz_selesai)
                                ELSE ''
                            END
                        )
                    ELSE ''
                END AS keterangan_setoran
            "),
                't.nilai',
                't.catatan',
                't.status',
                'u.name as pencatat'
            )

            ->where('t.santri_id', $id)
            ->whereNull('biodata.deleted_at')
            ->whereNull('santri.deleted_at')
            ->orderBy('t.id', 'desc')
            ->get();

        // Query rekap tahfidz (hanya untuk setoran 'baru')
        $rekap = DB::table('rekap_tahfidz as rt')
            ->join('santri', 'rt.santri_id', '=', 'santri.id')
            ->leftJoin('domisili_santri as ds', 'santri.id', '=', 'ds.santri_id')
            ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->leftJoin('pendidikan as pd', 'santri.id', '=', 'pd.biodata_id')
            ->select(
                'santri.nis',
                'biodata.nama as santri_nama',
                'rt.total_surat',
                'rt.persentase_khatam',
                'rt.surat_tersisa',
                'rt.sisa_persentase',
                'rt.jumlah_setoran',
                'rt.rata_rata_nilai',
                'rt.tanggal_mulai',
                'rt.tanggal_selesai'
            )
            ->where('rt.santri_id', $id)
            ->orderBy('rt.id', 'desc')
            ->first();

        return [
            'tahfidz' => $tahfidz,
            'rekap_tahfidz' => $rekap
        ];
    }

    public function getAllRekap(Request $request)
    {
        $query = DB::table('santri as s')
            ->leftJoin('domisili_santri as ds', 's.id', '=', 'ds.santri_id')
            ->leftJoin('rekap_tahfidz as rt', 'rt.santri_id', '=', 's.id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftJoin('pendidikan as pd', 's.id', '=', 'pd.biodata_id')
            ->select(
                's.id',
                's.nis',
                'b.nama as s_nama',
                'rt.total_surat',
                'rt.persentase_khatam',
                'rt.surat_tersisa',
                'rt.sisa_persentase',
                'rt.jumlah_setoran',
                'rt.rata_rata_nilai',
                'rt.tanggal_mulai',
                'rt.tanggal_selesai',
            )
            ->groupBy(
                's.id',
                's.nis',
                'b.nama',
                'rt.total_surat',
                'rt.persentase_khatam',
                'rt.surat_tersisa',
                'rt.sisa_persentase',
                'rt.jumlah_setoran',
                'rt.rata_rata_nilai',
                'rt.tanggal_mulai',
                'rt.tanggal_selesai',
                'rt.id'
            )
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
                'surat_tersisa'     => $item->surat_tersisa,
                'sisa_persentase'   => $item->sisa_persentase,
                'jumlah_setoran'    => $item->jumlah_setoran,
                'rata_rata_nilai'   => $item->rata_rata_nilai,
                'tanggal_mulai'     => $item->tanggal_mulai,
                'tanggal_selesai'   => $item->tanggal_selesai,
            ];
        });
    }
}
