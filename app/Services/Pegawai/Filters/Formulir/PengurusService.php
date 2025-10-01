<?php

namespace App\Services\Pegawai\Filters\Formulir;

use App\Models\AnakPegawai;
use App\Models\Pegawai\Karyawan;
use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\Pengajar;
use App\Models\Pegawai\Pengurus;
use App\Models\Pegawai\WaliKelas;
use App\Models\Santri;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengurusService
{
    public function index(string $bioId): array
    {
        $pengurus = Pengurus::whereHas('pegawai.biodata', fn($query) => $query->where('id', $bioId))
            ->with(['pegawai.biodata'])
            ->orderBy('tanggal_mulai', 'desc')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'jabatan_kontrak' => $item->jabatan,
                'satuan_kerja' => $item->satuan_kerja,
                'keterangan_jabatan' => $item->keterangan_jabatan,
                'tanggal_masuk' => $item->tanggal_mulai,
                'tanggal_keluar' => $item->tanggal_akhir,
                'status' => $item->status_aktif,
            ]);

        return [
            'status' => true,
            'data' => $pengurus,
        ];
    }

    public function show($id): array
    {
        $pengurus = Pengurus::select([
            'id',
            'golongan_jabatan_id',
            'satuan_kerja',
            'jabatan as jabatan_kontrak',
            'keterangan_jabatan',
            'tanggal_mulai as tanggal_masuk',
            'tanggal_akhir as tanggal_keluar',
            'status_aktif as status',
        ])
            ->find($id);

        if (! $pengurus) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        return [
            'status' => true,
            'data' => $pengurus,
        ];
    }

    public function update(array $input, string $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $pengurus = Pengurus::find($id);
            if (! $pengurus) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            // Larangan update jika tanggal_akhir sudah ada
            if (! is_null($pengurus->tanggal_akhir) && $pengurus->status_aktif === 'tidak aktif') {
                return [
                    'status' => false,
                    'message' => 'Data pengurus ini telah memiliki tanggal akhir dan statusnya tidak aktif, tidak dapat diubah lagi demi menjaga keakuratan histori.',
                ];
            }

            // Update data
            $pengurus->update([
                'golongan_jabatan_id' => $input['golongan_jabatan_id'],
                'satuan_kerja' => $input['satuan_kerja'] ?? $pengurus->satuan_kerja,
                'jabatan' => $input['jabatan'] ?? $pengurus->jabatan,
                'keterangan_jabatan' => $input['keterangan_jabatan'] ?? $pengurus->keterangan_jabatan,
                'tanggal_mulai' => Carbon::parse($input['tanggal_mulai']),
                'updated_by' => Auth::id(),
            ]);

            return [
                'status' => true,
                'data' => $pengurus,
            ];
        });
    }

    public function store(array $data, string $bioId): array
    {
        // 1. Cek apakah masih ada santri aktif untuk biodata ini
        $santriAktif = Santri::where('biodata_id', $bioId)
            ->where('status', 'aktif')
            ->first();

        if ($santriAktif) {
            return [
                'status' => false,
                'message' => 'Data masih terdaftar sebagai Santri aktif. Tidak bisa menjadi Pengurus.',
            ];
        }

        // 2. Cek apakah sudah ada pengurus aktif untuk biodata ini
        $exist = Pengurus::whereHas('pegawai', fn($q) => $q->where('biodata_id', $bioId))
            ->where('status_aktif', 'aktif')
            ->first();

        if ($exist) {
            return [
                'status' => false,
                'message' => 'Pegawai masih memiliki Pengurus aktif',
            ];
        }

        // 3. Cari pegawai berdasarkan biodata
        $pegawai = Pegawai::where('biodata_id', $bioId)->latest()->first();

        if (! $pegawai) {
            return [
                'status' => false,
                'message' => 'Pegawai tidak ditemukan untuk biodata ini',
            ];
        }

        // 4. Buat Pengurus Baru
        $pengurus = Pengurus::create([
            'pegawai_id' => $pegawai->id,
            'golongan_jabatan_id' => $data['golongan_jabatan_id'],
            'satuan_kerja' => $data['satuan_kerja'],
            'jabatan' => $data['jabatan'],
            'keterangan_jabatan' => $data['keterangan_jabatan'],
            'tanggal_mulai' => $data['tanggal_mulai'] ?? now(),
            'status_aktif' => 'aktif',
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'status' => true,
            'data' => $pengurus->fresh(),
        ];
    }

    public function pindahPengurus(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $old = Pengurus::find($id);
            if (! $old) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if ($old->tanggal_akhir) {
                return [
                    'status' => false,
                    'message' => 'Data pengurus sudah memiliki tanggal akhir, tidak dapat diganti.',
                ];
            }

            $tanggalMulaiBaru = Carbon::parse($input['tanggal_mulai'] ?? '');
            $hariIni = Carbon::now();

            if ($tanggalMulaiBaru->lt($hariIni)) {
                return [
                    'status' => false,
                    'message' => 'Tanggal mulai baru tidak boleh sebelum hari ini.',
                ];
            }

            $old->update([
                'status_aktif' => 'tidak aktif',
                'tanggal_akhir' => $hariIni,
                'updated_by' => Auth::id(),
            ]);

            $new = Pengurus::create([
                'pegawai_id' => $old->pegawai_id,
                'golongan_jabatan_id' => $input['golongan_jabatan_id'],
                'satuan_kerja' => $input['satuan_kerja'] ?? $old->satuan_kerja,
                'jabatan' => $input['jabatan'] ?? $old->jabatan,
                'keterangan_jabatan' => $input['keterangan_jabatan'] ?? $old->keterangan_jabatan,
                'tanggal_mulai' => $tanggalMulaiBaru,
                'status_aktif' => 'aktif',
                'created_by' => Auth::id(),
            ]);

            return [
                'status' => true,
                'data' => $new,
            ];
        });
    }

    public function keluarPengurus(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $pengurus = Pengurus::find($id);
            if (! $pengurus) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if ($pengurus->tanggal_akhir || $pengurus->status_aktif === 'tidak aktif') {
                return [
                    'status' => false,
                    'message' => 'Data pengurus sudah ditandai selesai/nonaktif.',
                ];
            }

            $tglAkhir = Carbon::parse($input['tanggal_akhir'] ?? '');
            if ($tglAkhir->lt(Carbon::parse($pengurus->tanggal_mulai))) {
                return [
                    'status' => false,
                    'message' => 'Tanggal keluar tidak boleh sebelum tanggal masuk.',
                ];
            }

            $pegawaiId = $pengurus->pegawai_id;

            $masihAktif = (
                Karyawan::where('pegawai_id', $pegawaiId)
                ->where('status_aktif', 'aktif')
                ->whereNull('tanggal_selesai')
                ->exists() ||

                Pengajar::where('pegawai_id', $pegawaiId)
                ->where('status_aktif', 'aktif')
                ->whereNull('tahun_akhir')
                ->exists() ||

                WaliKelas::where('pegawai_id', $pegawaiId)
                ->where('status_aktif', 'aktif')
                ->whereNull('periode_akhir')
                ->exists()
            );

            $pengurus->update([
                'status_aktif'   => 'tidak aktif',
                'tanggal_akhir'  => $tglAkhir,
                'updated_by'     => Auth::id(),
            ]);

            if (! $masihAktif) {
                Pegawai::where('id', $pegawaiId)->update([
                    'status_aktif' => 'tidak aktif',
                    'updated_by'   => Auth::id(),
                ]);

                // AnakPegawai::where('pegawai_id', $pegawaiId)->update([
                //     'status' => false,
                //     'updated_by'   => Auth::id(),
                //     'updated_at'   => now(),
                // ]);
            }

            return [
                'status' => true,
                'data'   => $pengurus,
            ];
        });
    }
}
