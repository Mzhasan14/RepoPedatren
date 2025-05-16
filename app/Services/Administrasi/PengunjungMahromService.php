<?php

namespace App\Services\Administrasi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Santri;
use App\Models\PengunjungMahrom;

class PengunjungMahromService
{
    public function index(string $bioId): array
    {
        $kunjungan = PengunjungMahrom::with('santri.biodata:id')
            ->whereHas('santri.biodata', fn($q) => $q->where('id', $bioId))
            ->latest()
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'nama_pengunjung' => $item->nama_pengunjung,
                'hubungan_id' => $item->hubungan_id,
                'jumlah_rombongan' => $item->jumlah_rombongan,
                'tanggal_kunjungan' => $item->tanggal_kunjungan,
                'status' => $item->status
            ]);

        return ['status' => true, 'data' => $kunjungan];
    }

    public function store(array $data, string $bioId): array
    {
        return DB::transaction(function () use ($data, $bioId) {
            $santri = Santri::where('biodata_id', $bioId)->latest()->first();

            if (!$santri) {
                return ['status' => false, 'message' => 'Santri tidak ditemukan untuk biodata ini'];
            }

            $kunjungan = PengunjungMahrom::create([
                'santri_id' => $santri->id,
                'nama_pengunjung' => $data['nama_pengunjung'],
                'hubungan_id' => $data['hubungan_id'],
                'jumlah_rombongan' => $data['jumlah_rombongan'],
                'tanggal_kunjungan' => $data['tanggal_kunjungan'],
                'status'   => $data['status'],
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ['status' => true, 'data' => $kunjungan];
        });
    }

    public function show($id): array
    {
        $kunjungan = PengunjungMahrom::find($id);

        if (!$kunjungan) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return [
            'status' => true,
            'data' => [
                'id' => $kunjungan->id,
                'santri_id' => $kunjungan->santri_id,
                'nama_pengunjung' => $kunjungan->nama_pengunjung,
                'hubungan_id' => $kunjungan->hubungan_id,
                'jumlah_rombongan' => $kunjungan->jumlah_rombongan,
                'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                'status' => $kunjungan->status
            ],
        ];
    }

    public function update(array $data, string $id): array
    {
        return DB::transaction(function () use ($data, $id) {
            $kunjungan = PengunjungMahrom::find($id);

            if (!$kunjungan) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            if (in_array($kunjungan->status, ['selesai', 'ditolak'])) {
                return [
                    'status' => false,
                    'message' => 'Maaf, data dengan status "' . $kunjungan->status . '" tidak dapat diubah.'
                ];
            }

            $kunjungan->update([
                'nama_pengunjung' => $data['nama_pengunjung'],
                'hubungan_id' => $data['hubungan_id'],
                'jumlah_rombongan' => $data['jumlah_rombongan'],
                'tanggal_kunjungan' => $data['tanggal_kunjungan'],
                'status'   => $data['status'],
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            return ['status' => true, 'data' => $kunjungan];
        });
    }
}
