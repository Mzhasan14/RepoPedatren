<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\Santri;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StatusSantriService
{
    public function index($bioId)
    {
        $santri = Santri::where('biodata_id', $bioId)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nis' => $item->nis,
                    'tanggal_masuk' => $item->tanggal_masuk,
                    'tanggal_keluar' => $item->tanggal_keluar,
                    'status' => $item->status
                ];
            });

        if (!$santri) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $santri];
    }

    public function store(array $data, string $bioId)
    {
        return DB::transaction(function () use ($data, $bioId) {
            $exist = Santri::where('status', 'aktif')
                ->whereHas('biodata', function ($query) use ($bioId) {
                    $query->where('id', $bioId);
                })
                ->first();

            if ($exist) {
                return ['status' => false, 'message' => 'Data masih santri aktif'];
            }

            $santri = new Santri();
            $santri->biodata_id = $bioId;
            $santri->nis = $data['nis'];
            $santri->tanggal_masuk = $data['tanggal_masuk'] ?? now();
            $santri->tanggal_keluar = $data['tanggal_keluar'] ?? null;
            $santri->status = 'aktif';
            $santri->created_by = Auth::id();
            $santri->save();

            return ['status' => true, 'data' => $santri];
        });
    }

    public function edit($id)
    {
        $santri = Santri::find($id);

        if (!$santri) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return [
            'status' => true,
            'data' => [
                'id' => $santri->id,
                'nis' => $santri->nis,
                'tanggal_masuk' => $santri->tanggal_masuk,
                'tanggal_keluar' => $santri->tanggal_keluar,
                'status' => $santri->status
            ],
        ];
    }

    public function update(array $data, $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $santri = Santri::find($id);

            if (!$santri) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            $santri->nis = $data['nis'] ?? $santri->nis;
            $santri->tanggal_masuk = $data['tanggal_masuk'] ?? $santri->tanggal_masuk;
            $santri->tanggal_keluar = $data['tanggal_keluar'] ?? $santri->tanggal_keluar;
            $santri->status = $data['status'] ?? $santri->status;
            $santri->updated_by = Auth::id();
            $santri->updated_at = now();
            $santri->save();

            return ['status' => true, 'data' => $santri];
        });
    }
}
