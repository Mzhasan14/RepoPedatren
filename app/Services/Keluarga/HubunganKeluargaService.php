<?php

namespace App\Services\Keluarga;

use App\Models\HubunganKeluarga;
use Illuminate\Support\Facades\Auth;

class HubunganKeluargaService
{
    public function index(): array
    {
        $data = HubunganKeluarga::orderBy('id', 'asc')->get();

        return [
            'status' => true,
            'data' => $data->map(fn ($item) => [
                'id' => $item->id,
                'nama_status' => $item->nama_status,
                'created_by' => $item->created_by,
                'created_at' => $item->created_at,
                'updated_by' => $item->updated_by,
                'updated_at' => $item->updated_at,
                'deleted_by' => $item->deleted_by,
                'deleted_at' => $item->deleted_at,
            ]),
        ];
    }

    public function store(array $input)
    {
        $hubungan = HubunganKeluarga::create([
            'nama_status' => $input['nama_status'],
            'created_by' => Auth::id(),
            'created_at' => now(),
            'status' => true,
        ]);

        return [
            'status' => true,
            'data' => $hubungan,
        ];
    }

    public function show(int $id)
    {
        $hubungan = HubunganKeluarga::find($id);

        if (! $hubungan) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        return [
            'status' => true,
            'data' => $hubungan,
        ];
    }

    public function update(array $input, int $id)
    {
        $hubungan = HubunganKeluarga::find($id);

        if (! $hubungan) {
            return [
                'status' => false,
                'message' => 'data tidak ditemukan',
            ];
        }

        $hubungan->update([
            'nama_status' => $input['nama_status'],
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        return [
            'status' => true,
            'data' => $hubungan,
        ];
    }

    public function delete(int $id)
    {
        $hubungan = HubunganKeluarga::find($id);

        if (! $hubungan) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        $hubungan->delete();

        return [
            'status' => true,
            'message' => 'Data berhasil dihapus',
        ];
    }
}
