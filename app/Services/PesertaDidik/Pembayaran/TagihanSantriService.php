<?php

namespace App\Services\PesertaDidik\Pembayaran;

use Illuminate\Support\Facades\DB;
use App\Models\Tagihan;
use App\Models\Potongan;
use App\Models\SantriPotongan;
use App\Models\TagihanSantri;

class TagihanSantriService
{
    public function generate(int $tagihanId, string $periode, array $filter = []): array
    {
        return DB::transaction(function () use ($tagihanId, $periode, $filter) {
            $tagihan = Tagihan::findOrFail($tagihanId);

            // cek apakah tagihan ini sudah pernah di-generate untuk periode tersebut
            $sudahAda = TagihanSantri::where('tagihan_id', $tagihanId)
                ->where('periode', $periode)
                ->exists();

            if ($sudahAda) {
                throw new \Exception("Tagihan {$tagihan->nama_tagihan} untuk periode {$periode} sudah pernah dibuat.");
            }

            // ambil semua santri aktif
            $santriQuery = DB::table('santri as s')
                ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
                ->leftJoin('khadam as kh', 'b.id', '=', 'kh.biodata_id')
                ->leftJoin('anak_pegawai as ap', fn($join) => $join->on('b.id', '=', 'ap.biodata_id')->where('ap.status', true))
                ->where('s.status', 'aktif')
                ->select(
                    's.id',
                    'b.jenis_kelamin',
                    's.biodata_id',
                    'k.no_kk',
                    'kh.status as khadam_status',
                    'ap.id as anak_pegawai_id'
                );

            if (!empty($filter['jenis_kelamin'])) {
                $santriQuery->where('b.jenis_kelamin', $filter['jenis_kelamin']);
            }

            $santriList = $santriQuery->get();

            // hitung jumlah santri per no_kk untuk cek bersaudara
            $kkCounts = $santriList->groupBy('no_kk')->map(fn($group) => count($group));

            // ambil potongan yang berelasi dengan tagihan (umum)
            $potonganList = Potongan::query()
                ->join('potongan_tagihan', 'potongan.id', '=', 'potongan_tagihan.potongan_id')
                ->where('potongan_tagihan.tagihan_id', $tagihanId)
                ->where('potongan.status', true)
                ->get(['potongan.*']);

            // ambil potongan personal santri yang terkait tagihan ini
            $potonganSantriList = DB::table('santri_potongan as sp')
                ->join('potongan as p', 'p.id', '=', 'sp.potongan_id')
                ->join('potongan_tagihan as pt', 'pt.potongan_id', '=', 'p.id')
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
            $upsertData = [];

            foreach ($santriList as $santri) {
                $nominalAkhir = $tagihan->nominal;

                // potongan umum (anak pegawai, bersaudara, khadam, dll)
                foreach ($potonganList as $potongan) {
                    if ($this->cekKelayakanPotonganOptimized($santri, $potongan, $kkCounts)) {
                        $nominalAkhir = $this->hitungPotongan($nominalAkhir, $potongan);
                    }
                }

                // potongan personal dari table santri_potongan (hanya untuk santri ini)
                $personalPotongan = $potonganSantriList->where('santri_id', $santri->id);
                foreach ($personalPotongan as $p) {
                    // validasi periode berlaku
                    $valid = true;
                    if ($p->berlaku_dari && $periode < $p->berlaku_dari) {
                        $valid = false;
                    }
                    if ($p->berlaku_sampai && $periode > $p->berlaku_sampai) {
                        $valid = false;
                    }

                    if ($valid) {
                        $nominalAkhir = $this->hitungPotongan($nominalAkhir, $p);
                    }
                }

                $totalSantri++;

                $upsertData[] = [
                    'tagihan_id' => $tagihan->id,
                    'santri_id'  => $santri->id,
                    'periode'    => $periode,
                    'nominal'    => $nominalAkhir,
                    'sisa'       => $nominalAkhir,
                    'tanggal_jatuh_tempo' => $tagihan->jatuh_tempo,
                    'status'     => 'pending',
                ];
            }

            // bulk upsert
            TagihanSantri::upsert(
                $upsertData,
                ['tagihan_id', 'santri_id', 'periode'],
                ['nominal', 'sisa', 'tanggal_jatuh_tempo', 'status']
            );

            return [
                'success'      => true,
                'total_santri' => $totalSantri,
            ];
        });
    }


    /**
     * cekKelayakanPotongan tanpa N+1 query
     */
    private function cekKelayakanPotonganOptimized($santri, $potongan, $kkCounts): bool
    {
        $nama = strtolower($potongan->nama);

        if ($nama === 'anak pegawai') {
            return !empty($santri->anak_pegawai_id);
        }

        if ($nama === 'bersaudara') {
            return !empty($santri->no_kk) && ($kkCounts[$santri->no_kk] ?? 0) > 1;
        }

        if ($nama === 'khadam') {
            return !empty($santri->khadam_status);
        }

        // potongan umum/default berlaku untuk semua santri
        return true;
    }

    private function hitungPotongan(float $nominal, $potongan): float
    {
        if ($potongan->jenis == 'persentase') {
            return $nominal - ($nominal * ($potongan->nilai / 100));
        }
        return max(0, $nominal - $potongan->nilai);
    }

    public function generateManual(int $tagihanId, string $periode, array $santriIds): array
    {
        return DB::transaction(function () use ($tagihanId, $periode, $santriIds) {
            $tagihan = Tagihan::findOrFail($tagihanId);

            // cek apakah ada santri yang sudah pernah mendapat tagihan ini untuk periode tsb
            $sudahAda = TagihanSantri::where('tagihan_id', $tagihanId)
                ->where('periode', $periode)
                ->whereIn('santri_id', $santriIds)
                ->pluck('santri_id')
                ->toArray();

            if (!empty($sudahAda)) {
                throw new \Exception("Beberapa santri sudah memiliki tagihan {$tagihan->nama_tagihan} untuk periode {$periode}: " . implode(',', $sudahAda));
            }

            // ambil data santri
            $santriList = DB::table('santri as s')
                ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
                ->leftJoin('khadam as kh', 'b.id', '=', 'kh.biodata_id')
                ->leftJoin('anak_pegawai as ap', fn($join) => $join->on('b.id', '=', 'ap.biodata_id')->where('ap.status', true))
                ->whereIn('s.id', $santriIds)
                ->where('s.status', 'aktif')
                ->select('s.id', 'b.jenis_kelamin', 's.biodata_id', 'k.no_kk', 'kh.status as khadam_status', 'ap.id as anak_pegawai_id')
                ->get();

            if ($santriList->isEmpty()) {
                throw new \Exception("Tidak ada santri valid untuk dibuatkan tagihan.");
            }

            // hitung jumlah santri per no_kk untuk cek bersaudara
            $kkCounts = $santriList->groupBy('no_kk')->map(fn($group) => count($group));

            // ambil potongan umum (relasi ke tagihan)
            $potonganList = Potongan::query()
                ->join('potongan_tagihan', 'potongan.id', '=', 'potongan_tagihan.potongan_id')
                ->where('potongan_tagihan.tagihan_id', $tagihanId)
                ->where('potongan.status', true)
                ->get(['potongan.*']);

            // ambil potongan personal santri
            $potonganSantriList = DB::table('santri_potongan as sp')
                ->join('potongan as p', 'p.id', '=', 'sp.potongan_id')
                ->where('sp.status', true)
                ->where('p.status', true)
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
                $nominalAkhir = $tagihan->nominal;

                // potongan umum
                foreach ($potonganList as $potongan) {
                    if ($this->cekKelayakanPotonganOptimized($santri, $potongan, $kkCounts)) {
                        $nominalAkhir = $this->hitungPotongan($nominalAkhir, $potongan);
                    }
                }

                // potongan personal
                $personalPotongan = $potonganSantriList->where('santri_id', $santri->id);
                foreach ($personalPotongan as $p) {
                    $valid = true;
                    if ($p->berlaku_dari && $periode < $p->berlaku_dari) {
                        $valid = false;
                    }
                    if ($p->berlaku_sampai && $periode > $p->berlaku_sampai) {
                        $valid = false;
                    }

                    if ($valid) {
                        $nominalAkhir = $this->hitungPotongan($nominalAkhir, $p);
                    }
                }

                $totalSantri++;

                $insertData[] = [
                    'tagihan_id' => $tagihan->id,
                    'santri_id'  => $santri->id,
                    'periode'    => $periode,
                    'nominal'    => $nominalAkhir,
                    'sisa'       => $nominalAkhir,
                    'tanggal_jatuh_tempo' => $tagihan->jatuh_tempo,
                    'status'     => 'pending',
                ];
            }

            // insert
            TagihanSantri::insert($insertData);

            return [
                'success'      => true,
                'total_santri' => $totalSantri,
            ];
        });
    }
}
