<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\Santri;
use Illuminate\Support\Str;
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
            $exist = RiwayatDomisili::where('status', 'aktif')
                ->whereHas('santri.biodata', function ($query) use ($bioId) {
                    $query->where('id', $bioId);
                })
                ->first();

            if ($exist) {
                return ['status' => false, 'message' => 'Santri masih memiliki domisili aktif'];
            }

            $santri = Santri::where('biodata_id', $bioId)->latest()->first();

            if (!$santri) {
                return ['status' => false, 'message' => 'Santri tidak ditemukan untuk biodata ini'];
            }

            $domisili = RiwayatDomisili::create([
                'santri_id'     => $santri->id,
                'wilayah_id'    => $data['wilayah_id'],
                'blok_id'       => $data['blok_id'],
                'kamar_id'      => $data['kamar_id'],
                'tanggal_masuk' => $data['tanggal_masuk'] ?? now(),
                'status'        => 'aktif',
                'created_by'    => Auth::id(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            activity('riwayat_domisili_create')
                ->causedBy(Auth::user())
                ->performedOn($domisili)
                ->withProperties([
                    'new_attributes'   => $domisili->toArray(),
                    'ip'          => request()->ip(),
                    'user_agent'  => request()->userAgent(),
                ])
                ->event('create_domisili')
                ->log('Riwayat domisili baru dibuat.');

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

    public function update(array $data, string $id)
    {
        return DB::transaction(function () use ($data, $id) {

            $domisili = RiwayatDomisili::find($id);

            if (!$domisili) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            if (!is_null($domisili->tanggal_keluar)) {
                return ['status' => false, 'message' => 'Data riwayat tidak boleh diubah!'];
            }

            $before = $domisili->toArray();

            // Handle perubahan tanggal keluar
            if (!empty($data['tanggal_keluar'])) {
                if (strtotime($data['tanggal_keluar']) < strtotime($domisili->tanggal_masuk)) {
                    return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk.'];
                }

                $domisili->update([
                    'tanggal_keluar' => $data['tanggal_keluar'],
                    'status' => 'keluar',
                    'updated_by' => Auth::id()
                ]);



                activity('riwayat_domisili_update')
                    ->performedOn($domisili)
                    ->withProperties([
                        'before' => $before,
                        'after' => $domisili->toArray(),
                    ])
                    ->event('update_domisili')
                    ->log('Riwayat domisili diperbarui (keluar).');

                return ['status' => true, 'data' => $domisili];
            }

            // Handle perubahan tempat
            $isWilayahChanged = $domisili->wilayah_id != $data['wilayah_id'];
            $isBlokChanged    = $domisili->blok_id != $data['blok_id'];
            $isKamarChanged   = $domisili->kamar_id != $data['kamar_id'];

            if ($isWilayahChanged || $isBlokChanged || $isKamarChanged) {
                $domisili->update([
                    'status' => 'pindah',
                    'tanggal_keluar' => now(),
                    'updated_by' => Auth::id()
                ]);

                activity('riwayat_domisili_update')
                    ->performedOn($domisili)
                    ->withProperties([
                        'before' => $before,
                        'after' => $domisili->toArray(),
                    ])
                    ->event('update_domisili')
                    ->log('Riwayat domisili diperbarui (pindah).');

                $new = RiwayatDomisili::create([
                    'santri_id'     => $domisili->santri_id,
                    'wilayah_id'    => $data['wilayah_id'],
                    'blok_id'       => $data['blok_id'],
                    'kamar_id'      => $data['kamar_id'],
                    'tanggal_masuk' => now(),
                    'status'        => 'aktif',
                    'created_by'    => Auth::id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                activity('riwayat_domisili_create')
                    ->performedOn($new)
                    ->withProperties([
                        'before' => null,
                        'after' => $new->toArray(),
                    ])
                    ->event('create_domisili')
                    ->log('Riwayat domisili baru dibuat setelah pindah.');

                return ['status' => true, 'data' => $new];
            }

            return ['status' => false, 'message' => 'Tidak ada perubahan data'];
        });
    }
}
