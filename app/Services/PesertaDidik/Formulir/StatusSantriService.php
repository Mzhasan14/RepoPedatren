<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\DomisiliSantri;
use App\Models\RiwayatDomisili;
use App\Models\Santri;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatusSantriService
{
    public function index(string $bioId): array
    {
        $santri = Santri::where('biodata_id', $bioId)
            ->orderByDesc('tanggal_masuk')
            ->get();

        if ($santri->isEmpty()) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        $data = $santri->map(fn (Santri $santri) => [
            'id' => $santri->id,
            'nis' => $santri->nis,
            'angkatan_id' => $santri->angkatan_id,
            'tanggal_masuk' => $santri->tanggal_masuk,
            'tanggal_keluar' => $santri->tanggal_keluar,
            'status' => $santri->status,
        ])->toArray();

        return [
            'status' => true,
            'data' => $data,
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
                    'status' => false,
                    'message' => 'Santri masih dalam status aktif',
                ];
            }

            $tanggalMasuk = $input['tanggal_masuk'] ? Carbon::parse($input['tanggal_masuk']) : now();

            // Ambil tanggal terakhir dari riwayat, jika ada
            $riwayatTerakhir = Santri::where('biodata_id', $bioId)
                ->orderByDesc('tanggal_masuk')
                ->first();

            if ($riwayatTerakhir && $tanggalMasuk->lt(Carbon::parse($riwayatTerakhir->tanggal_masuk))) {
                return [
                    'status' => false,
                    'message' => 'Tanggal masuk tidak boleh lebih awal dari riwayat domisili terakhir ('.Carbon::parse($riwayatTerakhir->tanggal_masuk)->format('Y-m-d').'). Harap periksa kembali tanggal yang Anda input.',
                ];
            }

            $santri = Santri::create([
                'biodata_id' => $bioId,
                'nis' => $input['nis'] ?? null,
                'tanggal_masuk' => isset($input['tanggal_masuk'])
                    ? Carbon::parse($input['tanggal_masuk'])
                    : Carbon::now(),
                'angkatan_id' => $input['angkatan_id'] ?? null,
                'tanggal_keluar' => null,
                'status' => 'aktif',
                'created_by' => Auth::id(),
            ]);

            return [
                'status' => true,
                'data' => $santri,
            ];
        });
    }

    public function show(int $id): array
    {
        $santri = Santri::find($id);

        if (! $santri) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        return [
            'status' => true,
            'data' => [
                'id' => $santri->id,
                'nis' => $santri->nis,
                'angkatan_id' => $santri->angkatan_id,
                'tanggal_masuk' => $santri->tanggal_masuk,
                'tanggal_keluar' => $santri->tanggal_keluar,
                'status' => $santri->status,
            ],
        ];
    }

    public function update(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $santri = Santri::find($id);

            if (! $santri) {
                return [
                    'status' => false,
                    'message' => 'Data tidak ditemukan',
                ];
            }

            // Ambil tanggal masuk dan keluar dari input atau fallback ke nilai lama
            $tanggalMasuk = isset($input['tanggal_masuk'])
                ? Carbon::parse($input['tanggal_masuk'])
                : $santri->tanggal_masuk;

            $tanggalKeluar = isset($input['tanggal_keluar'])
                ? Carbon::parse($input['tanggal_keluar'])
                : $santri->tanggal_keluar;

            // Validasi: tanggal_keluar tidak boleh kurang dari tanggal_masuk
            if ($tanggalKeluar && $tanggalKeluar->lt($tanggalMasuk)) {
                return [
                    'status' => false,
                    'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk',
                ];
            }

            // Ambil status dari input atau dari database
            $status = $input['status'] ?? $santri->status;

            // Validasi: jika status 'aktif', tanggal_keluar tidak boleh diisi
            if (strtolower($status) === 'aktif' && isset($input['tanggal_keluar'])) {
                return [
                    'status' => false,
                    'message' => 'Tanggal keluar tidak boleh diisi jika status santri masih aktif.',
                ];
            }

            // Validasi: jika status termasuk salah satu status non-aktif, tanggal_keluar wajib diisi
            $statusNonAktif = ['alumni', 'do', 'berhenti', 'nonaktif'];
            if (in_array($status, $statusNonAktif) && empty($input['tanggal_keluar'])) {
                return [
                    'status' => false,
                    'message' => 'Mohon mengisi tanggal keluar karena status santri telah berubah menjadi tidak aktif.',
                ];
            }

            // Lakukan update nilai-nilai
            $santri->tanggal_masuk = $tanggalMasuk;
            $santri->tanggal_keluar = $tanggalKeluar;
            $santri->angkatan_id = $input['angkatan_id'] ?? $santri->angkatan_id;
            $santri->status = $status;
            $santri->updated_at = Carbon::now();
            $santri->updated_by = Auth::id();

            // Check if any change
            if (! $santri->isDirty()) {
                return [
                    'status' => false,
                    'message' => 'Tidak ada perubahan data',
                ];
            }

            $santri->save();

            // Jika tanggal_keluar baru diisi atau diperbarui
            if (! is_null($input['tanggal_keluar']) && ($santri->wasChanged('tanggal_keluar'))) {
                $dom = DomisiliSantri::where('santri_id', $santri->id)->where('status', 'aktif')->first();

                if ($dom) {
                    $now = Carbon::now();
                    $user = Auth::id();

                    $dom->status = 'keluar';
                    $dom->tanggal_keluar = $now;
                    $dom->updated_at = $now;
                    $dom->updated_by = $user;
                    $dom->save();

                    RiwayatDomisili::create([
                        'santri_id' => $dom->santri_id,
                        'wilayah_id' => $dom->wilayah_id,
                        'blok_id' => $dom->blok_id,
                        'kamar_id' => $dom->kamar_id,
                        'tanggal_masuk' => $dom->tanggal_masuk,
                        'tanggal_keluar' => $now,
                        'status' => 'keluar',
                        'created_by' => $user,
                    ]);
                }
            }

            return [
                'status' => true,
                'data' => $santri,
            ];
        });
    }

    public function delete(int $id): array
    {
        $santri = Santri::find($id);

        if (! $santri) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        $santri->delete();

        return [
            'status' => true,
            'message' => 'Data berhasil dihapus',
        ];
    }
}
