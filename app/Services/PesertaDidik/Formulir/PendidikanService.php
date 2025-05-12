<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\Santri;
use App\Models\RiwayatPendidikan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PendidikanService
{
    public function index(string $bioId)
    {
        $pendidikan = RiwayatPendidikan::with([
            'lembaga:id,nama_lembaga',
            'jurusan:id,nama_jurusan',
            'kelas:id,nama_kelas',
            'rombel:id,nama_rombel',
            'santri.biodata:id'
        ])
            ->whereHas('santri.biodata', function ($query) use ($bioId) {
                $query->where('id', $bioId);
            })
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_lembaga' => $item->lembaga->nama_lembaga,
                    'nama_jurusan' => $item->jurusan->nama_jurusan,
                    'nama_kelas' => $item->kelas->nama_kelas,
                    'nama_rombel' => $item->rombel->nama_rombel,
                ];
            });

        return ['status' => true, 'data' => $pendidikan];
    }

    public function store(array $data, string $bioId)
    {
        return DB::transaction(function () use ($data, $bioId) {
            // Ambil data santri terbaru
            $santri = Santri::where('biodata_id', $bioId)->latest()->first();

            if (!$santri) {
                return ['status' => false, 'message' => 'Santri tidak ditemukan untuk biodata ini'];
            }

            // Cek apakah santri memiliki pendidikan aktif
            $existing = RiwayatPendidikan::where('status', 'aktif')
                ->where('santri_id', $santri->id)
                ->first();

            if ($existing) {
                $existing->update([
                    'status'         => 'pindah',
                    'tanggal_keluar' => now(),
                    'updated_by'     => Auth::id(),
                    'updated_at'     => now(),
                ]);
            }

            $new = RiwayatPendidikan::create([
                'santri_id'   => $santri->id,
                'lembaga_id'  => $data['lembaga_id'],
                'jurusan_id'  => $data['jurusan_id'],
                'kelas_id'    => $data['kelas_id'],
                'rombel_id'   => $data['rombel_id'],
                'tanggal_masuk' => $data['tanggal_masuk'] ?? now(),
                'status'      => 'aktif',
                'created_by'  => Auth::id(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return ['status' => true, 'data' => $new];
        });
    }


    public function edit($id): array
    {
        $pendidikan = RiwayatPendidikan::with(['lembaga', 'jurusan', 'kelas', 'rombel'])
            ->find($id);

        if (!$pendidikan) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return [
            'status' => true,
            'data' => [
                'id' => $pendidikan->id,
                'nama_lembaga' => $pendidikan->lembaga->nama_lembaga,
                'nama_jurusan' => $pendidikan->jurusan->nama_jurusan,
                'nama_kelas' => $pendidikan->kelas->nama_kelas,
                'nama_rombel' => $pendidikan->rombel->nama_rombel,
                'tanggal_masuk' => $pendidikan->tanggal_masuk,
                'tanggal_keluar' => $pendidikan->tanggal_keluar,
            ]
        ];
    }

    public function update(array $data, string $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $pendidikan = RiwayatPendidikan::find($id);

            if (!$pendidikan) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            // Jika pendidikan sudah berhenti (tanggal_keluar sudah ada), tidak bisa diubah
            if ($pendidikan->tanggal_keluar) {
                return ['status' => false, 'message' => 'Data riwayat pendidikan tidak boleh diubah setelah berhenti'];
            }

            // Cek percobaan perubahan lembaga, jurusan, kelas, atau rombel
            $isLembagaChanged = isset($data['lembaga_id']) && $pendidikan->lembaga_id != $data['lembaga_id'];
            $isJurusanChanged = isset($data['jurusan_id']) && $pendidikan->jurusan_id != $data['jurusan_id'];
            $isKelasChanged   = isset($data['kelas_id']) && $pendidikan->kelas_id != $data['kelas_id'];
            $isRombelChanged  = isset($data['rombel_id']) && $pendidikan->rombel_id != $data['rombel_id'];

            if ($isLembagaChanged || $isJurusanChanged || $isKelasChanged || $isRombelChanged) {
                return ['status' => false, 'message' => 'Perubahan lembaga, jurusan, kelas, atau rombel tidak diperbolehkan'];
            }

            // Jika ada tanggal keluar
            if (!empty($data['tanggal_keluar'])) {
                if (strtotime($data['tanggal_keluar']) < strtotime($pendidikan->tanggal_masuk)) {
                    return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk'];
                }

                $pendidikan->update([
                    'tanggal_keluar' => $data['tanggal_keluar'],
                    'status' => 'berhenti',
                    'updated_by' => Auth::id(),
                ]);

                return ['status' => true, 'data' => $pendidikan];
            }

            return ['status' => false, 'message' => 'Tidak ada perubahan yang diizinkan selain tanggal keluar'];
        });
    }
}
