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

class KaryawanService
{
    public function index(string $bioId): array
    {
        $karyawan = Karyawan::whereHas('pegawai.biodata', fn($query) => $query->where('id', $bioId))
            ->with(['pegawai.biodata'])
            ->orderBy('tanggal_mulai', 'desc')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'jabatan_kontrak' => $item->jabatan,
                'keterangan_jabatan' => $item->keterangan_jabatan,
                'tanggal_masuk' => $item->tanggal_mulai,
                'tanggal_keluar' => $item->tanggal_selesai,
                'status' => $item->status_aktif,
            ]);

        return [
            'status' => true,
            'data' => $karyawan,
        ];
    }

    public function show($id): array
    {
        $karyawan = Karyawan::select([
            'id',
            'golongan_jabatan_id',
            'lembaga_id',
            'jabatan as jabatan_kontrak',
            'keterangan_jabatan',
            'tanggal_mulai as tanggal_masuk',
            'tanggal_selesai as tanggal_keluar',
            'status_aktif as status',
        ])
            ->find($id);

        if (! $karyawan) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        return [
            'status' => true,
            'data' => $karyawan,
        ];
    }

    public function store(array $data, string $bioId): array
    {
        // 1. Validasi Santri Aktif
        $santriAktif = Santri::where('biodata_id', $bioId)
            ->where('status', 'aktif')
            ->first();

        if ($santriAktif) {
            return [
                'status' => false,
                'message' => 'Data masih terdaftar sebagai Santri aktif. Tidak bisa menjadi Pengajar.',
            ];
        }

        // 2. Validasi Karyawan Aktif
        $exist = Karyawan::whereHas('pegawai', fn($q) => $q->where('biodata_id', $bioId))
            ->where('status_aktif', 'aktif')
            ->first();

        if ($exist) {
            return [
                'status' => false,
                'message' => 'Pegawai masih memiliki Karyawan aktif',
            ];
        }

        // 3. Cari Pegawai berdasarkan biodata_id
        $pegawai = Pegawai::where('biodata_id', $bioId)
            ->latest()
            ->first();

        if (! $pegawai) {
            return [
                'status' => false,
                'message' => 'Pegawai tidak ditemukan untuk biodata ini',
            ];
        }

        // 4. Buat Karyawan Baru
        $karyawan = Karyawan::create([
            'pegawai_id' => $pegawai->id,
            'golongan_jabatan_id' => $data['golongan_jabatan_id'],
            'lembaga_id' => $data['lembaga_id'],
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
            'data' => $karyawan->fresh(),
        ];
    }

    public function update(array $input, string $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            // 1. Cari data
            $karyawan = Karyawan::find($id);
            if (! $karyawan) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            // 2. Jika sudah ada tanggal_selesai dan statusnya tidak aktif, larang update
            if (! is_null($karyawan->tanggal_selesai) && $karyawan->status_aktif === 'tidak aktif') {
                return [
                    'status' => false,
                    'message' => 'Data karyawan ini telah memiliki tanggal selesai dan statusnya tidak aktif, tidak dapat diubah lagi demi menjaga keakuratan histori.',
                ];
            }

            // 3. Update data tanpa validasi tanggal selesai
            $karyawan->update([
                'golongan_jabatan_id' => $input['golongan_jabatan_id'],
                'lembaga_id' => $input['lembaga_id'],
                'jabatan' => $input['jabatan'] ?? $karyawan->jabatan,
                'keterangan_jabatan' => $input['keterangan_jabatan'] ?? $karyawan->keterangan_jabatan,
                'tanggal_mulai' => Carbon::parse($input['tanggal_mulai']),
                'updated_by' => Auth::id(),
            ]);

            // 4. Return hasil
            return [
                'status' => true,
                'data' => $karyawan,
            ];
        });
    }

    public function pindahKaryawan(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $old = Karyawan::find($id);
            if (! $old) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if ($old->tanggal_selesai) {
                return [
                    'status' => false,
                    'message' => 'Data karyawan sudah memiliki tanggal selesai, tidak dapat diganti.',
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

            // Tutup jabatan lama
            $old->update([
                'status_aktif' => 'tidak aktif',
                'tanggal_selesai' => $hariIni,
                'updated_by' => Auth::id(),
            ]);

            // Buat jabatan baru
            $new = Karyawan::create([
                'pegawai_id' => $old->pegawai_id,
                'golongan_jabatan_id' => $input['golongan_jabatan_id'],
                'lembaga_id' => $input['lembaga_id'],
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

    public function keluarKaryawan(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $karyawan = Karyawan::find($id);
            if (! $karyawan) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if ($karyawan->tanggal_selesai || $karyawan->status_aktif === 'tidak aktif') {
                return [
                    'status' => false,
                    'message' => 'Data karyawan sudah ditandai selesai/nonaktif.',
                ];
            }

            $tglSelesai = Carbon::parse($input['tanggal_selesai'] ?? '');
            if ($tglSelesai->lt(Carbon::parse($karyawan->tanggal_mulai))) {
                return [
                    'status' => false,
                    'message' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
                ];
            }

            $pegawaiId = $karyawan->pegawai_id;

            $masihAktif = (
                Pengurus::where('pegawai_id', $pegawaiId)
                ->where('status_aktif', 'aktif')
                ->whereNull('tanggal_akhir')
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

            $karyawan->update([
                'status_aktif'    => 'tidak aktif',
                'tanggal_selesai' => $tglSelesai,
                'updated_by'      => Auth::id(),
            ]);

            if (! $masihAktif) {
                Pegawai::where('id', $pegawaiId)->update([
                    'status_aktif'    => 'tidak aktif',
                    'updated_by'      => Auth::id(),
                ]);

                // AnakPegawai::where('pegawai_id', $pegawaiId)->update([
                //     'status' => false,
                //     'updated_by'   => Auth::id(),
                //     'updated_at'   => now(),
                // ]);
            }

            return [
                'status' => true,
                'data'   => $karyawan,
            ];
        });
    }
}
