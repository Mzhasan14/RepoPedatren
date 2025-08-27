<?php

namespace App\Services\PesertaDidik\Pembayaran;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SantriPotonganService
{
    public function assign(array $data): array
    {
        DB::beginTransaction();
        try {
            $results = [];

            foreach ($data['santri_ids'] as $santriId) {
                $record = DB::table('santri_potongan')->updateOrInsert(
                    [
                        'santri_id'   => $santriId,
                        'potongan_id' => $data['potongan_id'],
                    ],
                    [
                        'keterangan'     => $data['keterangan'] ?? null,
                        'status'         => $data['status'] ?? true,
                        'berlaku_dari'   => $data['berlaku_dari'] ?? null,
                        'berlaku_sampai' => $data['berlaku_sampai'] ?? null,
                        'updated_at'     => now(),
                        'created_at'     => now(),
                    ]
                );

                $results[] = [
                    'santri_id'   => $santriId,
                    'potongan_id' => $data['potongan_id'],
                    'status'      => $record ? 'assigned' : 'failed'
                ];
            }

            DB::commit();
            return $results;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('SantriPotonganService assign error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data'  => $data
            ]);
            throw $e;
        }
    }

    public function find(int $id)
    {
        return DB::table('santri_potongan as sp')
            ->join('santri as s', 's.id', '=', 'sp.santri_id')
            ->join('biodata as b', 'b.id', '=', 's.biodata_id')
            ->join('potongan as p', 'p.id', '=', 'sp.potongan_id')
            ->select(
                'sp.*',
                'b.nama as nama_santri',
                'p.nama as nama_potongan'
            )
            ->where('sp.id', $id)
            ->first();
    }

    public function update(int $id, array $data): bool
    {
        DB::beginTransaction();
        try {
            $updated = DB::table('santri_potongan')
                ->where('id', $id)
                ->update([
                    'keterangan'     => $data['keterangan'] ?? null,
                    'status'         => $data['status'] ?? true,
                    'berlaku_dari'   => $data['berlaku_dari'] ?? null,
                    'berlaku_sampai' => $data['berlaku_sampai'] ?? null,
                    'updated_at'     => now(),
                ]);

            DB::commit();
            return (bool) $updated;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('SantriPotonganService update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id'    => $id,
                'data'  => $data
            ]);
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        DB::beginTransaction();
        try {
            $deleted = DB::table('santri_potongan')->where('id', $id)->delete();
            DB::commit();
            return (bool) $deleted;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('SantriPotonganService delete error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id'    => $id
            ]);
            throw $e;
        }
    }

    public function list(array $filter = [])
    {
        $query = DB::table('santri_potongan as sp')
            ->join('santri as s', 's.id', '=', 'sp.santri_id')
            ->join('biodata as b', 'b.id', '=', 's.biodata_id')
            ->join('potongan as p', 'p.id', '=', 'sp.potongan_id')
            ->select(
                'sp.*',
                'b.nama as nama_santri',
                'p.nama as nama_potongan'
            );

        if (!empty($filter['potongan_id'])) {
            $query->where('sp.potongan_id', $filter['potongan_id']);
        }

        if (!empty($filter['aktif_sekarang'])) {
            $today = now()->toDateString();
            $query->where('sp.status', true)
                ->where(function ($q) use ($today) {
                    $q->whereNull('sp.berlaku_dari')->orWhere('sp.berlaku_dari', '<=', $today);
                })
                ->where(function ($q) use ($today) {
                    $q->whereNull('sp.berlaku_sampai')->orWhere('sp.berlaku_sampai', '>=', $today);
                });
        }

        return $query->get();
    }
}
