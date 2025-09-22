<?php

namespace App\Services\Pegawai\Filters\Formulir;

use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\WaliKelas;
use App\Models\Santri;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WaliKelasService
{
    public function index(string $bioId): array
    {
        $waliKelas = WaliKelas::whereHas('pegawai.biodata', fn($q) => $q->where('id', $bioId))
            ->with(['pegawai.biodata', 'lembaga', 'jurusan', 'kelas', 'rombel'])
            ->orderBy('periode_awal', 'desc')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'Lembaga' => $item->lembaga->nama_lembaga ?? null,
                'Jurusan' => $item->jurusan->nama_jurusan ?? null,
                'Kelas' => $item->kelas->nama_kelas ?? null,
                'Rombel' => $item->rombel->nama_rombel ?? null,
                'jumlah_murid' => $item->jumlah_murid,
                'status_aktif' => $item->status_aktif,
                'Periode_awal' => $item->periode_awal,
                'Periode_akhir' => $item->periode_akhir,
            ]);

        return [
            'status' => true,
            'data' => $waliKelas,
        ];
    }

    public function show($id): array
    {
        $waliKelas = DB::table('wali_kelas as wk')
            ->leftJoin('pendidikan as pn', function ($join) {
                $join->on('pn.lembaga_id', '=', 'wk.lembaga_id')
                    ->on('pn.jurusan_id', '=', 'wk.jurusan_id')
                    ->on('pn.kelas_id', '=', 'wk.kelas_id')
                    ->on('pn.rombel_id', '=', 'wk.rombel_id')
                    ->on('pn.angkatan_id', '=', 'wk.angkatan_id');
            })
            ->where('wk.id', $id)
            ->select(
                'wk.id',
                'wk.lembaga_id',
                'wk.jurusan_id',
                'wk.kelas_id',
                'wk.rombel_id',
                'wk.angkatan_id',
                'wk.status_aktif',
                'wk.periode_awal',
                'wk.periode_akhir',
                DB::raw("
                TRIM(BOTH ', ' FROM CONCAT_WS(', ',
                    NULLIF(CONCAT(SUM(CASE WHEN pn.status = 'aktif' THEN 1 ELSE 0 END), ' murid aktif'), '0 murid aktif'),
                    NULLIF(CONCAT(SUM(CASE WHEN pn.status = 'lulus' THEN 1 ELSE 0 END), ' murid lulus'), '0 murid lulus'),
                    NULLIF(CONCAT(SUM(CASE WHEN pn.status = 'do' THEN 1 ELSE 0 END), ' murid DO'), '0 murid DO'),
                    NULLIF(CONCAT(SUM(CASE WHEN pn.status = 'berhenti' THEN 1 ELSE 0 END), ' murid berhenti'), '0 murid berhenti')
                )
            ) AS jumlah_murid")
            )
            ->groupBy(
                'wk.id',
                'wk.lembaga_id',
                'wk.jurusan_id',
                'wk.kelas_id',
                'wk.rombel_id',
                'wk.angkatan_id',
                'wk.status_aktif',
                'wk.periode_awal',
                'wk.periode_akhir'
            )
            ->first();

        if (! $waliKelas) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        return [
            'status' => true,
            'data' => $waliKelas,
        ];
    }

    public function store(array $data, string $bioId): array
    {
        // 1. Validasi: cek apakah masih ada santri aktif untuk biodata ini
        $santriAktif = Santri::where('biodata_id', $bioId)
            ->where('status', 'aktif')
            ->first();

        if ($santriAktif) {
            return [
                'status' => false,
                'message' => 'Data masih terdaftar sebagai Santri aktif. Tidak bisa menjadi Wali Kelas.',
            ];
        }

        // 2. Cek apakah sudah ada wali kelas aktif untuk biodata ini
        $exist = WaliKelas::whereHas('pegawai', fn($q) => $q->where('biodata_id', $bioId))
            ->where('status_aktif', 'aktif')
            ->first();

        if ($exist) {
            return [
                'status' => false,
                'message' => 'Pegawai masih memiliki Wali Kelas aktif.',
            ];
        }

        // 3. Cari pegawai berdasarkan biodata
        $pegawai = Pegawai::where('biodata_id', $bioId)->latest()->first();

        if (! $pegawai) {
            return [
                'status' => false,
                'message' => 'Pegawai tidak ditemukan untuk biodata ini.',
            ];
        }

        // 4. Buat Wali Kelas baru
        $waliKelas = WaliKelas::create([
            'pegawai_id'    => $pegawai->id,
            'lembaga_id'    => $data['lembaga_id'] ?? null,
            'jurusan_id'    => $data['jurusan_id'] ?? null,
            'kelas_id'      => $data['kelas_id'] ?? null,
            'rombel_id'     => $data['rombel_id'] ?? null,
            'jumlah_murid'  => $data['jumlah_murid'],
            'periode_awal'  => $data['periode_awal'] ?? now(),
            'status_aktif'  => 'aktif',
            'created_by'    => Auth::id(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return [
            'status' => true,
            'data' => $waliKelas->fresh(),
        ];
    }

    public function update(array $input, string $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $wali = WaliKelas::find($id);

            if (! $wali) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            // Tidak boleh update jika sudah ada tanggal akhir
            if (! is_null($wali->periode_akhir) && $wali->status_aktif === 'tidak aktif') {
                return [
                    'status' => false,
                    'message' => 'Data Wali Kelas ini sudah berakhir dan statusnya tidak aktif, tidak dapat diubah lagi untuk menjaga keakuratan histori.',
                ];
            }

            $wali->update([
                'lembaga_id' => $input['lembaga_id'] ?? $wali->lembaga_id,
                'jurusan_id' => $input['jurusan_id'] ?? $wali->jurusan_id,
                'kelas_id' => $input['kelas_id'] ?? $wali->kelas_id,
                'rombel_id' => $input['rombel_id'] ?? $wali->rombel_id,
                'jumlah_murid' => $input['jumlah_murid'] ?? $wali->jumlah_murid,
                'periode_awal' => isset($input['periode_awal']) ? Carbon::parse($input['periode_awal']) : $wali->periode_awal,
                'updated_by' => Auth::id(),
            ]);

            return [
                'status' => true,
                'data' => $wali,
            ];
        });
    }

    public function pindahWaliKelas(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $old = WaliKelas::find($id);
            if (! $old) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if ($old->periode_akhir) {
                return [
                    'status' => false,
                    'message' => 'Data wali kelas sudah memiliki periode akhir, tidak dapat diganti.',
                ];
            }

            $tanggalMulaiBaru = Carbon::parse($input['periode_awal'] ?? '');
            $hariIni = Carbon::now();

            if ($tanggalMulaiBaru->lt($hariIni)) {
                return [
                    'status' => false,
                    'message' => 'Periode awal baru tidak boleh sebelum hari ini.',
                ];
            }

            $old->update([
                'status_aktif' => 'tidak aktif',
                'periode_akhir' => $hariIni,
                'updated_by' => Auth::id(),
            ]);

            $new = WaliKelas::create([
                'pegawai_id' => $old->pegawai_id,
                'lembaga_id' => $input['lembaga_id'] ?? null,
                'jurusan_id' => $input['jurusan_id'] ?? null,
                'kelas_id' => $input['kelas_id'] ?? null,
                'rombel_id' => $input['rombel_id'] ?? null,
                'jumlah_murid' => $input['jumlah_murid'] ?? '0',
                'periode_awal' => $tanggalMulaiBaru,
                'status_aktif' => 'aktif',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'status' => true,
                'data' => $new,
            ];
        });
    }

    public function keluarWaliKelas(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $wali = WaliKelas::find($id);
            if (! $wali) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if ($wali->periode_akhir) {
                return [
                    'status' => false,
                    'message' => 'Data wali kelas sudah ditandai selesai.',
                ];
            }

            $periodeAkhir = Carbon::parse($input['periode_akhir'] ?? '');
            if ($periodeAkhir->lt(Carbon::parse($wali->periode_awal))) {
                return [
                    'status' => false,
                    'message' => 'Periode akhir tidak boleh sebelum periode awal.',
                ];
            }

            $wali->update([
                'status_aktif' => 'tidak aktif',
                'periode_akhir' => $periodeAkhir,
                'updated_by' => Auth::id(),
            ]);

            return [
                'status' => true,
                'data' => $wali,
            ];
        });
    }
}
