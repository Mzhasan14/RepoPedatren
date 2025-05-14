<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\Santri;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StatusSantriService
{
    public function index(string $bioId): array
    {
        $santri = Santri::where('biodata_id', $bioId)
            ->get();

        if ($santri->isEmpty()) {
            return [
                'status'  => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        $data = $santri->map(fn(Santri $santri) => [
            'id'             => $santri->id,
            'nis'            => $santri->nis,
            'tanggal_masuk'  => $santri->tanggal_masuk,
            'tanggal_keluar' => $santri->tanggal_keluar,
            'status'         => $santri->status,
        ])->toArray();

        return [
            'status' => true,
            'data'   => $data,
        ];
    }

    public function store(array $input, string $bioId): array
    {
        return DB::transaction(function () use ($input, $bioId) {
            // Check existing active santri
            $exists = Santri::where('biodata_id', $bioId)
                ->where('status', 'aktif')
                ->exists();

            if ($exists) {
                return [
                    'status'  => false,
                    'message' => 'Santri masih dalam status aktif',
                ];
            }

            $santri = Santri::create([
                'biodata_id'    => $bioId,
                'nis'           => $input['nis'],
                'tanggal_masuk' => isset($input['tanggal_masuk'])
                    ? Carbon::parse($input['tanggal_masuk'])
                    : Carbon::now(),
                'tanggal_keluar' => $input['tanggal_keluar']
                    ? Carbon::parse($input['tanggal_keluar'])
                    : null,
                'status'        => 'aktif',
                'created_by'    => Auth::id(),
            ]);

            return [
                'status' => true,
                'data'   => $santri,
            ];
        });
    }

    public function show(int $id): array
    {
        $santri = Santri::find($id);

        if (! $santri) {
            return [
                'status'  => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        return [
            'status' => true,
            'data'   => [
                'id'             => $santri->id,
                'nis'            => $santri->nis,
                'tanggal_masuk'  => $santri->tanggal_masuk,
                'tanggal_keluar' => $santri->tanggal_keluar,
                'status'         => $santri->status,
            ],
        ];
    }

    public function update(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $santri = Santri::find($id);

            if (! $santri) {
                return [
                    'status'  => false,
                    'message' => 'Data tidak ditemukan',
                ];
            }

            // Assign only provided fields
            $santri->nis            = $input['nis'] ?? $santri->nis;
            $santri->tanggal_masuk  = isset($input['tanggal_masuk'])
                ? Carbon::parse($input['tanggal_masuk'])
                : $santri->tanggal_masuk;
            $santri->tanggal_keluar = isset($input['tanggal_keluar'])
                ? Carbon::parse($input['tanggal_keluar'])
                : $santri->tanggal_keluar;
            $santri->status         = $input['status'] ?? $santri->status;
            $santri->updated_by     = Auth::id();

            // Check if any change
            if (! $santri->isDirty()) {
                return [
                    'status'  => false,
                    'message' => 'Tidak ada perubahan data',
                ];
            }

            $santri->save();

            return [
                'status' => true,
                'data'   => $santri,
            ];
        });
    }

    public function delete(int $id): array
    {
        $santri = Santri::find($id);

        if (! $santri) {
            return [
                'status'  => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        $santri->delete();

        return [
            'status'  => true,
            'message' => 'Data berhasil dihapus',
        ];
    }
}
