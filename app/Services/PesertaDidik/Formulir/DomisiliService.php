<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\Santri;
use App\Models\DomisiliSantri;
use Illuminate\Support\Carbon;
use App\Models\RiwayatDomisili;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DomisiliService
{
    public function index(string $bioId): array
    {
        $riwayat = RiwayatDomisili::with([
            'wilayah:id,nama_wilayah',
            'blok:id,nama_blok',
            'kamar:id,nama_kamar',
            'santri.biodata:id',
        ])
            ->whereHas('santri.biodata', fn($q) => $q->where('id', $bioId))
            ->get();

        $aktif = DomisiliSantri::with([
            'wilayah:id,nama_wilayah',
            'blok:id,nama_blok',
            'kamar:id,nama_kamar',
            'santri.biodata:id',
        ])
            ->whereHas('santri.biodata', fn($q) => $q->where('id', $bioId))
            ->where('status', 'aktif')
            ->first();

        $gabungan = collect($riwayat);
        if ($aktif) {
            $gabungan->push($aktif);
        }

        $gabungan = $gabungan->sortByDesc('tanggal_masuk')->values();

        $data = $gabungan->map(function ($item) {
            return [
                'id'             => $item->id,
                'nama_wilayah'   => $item->wilayah->nama_wilayah ?? null,
                'nama_blok'      => $item->blok->nama_blok ?? null,
                'nama_kamar'     => $item->kamar->nama_kamar ?? null,
                'tanggal_masuk'  => $item->tanggal_masuk ?? null,
                'tanggal_keluar' => $item->tanggal_keluar ?? null,
                'status'         => $item->status ?? null,
                'sumber'         => $item instanceof DomisiliSantri ? 'aktif' : 'riwayat',
            ];
        });

        return [
            'status' => true,
            'data'   => $data,
        ];
    }

    public function store(array $input, string $bioId): array
    {
        return DB::transaction(function () use ($input, $bioId) {
            $santri = Santri::where('biodata_id', $bioId)->latest()->first();
            if (!$santri) {
                return ['status' => false, 'message' => 'Santri tidak ditemukan.'];
            }

            if (DomisiliSantri::where('santri_id', $santri->id)->exists()) {
                return ['status' => false, 'message' => 'Santri masih memiliki domisili aktif.'];
            }

            $tanggalMasuk = $input['tanggal_masuk'] ? Carbon::parse($input['tanggal_masuk']) : now();

            // Ambil tanggal terakhir dari riwayat, jika ada
            $riwayatTerakhir = RiwayatDomisili::where('santri_id', $santri->id)
                ->orderByDesc('tanggal_masuk')
                ->first();

            if ($riwayatTerakhir && $tanggalMasuk->lt(Carbon::parse($riwayatTerakhir->tanggal_masuk))) {
                return [
                    'status' => false,
                    'message' => 'Tanggal masuk tidak boleh lebih awal dari riwayat domisili terakhir (' . $riwayatTerakhir->tanggal_masuk->format('Y-m-d') . '). Harap periksa kembali tanggal yang Anda input.',
                ];
            }

            $dom = DomisiliSantri::create([
                'santri_id'     => $santri->id,
                'wilayah_id'    => $input['wilayah_id'],
                'blok_id'       => $input['blok_id'],
                'kamar_id'      => $input['kamar_id'],
                'tanggal_masuk' => $tanggalMasuk,
                'status'        => $input['status'] ?? 'aktif',
                'created_by'    => Auth::id(),
            ]);

            return ['status' => true, 'data' => $dom];
        });
    }


    public function show(int $id): array
    {
        $dom = RiwayatDomisili::find($id);
        $source = 'riwayat';

        if (!$dom) {
            $dom = DomisiliSantri::find($id);
            $source = 'aktif';
        }

        if (!$dom) {
            return ['status' => false, 'message' => 'Data tidak ditemukan.'];
        }

        return [
            'status' => true,
            'data'   => [
                'id'             => $dom->id,
                'nama_wilayah'   => $dom->wilayah_id ?? '-',
                'nama_blok'      => $dom->blok_id ?? '-',
                'nama_kamar'     => $dom->kamar_id ?? '-',
                'tanggal_masuk'  => $dom->tanggal_masuk,
                'tanggal_keluar' => $dom->tanggal_keluar ?? '-',
                'status'         => $dom->status,
                'sumber'         => $source,
            ],
        ];
    }
    public function pindahDomisili(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $aktif = DomisiliSantri::find($id);

            if (!$aktif) {
                return ['status' => false, 'message' => 'Domisili aktif tidak ditemukan.'];
            }

            $tanggalBaru = Carbon::parse($input['tanggal_masuk']);
            $tanggalLama = Carbon::parse($aktif->tanggal_masuk);

            if ($tanggalBaru->lt($tanggalLama)) {
                return [
                    'status' => false,
                    'message' => 'Tanggal masuk baru tidak boleh lebih awal dari tanggal masuk sebelumnya (' . $tanggalLama->format('Y-m-d') . '). Silakan periksa kembali tanggal yang Anda input.',
                ];
            }

            // Simpan ke riwayat
            RiwayatDomisili::create([
                'santri_id'      => $aktif->santri_id,
                'wilayah_id'     => $aktif->wilayah_id,
                'blok_id'        => $aktif->blok_id,
                'kamar_id'       => $aktif->kamar_id,
                'tanggal_masuk'  => $aktif->tanggal_masuk,
                'tanggal_keluar' => now(),
                'status'         => 'pindah',
                'created_by'     => $aktif->created_by,
            ]);

            // Update domisili aktif
            $aktif->update([
                'wilayah_id'     => $input['wilayah_id'],
                'blok_id'        => $input['blok_id'],
                'kamar_id'       => $input['kamar_id'],
                'tanggal_masuk'  => $tanggalBaru,
                'status'         => 'aktif',
                'updated_by'     => Auth::id(),
                'updated_at'    => now()
            ]);

            return ['status' => true, 'data' => $aktif];
        });
    }


    public function keluarDomisili(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $aktif = DomisiliSantri::find($id);
            if (!$aktif) {
                return ['status' => false, 'message' => 'Domisili aktif tidak ditemukan.'];
            }

            $tglKeluar = Carbon::parse($input['tanggal_keluar']);
            if ($tglKeluar->lt(Carbon::parse($aktif->tanggal_masuk))) {
                return ['status' => false, 'message' => 'Tanggal keluar tidak boleh sebelum tanggal masuk.'];
            }

            RiwayatDomisili::create([
                'santri_id'      => $aktif->santri_id,
                'wilayah_id'     => $aktif->wilayah_id,
                'blok_id'        => $aktif->blok_id,
                'kamar_id'       => $aktif->kamar_id,
                'tanggal_masuk'  => $aktif->tanggal_masuk,
                'tanggal_keluar' => $tglKeluar,
                'status'         => 'keluar',
                'created_by'     => $aktif->created_by,
            ]);

            $aktif->delete();

            return ['status' => true, 'message' => 'Santri telah keluar dari domisili.'];
        });
    }

    public function update(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $dom = DomisiliSantri::find($id);
            if (!$dom) {
                return ['status' => false, 'message' => 'Domisili aktif tidak ditemukan.'];
            }

            $tanggalBaru = Carbon::parse($input['tanggal_masuk']);
            $tanggalLama = Carbon::parse($dom->tanggal_masuk);

            if ($tanggalBaru->lt($tanggalLama)) {
                return [
                    'status' => false,
                    'message' => 'Tanggal masuk baru tidak boleh lebih awal dari tanggal masuk sebelumnya (' . $tanggalLama->format('Y-m-d') . '). Silakan periksa kembali tanggal yang Anda input.',
                ];
            }

            $dom->update([
                'wilayah_id'    => $input['wilayah_id'],
                'blok_id'       => $input['blok_id'],
                'kamar_id'      => $input['kamar_id'],
                'tanggal_masuk' => Carbon::parse($input['tanggal_masuk']),
                'updated_by'    => Auth::id(),
                'updated_at'    => now()
            ]);

            return ['status' => true, 'data' => $dom];
        });
    }
}
