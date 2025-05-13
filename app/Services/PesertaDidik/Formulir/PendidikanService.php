<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\Santri;
use Illuminate\Support\Carbon;
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
            $santri = Santri::where('biodata_id', $bioId)->latest()->first();

            if (!$santri) {
                return ['status' => false, 'message' => 'Santri tidak ditemukan untuk biodata ini'];
            }

            // Cek apakah santri sudah memiliki pendidikan aktif
            $exist = RiwayatPendidikan::whereHas('santri.biodata', function ($query) use ($bioId) {
                $query->where('id', $bioId);
            })->where('status', 'aktif')->first();

            if ($exist) {
                return ['status' => false, 'message' => 'Santri masih memiliki pendidikan aktif'];
            }

            $new = RiwayatPendidikan::create([
                'santri_id'   => $santri->id,
                'no_induk'    => $data['no_induk'] ?? null,
                'lembaga_id'  => $data['lembaga_id'],
                'jurusan_id'  => $data['jurusan_id'],
                'kelas_id'    => $data['kelas_id'],
                'rombel_id'   => $data['rombel_id'],
                'tanggal_masuk' => $data['tanggal_masuk'] ?? now(),
                'tanggal_keluar' => null,
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

    public function pindahPendidikan(array $data, string $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $pendidikan = RiwayatPendidikan::find($id);

            if (!$pendidikan) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            // Jika sudah ada tanggal keluar, tidak bisa diubah lagi
            if (!empty($pendidikan->tanggal_keluar)) {
                return ['status' => false, 'message' => 'Data riwayat pendidikan tidak boleh diubah setelah berhenti'];
            }

            if (empty($data['tanggal_masuk']) || !strtotime($data['tanggal_masuk'])) {
                return ['status' => false, 'message' => 'Tanggal masuk wajib diisi dan harus format tanggal yang valid'];
            }

            $tanggalKeluar = now();
            $tanggalMasukBaru = Carbon::parse($data['tanggal_masuk']);

            if ($tanggalMasukBaru->lt($tanggalKeluar)) {
                return ['status' => false, 'message' => 'Tanggal masuk tidak boleh lebih awal dari tanggal keluar sebelumnya'];
            }

            $pendidikan->update([
                'status'         => 'pindah',
                'tanggal_keluar' => $tanggalKeluar,
                'updated_by'     => Auth::id(),
                'updated_at'     => now(),
            ]);

            $new = RiwayatPendidikan::create([
                'santri_id'      => $pendidikan->santri_id,
                'no_induk'       => $pendidikan?->no_induk ?? null,
                'lembaga_id'     => $data['lembaga_id'],
                'jurusan_id'     => $data['jurusan_id'],
                'kelas_id'       => $data['kelas_id'],
                'rombel_id'      => $data['rombel_id'],
                'tanggal_masuk'  => $data['tanggal_masuk'],
                'status'         => 'aktif',
                'created_by'     => Auth::id(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);


            return ['status' => true, 'data' => $new];
        });
    }

    public function keluarPendidikan(array $data, string $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $pendidikan = RiwayatPendidikan::find($id);

            if (!$pendidikan) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            // Jika sudah ada tanggal keluar, tidak bisa diubah lagi
            if (!empty($pendidikan->tanggal_keluar)) {
                return ['status' => false, 'message' => 'Data riwayat pendidikan tidak boleh diubah setelah berhenti'];
            }

            if (empty($data['tanggal_keluar']) || !strtotime($data['tanggal_keluar'])) {
                return ['status' => false, 'message' => 'Tanggal keluar wajib diisi dan harus format tanggal yang valid'];
            }

            if (strtotime($data['tanggal_keluar']) < strtotime($pendidikan->tanggal_masuk)) {
                return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk'];
            }

            $tanggalKeluarBaru = Carbon::parse($data['tanggal_keluar']);
            $tanggalMasukLama = Carbon::parse($pendidikan->tanggal_masuk);

            if ($tanggalKeluarBaru->lt($tanggalMasukLama)) {
                return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk'];
            }

            // Update data
            $pendidikan->update([
                'status'         => $data['status'],
                'tanggal_keluar' => $data['tanggal_keluar'],
                'updated_by'     => Auth::id(),
                'updated_at'     => now(),
            ]);

            return ['status' => true, 'data' => $pendidikan];
        });
    }

    public function update(array $data, string $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $pendidikan = RiwayatPendidikan::find($id);

            if (!$pendidikan) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }
            if (!empty($data['tanggal_keluar'])) {
                // Validasi tanggal keluar tidak boleh lebih awal dari tanggal masuk
                if (strtotime($data['tanggal_keluar']) < strtotime($pendidikan->tanggal_masuk)) {
                    return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk'];
                }
            }
            // Update data
            $pendidikan->update([
                'no_induk'       => $pendidikan?->no_induk ?? null,
                'lembaga_id'     => $data['lembaga_id'],
                'jurusan_id'     => $data['jurusan_id'] ?? null,
                'kelas_id'       => $data['kelas_id'] ?? null,
                'rombel_id'      => $data['rombel_id'] ?? null,
                'tanggal_masuk'  => $data['tanggal_masuk'],
                'tanggal_keluar'  => $data['tanggal_keluar'] ?? null,
                'updated_by'     => Auth::id(),
                'updated_at'     => now(),
            ]);

            return ['status' => true, 'data' => $pendidikan];
        });
    }
}
