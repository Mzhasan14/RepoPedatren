<?php

namespace App\Services\Pegawai\Filters\Formulir;

use App\Models\Pegawai\MateriAjar;
use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\Pengajar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengajarService
{
    public function index(string $bioId): array
    {
        $pengajar = Pengajar::whereHas('pegawai.biodata', fn ($query) => $query->where('id', $bioId))
            ->with(['materiAjar', 'lembaga', 'golongan'])
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nama_lembaga' => optional($p->lembaga)->nama_lembaga,
                'nama_golongan' => optional($p->golongan)->nama_golongan,
                'jabatan_kontrak' => $p->jabatan,
                'tanggal_masuk' => $p->tahun_masuk,
                'tanggal_keluar' => $p->tahun_akhir,
                'status' => $p->status_aktif,
                'nama_materi' => $p->materiAjar->pluck('nama_materi')->join(', '),
                'jumlah_menit' => $p->materiAjar->sum('jumlah_menit'),
                'status_aktif' => $p->status_aktif,
            ]);

        return [
            'status' => true,
            'data' => $pengajar,
        ];
    }

    public function show($id): array
    {
        $pengajar = Pengajar::with(['materiAjar'])
            ->find($id);

        if (! $pengajar) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        $materi = $pengajar->materiAjar->map(fn ($m) => [
            'id' => $m->id,
            'nama_materi' => $m->nama_materi,
            'jumlah_menit' => $m->jumlah_menit,
            'tahun_masuk' => $m->tahun_masuk,
            'tahun_akhir' => $m->tahun_akhir,
            'status_aktif' => $m->status_aktif,
        ]);

        return [
            'status' => true,
            'data' => [
                'id' => $pengajar->id,
                'lembaga_id' => $pengajar->lembaga_id,
                'golongan_id' => $pengajar->golongan_id,
                'jabatan_kontrak' => $pengajar->jabatan,
                'tanggal_masuk' => $pengajar->tahun_masuk,
                'tanggal_keluar' => $pengajar->tahun_akhir,
                'status_aktif' => $pengajar->status_aktif,
                'materi' => $materi,
            ],
        ];
    }

    public function update(array $input, string $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $pengajar = Pengajar::with('materiAjar')->find($id);
            if (! $pengajar) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            // Larangan update jika tahun_akhir sudah ada dan status aktif 'tidak aktif'
            if (! is_null($pengajar->tahun_akhir) && $pengajar->status_aktif === 'tidak aktif') {
                return [
                    'status' => false,
                    'message' => 'Data pengajar ini telah memiliki tahun akhir dan statusnya tidak aktif, tidak dapat diubah lagi demi menjaga keakuratan histori.',
                ];
            }

            // Update data pengajar biasa
            $pengajar->update([
                'golongan_id' => $input['golongan_id'],
                'lembaga_id' => $input['lembaga_id'],
                'jabatan' => $input['jabatan'] ?? $pengajar->jabatan,
                'tahun_masuk' => Carbon::parse($input['tahun_masuk']) ?? now(),
                'updated_by' => Auth::id(),
            ]);

            return [
                'status' => true,
                'data' => $pengajar,
            ];
        });
    }

    public function store(array $data, string $bioId): array
    {
        // 1. Periksa apakah Pegawai sudah memiliki pengajar aktif
        $exist = Pengajar::whereHas('pegawai', fn ($q) => $q->where('biodata_id', $bioId))
            ->where('status_aktif', 'aktif')
            ->first();

        if ($exist) {
            return [
                'status' => false,
                'message' => 'Pegawai masih memiliki Pengajar aktif',
            ];
        }

        // 2. Cari Pegawai berdasarkan biodata_id
        $pegawai = Pegawai::where('biodata_id', $bioId)
            ->latest()
            ->first();

        if (! $pegawai) {
            return [
                'status' => false,
                'message' => 'Pegawai tidak ditemukan untuk biodata ini',
            ];
        }

        // 3. Buat Pengajar Baru dalam transaction
        return DB::transaction(function () use ($data, $pegawai) {
            $pengajar = Pengajar::create([
                'pegawai_id' => $pegawai->id,
                'golongan_id' => $data['golongan_id'],
                'lembaga_id' => $data['lembaga_id'],
                'jabatan' => $data['jabatan'],
                'tahun_masuk' => $data['tahun_masuk'] ?? now(),
                'status_aktif' => 'aktif',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Tambahkan materi ajar jika ada
            if (! empty($data['nama_materi'])) {
                if (is_array($data['nama_materi'])) {
                    foreach ($data['nama_materi'] as $index => $nama) {
                        MateriAjar::create([
                            'pengajar_id' => $pengajar->id,
                            'nama_materi' => $nama,
                            'jumlah_menit' => $data['jumlah_menit'][$index] ?? 0,
                            'tahun_masuk' => now(),
                            'status_aktif' => 'aktif',
                            'created_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } else {
                    MateriAjar::create([
                        'pengajar_id' => $pengajar->id,
                        'nama_materi' => $data['nama_materi'],
                        'jumlah_menit' => $data['jumlah_menit'] ?? 0,
                        'tahun_masuk' => now(),
                        'status_aktif' => 'aktif',
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 5. Return response
            return [
                'status' => true,
                'data' => $pengajar->fresh()->load('materiAjar'),
            ];
        });
    }

    public function pindahPengajar(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $old = Pengajar::with('materiAjar')->find($id);
            if (! $old) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if ($old->tahun_akhir) {
                return [
                    'status' => false,
                    'message' => 'Data pengajar sudah memiliki tahun akhir, tidak dapat diganti.',
                ];
            }

            $tahunMasukBaru = Carbon::parse($input['tahun_masuk'] ?? '');
            $hariIni = Carbon::now();

            if ($tahunMasukBaru->lt($hariIni)) {
                return [
                    'status' => false,
                    'message' => 'Tahun masuk baru tidak boleh sebelum hari ini.',
                ];
            }

            // Nonaktifkan semua materi ajar lama
            foreach ($old->materiAjar as $materi) {
                $materi->update([
                    'status_aktif' => 'tidak aktif',
                    'tahun_akhir' => $hariIni,
                    'updated_by' => Auth::id(),
                ]);
            }

            // Tutup pengajar lama
            $old->update([
                'status_aktif' => 'tidak aktif',
                'tahun_akhir' => $hariIni,
                'updated_by' => Auth::id(),
            ]);

            // Buat pengajar baru
            $new = Pengajar::create([
                'pegawai_id' => $old->pegawai_id,
                'golongan_id' => $input['golongan_id'],
                'lembaga_id' => $input['lembaga_id'],
                'jabatan' => $input['jabatan'] ?? $old->jabatan,
                'tahun_masuk' => $tahunMasukBaru,
                'status_aktif' => 'aktif',
                'created_by' => Auth::id(),
            ]);

            // Buat materi ajar baru dari input
            if (! empty($input['materi_ajar']) && is_array($input['materi_ajar'])) {
                foreach ($input['materi_ajar'] as $materiBaru) {
                    $new->materiAjar()->create([
                        'nama_materi' => $materiBaru['nama_materi'],
                        'tahun_masuk' => $tahunMasukBaru,
                        'jumlah_menit' => $materiBaru['jumlah_menit'] ?? 0,
                        'status_aktif' => 'aktif',
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            return [
                'status' => true,
                'data' => $new->load('materiAjar'),
            ];
        });
    }

    public function keluarPengajar(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $pengajar = Pengajar::find($id);
            if (! $pengajar) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if ($pengajar->tahun_akhir) {
                return [
                    'status' => false,
                    'message' => 'Data pengajar sudah ditandai selesai/nonaktif.',
                ];
            }

            $tahunAkhir = Carbon::parse($input['tahun_akhir'] ?? '');
            if ($tahunAkhir->lt(Carbon::parse($pengajar->tahun_masuk))) {
                return [
                    'status' => false,
                    'message' => 'Tahun akhir tidak boleh sebelum tahun masuk.',
                ];
            }

            // Update status pengajar menjadi tidak aktif dan set tahun_akhir
            $pengajar->update([
                'status_aktif' => 'tidak aktif',
                'tahun_akhir' => $tahunAkhir,
                'updated_by' => Auth::id(),
            ]);

            // Nonaktifkan semua materi ajar terkait pengajar ini
            foreach ($pengajar->materiAjar as $materi) {
                $materi->update([
                    'status_aktif' => 'tidak aktif',
                    'tahun_akhir' => $tahunAkhir,
                    'updated_by' => Auth::id(),
                ]);
            }

            return [
                'status' => true,
                'data' => $pengajar->load('materiAjar'),
            ];
        });
    }

    public function nonaktifkan(string $pengajarId, string $materiId): array
    {
        $materi = MateriAjar::where('pengajar_id', $pengajarId)
            ->where('id', $materiId)
            ->first();

        if (! $materi) {
            return ['status' => false, 'message' => 'Materi tidak ditemukan.'];
        }

        if (! is_null($materi->tahun_akhir) && $materi->status_aktif === 'tidak aktif') {
            return ['status' => false, 'message' => 'Materi sudah nonaktif dan tidak bisa diubah.'];
        }

        $materi->update([
            'tahun_akhir' => now(),
            'status_aktif' => 'tidak aktif',
            'updated_by' => Auth::id(),
        ]);

        return ['status' => true, 'message' => 'Materi berhasil dinonaktifkan.', 'data' => $materi];
    }

    public function tambahMateri(string $pengajarId, array $input): array
    {
        $pengajar = Pengajar::find($pengajarId);

        if (! $pengajar) {
            return ['status' => false, 'message' => 'Pengajar tidak ditemukan.'];
        }

        if (empty($input['materi_ajar']) || ! is_array($input['materi_ajar'])) {
            return ['status' => false, 'message' => 'Data materi ajar tidak valid.'];
        }

        $tahunMasuk = $input['tahun_masuk'] ?? now();

        foreach ($input['materi_ajar'] as $materiBaru) {
            $pengajar->materiAjar()->create([
                'nama_materi' => $materiBaru['nama_materi'],
                'tahun_masuk' => $tahunMasuk,
                'jumlah_menit' => $materiBaru['jumlah_menit'] ?? 0,
                'status_aktif' => 'aktif',
                'created_by' => Auth::id(),
            ]);
        }

        return ['status' => true, 'message' => 'Materi ajar berhasil ditambahkan.', 'data' => $pengajar->load('materiAjar')];
    }
}
