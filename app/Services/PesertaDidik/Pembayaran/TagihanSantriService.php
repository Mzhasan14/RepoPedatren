<?php

namespace App\Services\PesertaDidik\Pembayaran;

use Carbon\Carbon;
use App\Models\Tagihan;
use App\Models\Potongan;
use App\Models\TagihanSantri;
use App\Models\SantriPotongan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TagihanSantriService
{
    public function generate(int $tagihanId, array $filter = []): array
    {
        return DB::transaction(function () use ($tagihanId, $filter) {
            // 1. Ambil data tagihan
            $tagihan = DB::table('tagihan')->where('id', $tagihanId)->first();
            if (!$tagihan) {
                throw new \Exception("Tagihan tidak ditemukan.");
            }

            // 2. Ambil daftar santri aktif sesuai filter
            $santriQuery = DB::table('santri as s')
                ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
                ->where('s.status', 'aktif')
                ->select('s.id', 'b.jenis_kelamin', 's.biodata_id');

            if (!empty($filter['santri_ids']) && empty($filter['all'])) {
                $santriQuery->whereIn('s.id', $filter['santri_ids']);
            }

            if (!empty($filter['jenis_kelamin'])) {
                $santriQuery->where('b.jenis_kelamin', $filter['jenis_kelamin']);
            }

            $santriList = $santriQuery->get();
            if ($santriList->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada santri sesuai filter.',
                    'total_santri' => 0
                ];
            }

            // 3. Ambil daftar santri yang sudah punya tagihan ini (untuk validasi)
            $existingSantriIds = DB::table('tagihan_santri')
                ->where('tagihan_id', $tagihanId)
                ->pluck('santri_id')
                ->toArray();

            // 4. Ambil data potongan
            $potonganList = DB::table('potongan')
                ->join('potongan_tagihan', 'potongan_tagihan.potongan_id', '=', 'potongan.id')
                ->where('potongan_tagihan.tagihan_id', $tagihanId)
                ->where('potongan.status', true)
                ->select('potongan.*')
                ->get();

            // daftar tambahan
            $anakPegawaiList = DB::table('anak_pegawai')->where('status', true)->pluck('biodata_id')->toArray();
            $khadamList = DB::table('khadam')->pluck('biodata_id')->toArray();

            $santriPotonganList = DB::table('santri_potongan')
                ->select('santri_id', 'potongan_id')
                ->get()
                ->groupBy('santri_id');

            // keluarga per KK
            $keluargaList = DB::table('keluarga as k')
                ->join('santri as s', 's.biodata_id', '=', 'k.id_biodata')
                ->where('s.status', 'aktif')
                ->whereNull('s.deleted_at')
                ->select('s.id as santri_id', 'k.no_kk')
                ->get();

            $kelompokKK = $keluargaList->groupBy('no_kk')
                ->filter(fn($group) => $group->count() > 1)
                ->flatMap(fn($group) => $group->pluck('santri_id'))
                ->toArray();

            // ============================
            //  🔍 Proses generate tagihan
            // ============================
            $insertData = [];
            $totalSantri = 0;

            foreach ($santriList as $santri) {
                // 🚫 Skip jika santri sudah punya tagihan ini
                if (in_array($santri->id, $existingSantriIds)) {
                    continue;
                }

                $totalSantri++;
                $nominalAwal = $tagihan->nominal;
                $totalPotongan = 0;

                foreach ($potonganList as $potongan) {
                    $eligible = false;

                    switch ($potongan->kategori) {
                        case 'anak_pegawai':
                            $eligible = in_array($santri->biodata_id, $anakPegawaiList);
                            break;
                            
                        case 'bersaudara':
                            $eligible = in_array($santri->id, $kelompokKK);
                            break;

                        case 'khadam':
                            $eligible = in_array($santri->biodata_id, $khadamList);
                            break;

                        case 'umum':
                            $eligible = $santriPotonganList->has($santri->id)
                                && $santriPotonganList[$santri->id]->contains('potongan_id', $potongan->id);
                            break;
                    }

                    if ($eligible) {
                        $potonganValue = $potongan->jenis === 'persentase'
                            ? $nominalAwal * ($potongan->nilai / 100)
                            : $potongan->nilai;

                        $totalPotongan += $potonganValue;
                    }
                }

                $totalTagihan = max(0, $nominalAwal - $totalPotongan);

                $insertData[] = [
                    'tagihan_id'          => $tagihanId,
                    'santri_id'           => $santri->id,
                    'total_potongan'      => $totalPotongan,
                    'total_tagihan'       => $totalTagihan,
                    'status'              => 'pending',
                    'tanggal_jatuh_tempo' => $tagihan->jatuh_tempo,
                    'created_at'          => Carbon::now(),
                    'updated_at'          => Carbon::now(),
                ];
            }

            // Insert batch (jika ada data baru)
            if (!empty($insertData)) {
                DB::table('tagihan_santri')->insert($insertData);
            }

            return [
                'success' => true,
                'message' => 'Tagihan santri berhasil digenerate.',
                'tagihan_id' => $tagihanId,
                'total_santri' => $totalSantri,
                'total_tagihan_santri' => count($insertData),
            ];
        });
    }




    /**
     * cekKelayakanPotongan tanpa N+1
     */
    // private function cekKelayakanPotonganOptimized($santri, $potongan, $kkCounts): bool
    // {
    //     $nama = strtolower($potongan->nama);

    //     if ($nama === 'anak pegawai') {
    //         return !empty($santri->anak_pegawai_id);
    //     }

    //     if ($nama === 'bersaudara') {
    //         return !empty($santri->no_kk) && ($kkCounts[$santri->no_kk] ?? 0) > 1;
    //     }

    //     if ($nama === 'khadam') {
    //         return !empty($santri->khadam_status);
    //     }

    //     return true;
    // }

    // private function hitungPotongan(float $nominal, $potongan): float
    // {
    //     if ($potongan->jenis == 'persentase') {
    //         return $nominal - ($nominal * ($potongan->nilai / 100));
    //     }
    //     return max(0, $nominal - $potongan->nilai);
    // }


    public function generateManual(int $tagihanId, string $periode, array $santriIds): array
    {
        return DB::transaction(function () use ($tagihanId, $periode, $santriIds) {
            $tagihan = Tagihan::findOrFail($tagihanId);

            // ✅ cek apakah ada santri yang sudah pernah mendapat tagihan untuk periode ini
            $sudahAda = TagihanSantri::where('tagihan_id', $tagihanId)
                ->where('periode', $periode)
                ->whereIn('santri_id', $santriIds)
                ->pluck('santri_id')
                ->toArray();

            if (!empty($sudahAda)) {
                throw new \Exception(
                    "Beberapa santri sudah memiliki tagihan {$tagihan->nama_tagihan} untuk periode {$periode}: "
                        . implode(',', $sudahAda)
                );
            }

            // ✅ ambil data santri aktif
            $santriList = DB::table('santri as s')
                ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
                ->leftJoin('khadam as kh', 'b.id', '=', 'kh.biodata_id')
                ->leftJoin('anak_pegawai as ap', function ($join) {
                    $join->on('b.id', '=', 'ap.biodata_id')->where('ap.status', true);
                })
                ->whereIn('s.id', $santriIds)
                ->where('s.status', 'aktif')
                ->select(
                    's.id',
                    'b.jenis_kelamin',
                    's.biodata_id',
                    'k.no_kk',
                    'kh.status as khadam_status',
                    'ap.id as anak_pegawai_id'
                )
                ->get();

            if ($santriList->isEmpty()) {
                throw new \Exception("Tidak ada santri valid untuk dibuatkan tagihan.");
            }

            // hitung jumlah santri per KK (untuk potongan bersaudara)
            $kkCounts = $santriList->groupBy('no_kk')->map(fn($g) => $g->count());

            // ✅ potongan umum
            $potonganList = Potongan::query()
                ->join('potongan_tagihan as pt', 'pt.potongan_id', '=', 'potongan.id')
                ->where('pt.tagihan_id', $tagihanId)
                ->where('potongan.status', true)
                ->get(['potongan.*']);

            // ✅ potongan personal (terikat santri)
            $potonganSantriList = DB::table('santri_potongan as sp')
                ->join('potongan as p', 'p.id', '=', 'sp.potongan_id')
                ->join('potongan_tagihan as pt', 'pt.potongan_id', '=', 'p.id') // filter hanya yg relevan dgn tagihan
                ->where('sp.status', true)
                ->where('p.status', true)
                ->where('pt.tagihan_id', $tagihanId)
                ->get([
                    'sp.santri_id',
                    'sp.berlaku_dari',
                    'sp.berlaku_sampai',
                    'p.id as potongan_id',
                    'p.nama',
                    'p.jenis',
                    'p.nilai'
                ]);

            $totalSantri = 0;
            $insertData = [];

            foreach ($santriList as $santri) {
                $nominalAwal = $tagihan->nominal;
                $totalPotongan = 0;
                $nominalAkhir = $nominalAwal;

                // === potongan umum ===
                foreach ($potonganList as $potongan) {
                    if ($this->cekKelayakanPotonganOptimized($santri, $potongan, $kkCounts)) {
                        $sesudahPotongan = $this->hitungPotongan($nominalAkhir, $potongan);
                        $totalPotongan += ($nominalAkhir - $sesudahPotongan);
                        $nominalAkhir = $sesudahPotongan;
                    }
                }

                // === potongan personal ===
                foreach ($potonganSantriList->where('santri_id', $santri->id) as $p) {
                    $valid = true;

                    // ✅ konversi periode ke awal bulan untuk dibandingkan dengan date
                    $periodeDate = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();

                    if ($p->berlaku_dari && $periodeDate->lt(Carbon::parse($p->berlaku_dari))) {
                        $valid = false;
                    }
                    if ($p->berlaku_sampai && $periodeDate->gt(Carbon::parse($p->berlaku_sampai))) {
                        $valid = false;
                    }

                    if ($valid) {
                        $sesudahPotongan = $this->hitungPotongan($nominalAkhir, $p);
                        $totalPotongan += ($nominalAkhir - $sesudahPotongan);
                        $nominalAkhir = $sesudahPotongan;
                    }
                }

                $totalSantri++;

                $insertData[] = [
                    'tagihan_id'          => $tagihan->id,
                    'santri_id'           => $santri->id,
                    'periode'             => $periode,
                    'nominal'             => $nominalAwal,
                    'total_potongan'      => $totalPotongan,
                    'total_tagihan'       => $nominalAkhir,
                    'tanggal_jatuh_tempo' => $tagihan->jatuh_tempo,
                    'status'              => 'pending',
                ];
            }

            // ✅ batch insert
            TagihanSantri::insert($insertData);

            return [
                'success'      => true,
                'total_santri' => $totalSantri,
            ];
        });
    }
}
