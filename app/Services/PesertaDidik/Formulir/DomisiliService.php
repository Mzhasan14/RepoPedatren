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
        $domisili = RiwayatDomisili::with([
            'wilayah:id,nama_wilayah',
            'blok:id,nama_blok',
            'kamar:id,nama_kamar',
            'santri.biodata:id'
        ])
            ->whereHas('santri.biodata', function ($query) use ($bioId) {
                $query->where('id', $bioId);
            })
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_wilayah' => $item->wilayah->nama_wilayah,
                    'nama_blok' => $item->blok->nama_blok,
                    'nama_kamar' => $item->kamar->nama_kamar,
                    'tanggal_masuk' => $item->tanggal_masuk,
                    'tanggal_keluar' => $item->tanggal_keluar,
                    'status' => $item->status,
                ];
            });

        return ['status' => true, 'data' => $domisili];
    }


    public function store(array $data, string $bioId)
    {
        return DB::transaction(function () use ($data, $bioId) {
            $santri = Santri::where('biodata_id', $bioId)->latest()->first();

            if (!$santri) {
                return ['status' => false, 'message' => 'Santri tidak ditemukan untuk biodata ini'];
            }

            // Cek dan update domisili aktif jika ada
            $exist = RiwayatDomisili::where('status', 'aktif')
                ->where('santri_id', $santri->id)
                ->first();

            if ($exist) {
                return ['status' => false, 'message' => 'Santri masih memiliki domisili aktif'];
            }

            $domisili = RiwayatDomisili::create([
                'santri_id'     => $santri->id,
                'wilayah_id'    => $data['wilayah_id'],
                'blok_id'       => $data['blok_id'],
                'kamar_id'      => $data['kamar_id'],
                'tanggal_masuk' => $data['tanggal_masuk'] ?? now(),
                'tanggal_keluar' => null,
                'status'        => 'aktif',
                'created_by'    => Auth::id(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            return ['status' => true, 'data' => $domisili];
        });
    }


    public function edit($id): array
    {
        $domisili = RiwayatDomisili::with(['wilayah', 'blok', 'kamar'])
            ->find($id);

        if (!$domisili) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return [
            'status' => true,
            'data' => [
                'id' => $domisili->id,
                'nama_wilayah' => $domisili->wilayah->nama_wilayah,
                'nama_blok' => $domisili->blok->nama_blok,
                'nama_kamar' => $domisili->kamar->nama_kamar,
                'tanggal_masuk' => $domisili->tanggal_masuk,
                'tanggal_keluar' => $domisili->tanggal_keluar,
            ],
        ];
    }

    public function pindahDomisili(array $data, string $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $domisili = RiwayatDomisili::find($id);

            if (!$domisili) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            // Jika sudah ada tanggal keluar, tidak bisa diubah lagi
            if (!empty($domisili->tanggal_keluar)) {
                return ['status' => false, 'message' => 'Data riwayat domisili tidak boleh diubah setelah keluar'];
            }

            if (empty($data['tanggal_masuk']) || !strtotime($data['tanggal_masuk'])) {
                return ['status' => false, 'message' => 'Tanggal masuk wajib diisi dan harus format tanggal yang valid'];
            }

            $tanggalKeluar = now();
            $tanggalMasukBaru = Carbon::parse($data['tanggal_masuk']);

            if ($tanggalMasukBaru->toDateString() < $tanggalKeluar->toDateString()) {
                return ['status' => false, 'message' => 'Tanggal masuk tidak boleh lebih awal dari tanggal keluar sebelumnya'];
            }

            $domisili->update([
                'status'         => 'pindah',
                'tanggal_keluar' => $tanggalKeluar,
                'updated_by'     => Auth::id(),
                'updated_at'     => now(),
            ]);

            $new = RiwayatDomisili::create([
                'santri_id'      => $domisili->santri_id,
                'wilayah_id'    => $data['wilayah_id'],
                'blok_id'       => $data['blok_id'],
                'kamar_id'      => $data['kamar_id'],
                'tanggal_masuk'  => $data['tanggal_masuk'],
                'status'         => 'aktif',
                'created_by'     => Auth::id(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            return ['status' => true, 'data' => $new];
        });
    }

    public function keluarDomisili(array $data, string $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $domisili = RiwayatDomisili::find($id);

            if (!$domisili) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            // Jika sudah ada tanggal keluar, tidak bisa diubah lagi
            if (!empty($domisili->tanggal_keluar)) {
                return ['status' => false, 'message' => 'Data riwayat domisili tidak boleh diubah setelah berhenti'];
            }

            if (empty($data['tanggal_keluar']) || !strtotime($data['tanggal_keluar'])) {
                return ['status' => false, 'message' => 'Tanggal keluar wajib diisi dan harus format tanggal yang valid'];
            }

            if (strtotime($data['tanggal_keluar']) < strtotime($domisili->tanggal_masuk)) {
                return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk'];
            }

            $tanggalKeluarBaru = Carbon::parse($data['tanggal_keluar']);
            $tanggalMasukLama = Carbon::parse($domisili->tanggal_masuk);

            if ($tanggalKeluarBaru->lt($tanggalMasukLama)) {
                return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk'];
            }

            // Update data
            $domisili->update([
                'status'         => 'keluar',
                'tanggal_keluar' => $data['tanggal_keluar'],
                'updated_by'     => Auth::id(),
                'updated_at'     => now(),
            ]);

            return ['status' => true, 'data' => $domisili];
        });
    }

    public function update(array $data, string $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $domisili = RiwayatDomisili::find($id);

            if (!$domisili) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            if (!empty($data['tanggal_keluar'])) {
                // Validasi tanggal keluar tidak boleh lebih awal dari tanggal masuk
                if (strtotime($data['tanggal_keluar']) < strtotime($domisili->tanggal_masuk)) {
                    return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk'];
                }
            }

            // Update data
            $domisili->update([
                'wilayah_id'    => $data['wilayah_id'],
                'blok_id'       => $data['blok_id'],
                'kamar_id'      => $data['kamar_id'],
                'tanggal_masuk'  => $data['tanggal_masuk'],
                'tanggal_keluar'  => $data['tanggal_keluar'] ?? null,
                'updated_by'     => Auth::id(),
                'updated_at'     => now(),
            ]);

            return ['status' => true, 'data' => $domisili];
        });
    }
}
