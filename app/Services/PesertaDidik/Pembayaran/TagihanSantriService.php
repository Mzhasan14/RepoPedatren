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

            // --- pilih santri aktif
            $santriQuery = DB::table('santri as s')
                ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                ->leftjoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
                ->leftjoin('khadam as kh', 'b.id', '=', 'kh.biodata_id')
                ->leftjoin(
                    'anak_pegawai as ap',
                    fn($join) =>
                    $join->on('b.id', '=', 'ap.biodata_id')
                        ->where('ap.status', true)
                )
                ->select('s.id', 'b.jenis_kelamin', 's.biodata_id', 'k.no_kk', 'kh.status as khadam_status', 'ap.id as anak_pegawai_id')
                ->where('s.status', 'aktif');

            if (!empty($filter['jenis_kelamin'])) {
                $santriQuery->where('b.jenis_kelamin', $filter['jenis_kelamin']);
            }

            $santriList = $santriQuery->get();

            // --- ambil potongan yang berelasi dengan tagihan
            $potonganList = Potongan::query()
                ->join('potongan_tagihan', 'potongan.id', '=', 'potongan_tagihan.potongan_id')
                ->where('potongan_tagihan.tagihan_id', $tagihanId)
                ->where('potongan.status', true)
                ->get(['potongan.*']);

            // --- inisialisasi counter
            $totalSantriBiasa = 0;
            $totalAnakPegawai = 0;
            $totalBersaudara = 0;
            $totalKhadam = 0;
            $totalPutra = 0;
            $totalPutri = 0;

            foreach ($santriList as $santri) {
                $nominalAkhir = $tagihan->nominal;
                $terefek = false;

                foreach ($potonganList as $potongan) {
                    if ($this->cekKelayakanPotongan($santri, $potongan)) {
                        $nominalAkhir = $this->hitungPotongan($nominalAkhir, $potongan);
                        $terefek = true;
                    }
                }

                // hitung kategori
                if (!empty($santri->anak_pegawai_id)) {
                    $totalAnakPegawai++;
                } elseif ($santri->no_kk && DB::table('keluarga')
                    ->join('biodata as b', 'b.id', '=', 'keluarga.id_biodata')
                    ->join('santri as s', 's.biodata_id', '=', 'b.id')
                    ->where('id_biodata', '!=', $santri->biodata_id)
                    ->where('no_kk', $santri->no_kk)
                    ->count() > 1
                ) {
                    $totalBersaudara++;
                } elseif (!empty($santri->khadam_status)) {
                    $totalKhadam++;
                } else {
                    $totalSantriBiasa++;
                }

                if ($santri->jenis_kelamin === 'l') {
                    $totalPutra++;
                } elseif ($santri->jenis_kelamin === 'p') {
                    $totalPutri++;
                }

                TagihanSantri::updateOrCreate(
                    [
                        'tagihan_id' => $tagihan->id,
                        'santri_id'  => $santri->id,
                        'periode'    => $periode
                    ],
                    [
                        'nominal'             => $nominalAkhir,
                        'sisa'                => $nominalAkhir,
                        'tanggal_jatuh_tempo' => $tagihan->jatuh_tempo,
                        'status'              => 'pending'
                    ]
                );
            }

            return [
                'success'             => true,
                'total_santri_biasa'  => $totalSantriBiasa,
                'total_anak_pegawai'  => $totalAnakPegawai,
                'total_bersaudara'    => $totalBersaudara,
                'total_khadam'        => $totalKhadam,
                'total_putra'         => $totalPutra,
                'total_putri'         => $totalPutri,
            ];
        });
    }

    private function cekKelayakanPotongan($santri, $potongan): bool
    {
        switch (strtolower($potongan->nama)) {
            case 'anak pegawai':
                return $santri->anak_pegawai_id ?? false;

            case 'bersaudara':
                $jumlah = DB::table('keluarga')
                    ->join('biodata as b', 'b.id', '=', 'keluarga.id_biodata')
                    ->join('santri as s', 's.biodata_id', '=', 'b.id')
                    ->where('id_biodata', '!=', $santri->biodata_id)
                    ->where('no_kk', $santri->no_kk ?? null)
                    ->count();
                return $jumlah > 1;

            case 'khadam':
                return $santri->khadam_status == true;

            default:
                return SantriPotongan::where('santri_id', $santri->id)
                    ->where('potongan_id', $potongan->id)
                    ->where('status', true)
                    ->exists();
        }
    }

    private function hitungPotongan(float $nominal, $potongan): float
    {
        if ($potongan->jenis == 'persentase') {
            return $nominal - ($nominal * ($potongan->nilai / 100));
        }
        return max(0, $nominal - $potongan->nilai);
    }
}
