<?php

namespace App\Services\PesertaDidik\Pembayaran;

use Exception;
use App\Models\Potongan;
use App\Models\SantriPotongan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SantriPotonganImport;

class PotonganService
{
    public function create(array $data): Potongan
    {
        DB::beginTransaction();
        try {
            $potongan = Potongan::create([
                'nama'       => $data['nama'],
                'jenis'      => $data['jenis'],
                'nilai'      => $data['nilai'],
                'status'     => $data['status'] ?? true,
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            if (!empty($data['tagihan_ids'])) {
                $potongan->tagihans()->sync($data['tagihan_ids']);
            }

            DB::commit();
            Log::info('Potongan created', ['id' => $potongan->id]);
            return $potongan;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create Potongan', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update Potongan (dengan optional relasi ke Tagihan)
     */
    public function update(Potongan $potongan, array $data): Potongan
    {
        DB::beginTransaction();
        try {
            $potongan->update([
                'nama'       => $data['nama'],
                'jenis'      => $data['jenis'],
                'nilai'      => $data['nilai'],
                'status'     => $data['status'] ?? $potongan->status,
                'keterangan' => $data['keterangan'] ?? $potongan->keterangan,
            ]);

            if (array_key_exists('tagihan_ids', $data)) {
                $potongan->tagihans()->sync($data['tagihan_ids'] ?? []);
            }

            DB::commit();
            Log::info('Potongan updated', ['id' => $potongan->id]);
            return $potongan;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update Potongan', ['id' => $potongan->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete Potongan
     */
    public function delete(Potongan $potongan): bool
    {
        DB::beginTransaction();
        try {
            $potongan->tagihans()->detach();
            $potongan->delete();

            DB::commit();
            Log::info('Potongan deleted', ['id' => $potongan->id]);
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete Potongan', ['id' => $potongan->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
