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
            ->first();

        $gabungan = collect($riwayat);
        if ($aktif) {
            $gabungan->push($aktif);
        }

        $gabungan = $gabungan->sortByDesc('created_at')->values();

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

            $dom = DomisiliSantri::create([
                'santri_id'     => $santri->id,
                'wilayah_id'    => $input['wilayah_id'],
                'blok_id'       => $input['blok_id'],
                'kamar_id'      => $input['kamar_id'],
                'tanggal_masuk' => $input['tanggal_masuk'] ? Carbon::parse($input['tanggal_masuk']) : now(),
                'status'        => $input['status'] ?? 'aktif',
                'created_by'    => Auth::id(),
            ]);

            return ['status' => true, 'data' => $dom];
        });
    }

    public function show(int $id): array
    {
        $dom = RiwayatDomisili::with(['wilayah', 'blok', 'kamar'])->find($id);
        $source = 'riwayat';

        if (!$dom) {
            $dom = DomisiliSantri::with(['wilayah', 'blok', 'kamar'])->find($id);
            $source = 'aktif';
        }

        if (!$dom) {
            return ['status' => false, 'message' => 'Data tidak ditemukan.'];
        }

        return [
            'status' => true,
            'data'   => [
                'id'             => $dom->id,
                'nama_wilayah'   => $dom->wilayah->nama_wilayah ?? '-',
                'nama_blok'      => $dom->blok->nama_blok ?? '-',
                'nama_kamar'     => $dom->kamar->nama_kamar ?? '-',
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

            RiwayatDomisili::create([
                'santri_id'      => $aktif->santri_id,
                'wilayah_id'     => $aktif->wilayah_id,
                'blok_id'        => $aktif->blok_id,
                'kamar_id'       => $aktif->kamar_id,
                'tanggal_masuk'  => $aktif->tanggal_masuk,
                'tanggal_keluar' => now(),
                'status'         => 'pindah',
                'created_by'     => $aktif->created_by,
                'updated_by'     => Auth::id(),
            ]);

            $aktif->update([
                'wilayah_id'     => $input['wilayah_id'],
                'blok_id'        => $input['blok_id'],
                'kamar_id'       => $input['kamar_id'],
                'tanggal_masuk'  => Carbon::parse($input['tanggal_masuk']),
                'status'         => $input['status'] ?? 'aktif',
                'updated_by'     => Auth::id(),
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
                'updated_by'     => Auth::id(),
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

            RiwayatDomisili::create([
                'santri_id'      => $dom->santri_id,
                'wilayah_id'     => $dom->wilayah_id,
                'blok_id'        => $dom->blok_id,
                'kamar_id'       => $dom->kamar_id,
                'tanggal_masuk'  => $dom->tanggal_masuk,
                'tanggal_keluar' => now(),
                'status'         => $dom->status,
                'created_by'     => $dom->created_by,
                'updated_by'     => Auth::id(),
            ]);

            $dom->update([
                'wilayah_id'    => $input['wilayah_id'],
                'blok_id'       => $input['blok_id'],
                'kamar_id'      => $input['kamar_id'],
                'tanggal_masuk' => Carbon::parse($input['tanggal_masuk']),
                'status'        => $input['status'] ?? $dom->status,
                'updated_by'    => Auth::id(),
            ]);

            return ['status' => true, 'data' => $dom];
        });
    }
}
