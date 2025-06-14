<?php

namespace App\Services\Pegawai;

use App\Models\Pendidikan\Lembaga;
use Illuminate\Support\Facades\Auth;

class LembagaService
{
    public function index(): array
    {
        $lembaga = Lembaga::select([
            'id',
            'nama_lembaga',
            'status',
        ])
            ->where('status', 1)
            ->get();

        return ['status' => true, 'data' => $lembaga];
    }

    public function edit($id): array
    {
        $lembaga = Lembaga::select([
            'id',
            'nama_lembaga',
            'status',
        ])->find($id);

        if (! $lembaga) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $lembaga];
    }

    public function update(array $data, $id): array
    {
        $lembaga = Lembaga::find($id);
        if (! $lembaga) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        $lembaga->update([
            'nama_lembaga' => $data['nama_lembaga'],
            'updated_by' => Auth::id(),
        ]);

        return ['status' => true, 'data' => $lembaga];
    }

    public function store(array $data): array
    {
        $lembaga = Lembaga::create([
            'nama_lembaga' => $data['nama_lembaga'],
            'status' => 1,
            'created_by' => Auth::id(),
        ]);

        return ['status' => true, 'data' => $lembaga];
    }

    public function destroy($id): array
    {
        $lembaga = Lembaga::find($id);
        if (! $lembaga) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        $lembaga->update([
            'status' => 0,
            'updated_by' => Auth::id(),
        ]);

        return ['status' => true, 'data' => $lembaga, 'message' => 'Lembaga berhasil dinonaktifkan'];
    }
}
