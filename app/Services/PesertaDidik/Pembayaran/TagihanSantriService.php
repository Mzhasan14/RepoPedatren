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

        // ambil semua santri aktif
        $santriQuery = DB::table('santri as s')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
            ->leftJoin('khadam as kh', 'b.id', '=', 'kh.biodata_id')
            ->leftJoin('anak_pegawai as ap', fn($join) => $join->on('b.id', '=', 'ap.biodata_id')->where('ap.status', true))
            ->where('s.status', 'aktif')
            ->select('s.id', 'b.jenis_kelamin', 's.biodata_id', 'k.no_kk', 'kh.status as khadam_status', 'ap.id as anak_pegawai_id');

        if (!empty($filter['jenis_kelamin'])) {
            $santriQuery->where('b.jenis_kelamin', $filter['jenis_kelamin']);
        }

        $santriList = $santriQuery->get();

        // hitung jumlah santri per no_kk untuk cek bersaudara
        $kkCounts = $santriList->groupBy('no_kk')->map(fn($group) => count($group));

        // ambil potongan yang berelasi dengan tagihan
        $potonganList = Potongan::query()
            ->join('potongan_tagihan', 'potongan.id', '=', 'potongan_tagihan.potongan_id')
            ->where('potongan_tagihan.tagihan_id', $tagihanId)
            ->where('potongan.status', true)
            ->get(['potongan.*']);

        $totalSantri = 0;
        $upsertData = [];

        foreach ($santriList as $santri) {
            $nominalAkhir = $tagihan->nominal;

            foreach ($potonganList as $potongan) {
                if ($this->cekKelayakanPotonganOptimized($santri, $potongan, $kkCounts)) {
                    $nominalAkhir = $this->hitungPotongan($nominalAkhir, $potongan);
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

}
