<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\Santri;
use Illuminate\Support\Carbon;
use App\Models\RiwayatDomisili;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DomisiliService
{
    public function index(string $bioId): array
    {
        $list = RiwayatDomisili::with([
            'wilayah:id,nama_wilayah',
            'blok:id,nama_blok',
            'kamar:id,nama_kamar',
            'santri.biodata:id'
        ])
            ->whereHas('santri.biodata', fn($q) => $q->where('id', $bioId))
            ->get();

        $data = $list->map(fn(RiwayatDomisili $item) => [
            'id'             => $item->id,
            'nama_wilayah'   => $item->wilayah->nama_wilayah,
            'nama_blok'      => $item->blok->nama_blok,
            'nama_kamar'     => $item->kamar->nama_kamar,
            'tanggal_masuk'  => $item->tanggal_masuk,
            'tanggal_keluar' => $item->tanggal_keluar,
            'status'         => $item->status,
        ]);

        return [
            'status' => true,
            'data'   => $data,
        ];
    }

    public function store(array $input, string $bioId): array
    {
        return DB::transaction(function () use ($input, $bioId) {
            $santri = Santri::where('biodata_id', $bioId)->latest()->first();
            if (! $santri) {
                return [
                    'status'  => false,
                    'message' => 'Santri untuk biodata ini tidak ditemukan.',
                ];
            }

            // Tidak boleh ada domisili 'aktif' ganda
            if (RiwayatDomisili::where('santri_id', $santri->id)
                ->where('status', 'aktif')
                ->exists()
            ) {
                return [
                    'status'  => false,
                    'message' => 'Santri masih memiliki domisili aktif.',
                ];
            }

            $dom = RiwayatDomisili::create([
                'santri_id'     => $santri->id,
                'wilayah_id'    => $input['wilayah_id'],
                'blok_id'       => $input['blok_id'],
                'kamar_id'      => $input['kamar_id'],
                'tanggal_masuk' => $input['tanggal_masuk']
                    ? Carbon::parse($input['tanggal_masuk'])
                    : Carbon::now(),
                'tanggal_keluar' => null,
                'status'        => 'aktif',
                'created_by'    => Auth::id(),
            ]);

            return [
                'status' => true,
                'data'   => $dom,
            ];
        });
    }

    public function show(int $id): array
    {
        $dom = RiwayatDomisili::with(['wilayah', 'blok', 'kamar'])->find($id);

        if (! $dom) {
            return [
                'status'  => false,
                'message' => 'Data tidak ditemukan.',
            ];
        }

        return [
            'status' => true,
            'data'   => [
                'id'             => $dom->id,
                'nama_wilayah'   => $dom->wilayah->nama_wilayah,
                'nama_blok'      => $dom->blok->nama_blok,
                'nama_kamar'     => $dom->kamar->nama_kamar,
                'tanggal_masuk'  => $dom->tanggal_masuk,
                'tanggal_keluar' => $dom->tanggal_keluar,
                'status'         => $dom->status,
            ],
        ];
    }

    public function pindahDomisili(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $old = RiwayatDomisili::find($id);
            if (! $old) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if ($old->tanggal_keluar) {
                return [
                    'status'  => false,
                    'message' => 'Riwayat sudah memiliki tanggal keluar, tidak dapat dipindah.',
                ];
            }

            $newMasuk = Carbon::parse($input['tanggal_masuk'] ?? '');
            $today    = Carbon::now();

            if ($newMasuk->lt($today)) {
                return [
                    'status'  => false,
                    'message' => 'Tanggal masuk baru tidak boleh sebelum hari ini.',
                ];
            }

            // Tutup domisili lama
            $old->update([
                'status'         => 'pindah',
                'tanggal_keluar' => $today,
                'updated_by'     => Auth::id(),
            ]);

            // Buat domisili baru
            $new = RiwayatDomisili::create([
                'santri_id'     => $old->santri_id,
                'wilayah_id'    => $input['wilayah_id'],
                'blok_id'       => $input['blok_id'],
                'kamar_id'      => $input['kamar_id'],
                'tanggal_masuk' => $newMasuk,
                'status'        => 'aktif',
                'created_by'    => Auth::id(),
            ]);

            return [
                'status' => true,
                'data'   => $new,
            ];
        });
    }

    public function keluarDomisili(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $dom = RiwayatDomisili::find($id);
            if (! $dom) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if ($dom->tanggal_keluar) {
                return [
                    'status'  => false,
                    'message' => 'Riwayat sudah ditandai keluar.',
                ];
            }

            $tglKeluar = Carbon::parse($input['tanggal_keluar'] ?? '');
            if ($tglKeluar->lt(Carbon::parse($dom->tanggal_masuk))) {
                return [
                    'status'  => false,
                    'message' => 'Tanggal keluar tidak boleh sebelum tanggal masuk.',
                ];
            }

            $dom->update([
                'status'         => 'keluar',
                'tanggal_keluar' => $tglKeluar,
                'updated_by'     => Auth::id(),
            ]);

            return [
                'status' => true,
                'data'   => $dom,
            ];
        });
    }

    public function update(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $dom = RiwayatDomisili::find($id);
            if (! $dom) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            // Jika data sudah memiliki tanggal keluar sebelumnya, larang perubahan
            if (! is_null($dom->tanggal_keluar)) {
                return [
                    'status'  => false,
                    'message' => 'Data riwayat ini telah memiliki tanggal keluar dan tidak dapat diubah lagi demi menjaga keakuratan histori.',
                ];
            }

            $dom->update([
                'wilayah_id'    => $input['wilayah_id'],
                'blok_id'       => $input['blok_id'],
                'kamar_id'      => $input['kamar_id'],
                'tanggal_masuk' => Carbon::parse($input['tanggal_masuk']),
                'updated_by'    => Auth::id(),
            ]);

            return [
                'status' => true,
                'data'   => $dom,
            ];
        });
    }
}
