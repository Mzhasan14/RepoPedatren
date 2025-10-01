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
        // Ambil wali kelas dulu
        $waliKelas = DB::table('wali_kelas')->where('id', $id)->first();

        if (! $waliKelas) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        // Function helper untuk null-safe comparison (tanpa angkatan)
        $matchCondition = function ($query, $prefix, $waliKelas) {
            $query->where(function ($q) use ($waliKelas, $prefix) {
                $q->where("{$prefix}.kelas_id", $waliKelas->kelas_id)
                    ->orWhere(function ($sub) use ($waliKelas, $prefix) {
                        if (is_null($waliKelas->kelas_id)) {
                            $sub->whereNull("{$prefix}.kelas_id");
                        }
                    });
            })->where(function ($q) use ($waliKelas, $prefix) {
                $q->where("{$prefix}.jurusan_id", $waliKelas->jurusan_id)
                    ->orWhere(function ($sub) use ($waliKelas, $prefix) {
                        if (is_null($waliKelas->jurusan_id)) {
                            $sub->whereNull("{$prefix}.jurusan_id");
                        }
                    });
            })->where(function ($q) use ($waliKelas, $prefix) {
                $q->where("{$prefix}.rombel_id", $waliKelas->rombel_id)
                    ->orWhere(function ($sub) use ($waliKelas, $prefix) {
                        if (is_null($waliKelas->rombel_id)) {
                            $sub->whereNull("{$prefix}.rombel_id");
                        }
                    });
            });
        };

        // Query untuk murid sesuai status wali kelas
        if ($waliKelas->status_aktif === 'aktif') {
            // Ambil dari pendidikan (hanya murid aktif)
            $murid = DB::table('pendidikan as pn')
                ->where('pn.status', 'aktif')
                ->where(function ($q) use ($waliKelas, $matchCondition) {
                    $matchCondition($q, 'pn', $waliKelas);
                })
                ->select(DB::raw("
                CONCAT(COUNT(*), ' murid aktif') AS jumlah_murid
            "))
                ->first();
        } else {
            // Ambil dari riwayat_pendidikan (semua status untuk histori)
            $murid = DB::table('riwayat_pendidikan as rp')
                ->where(function ($q) use ($waliKelas, $matchCondition) {
                    $matchCondition($q, 'rp', $waliKelas);
                })
                ->select(DB::raw("
                TRIM(BOTH ', ' FROM CONCAT_WS(', ',
                    NULLIF(CONCAT(SUM(CASE WHEN rp.status = 'lulus' THEN 1 ELSE 0 END), ' murid lulus'), '0 murid lulus'),
                    NULLIF(CONCAT(SUM(CASE WHEN rp.status = 'do' THEN 1 ELSE 0 END), ' murid DO'), '0 murid DO'),
                    NULLIF(CONCAT(SUM(CASE WHEN rp.status = 'berhenti' THEN 1 ELSE 0 END), ' murid berhenti'), '0 murid berhenti'),
                    NULLIF(CONCAT(SUM(CASE WHEN rp.status = 'selesai' THEN 1 ELSE 0 END), ' murid selesai'), '0 murid selesai'),
                    NULLIF(CONCAT(SUM(CASE WHEN rp.status = 'pindah' THEN 1 ELSE 0 END), ' murid pindah'), '0 murid pindah'),
                    NULLIF(CONCAT(SUM(CASE WHEN rp.status = 'batal_lulus' THEN 1 ELSE 0 END), ' murid batal lulus'), '0 murid batal lulus'),
                    NULLIF(CONCAT(SUM(CASE WHEN rp.status = 'nonaktif' THEN 1 ELSE 0 END), ' murid nonaktif'), '0 murid nonaktif')
                )
            ) AS jumlah_murid
            "))
                ->first();
        }

        return [
            'status' => true,
            'data' => array_merge((array) $waliKelas, (array) $murid),
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
            // 'jumlah_murid'  => $data['jumlah_murid'],
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
                // 'jumlah_murid' => $input['jumlah_murid'] ?? $wali->jumlah_murid,
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
                // 'jumlah_murid' => $input['jumlah_murid'] ?? '0',
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

            if ($wali->periode_akhir || $wali->status_aktif === 'tidak aktif') {
                return [
                    'status' => false,
                    'message' => 'Data wali kelas sudah ditandai selesai/nonaktif.',
                ];
            }

            $periodeAkhir = Carbon::parse($input['periode_akhir'] ?? '');
            if ($periodeAkhir->lt(Carbon::parse($wali->periode_awal))) {
                return [
                    'status' => false,
                    'message' => 'Periode akhir tidak boleh sebelum periode awal.',
                ];
            }

            $pegawaiId = $wali->pegawai_id;

            $masihAktif = (
                Karyawan::where('pegawai_id', $pegawaiId)
                ->where('status_aktif', 'aktif')
                ->whereNull('tanggal_selesai')
                ->exists() ||

                Pengajar::where('pegawai_id', $pegawaiId)
                ->where('status_aktif', 'aktif')
                ->whereNull('tahun_akhir')
                ->exists() ||

                Pengurus::where('pegawai_id', $pegawaiId)
                ->where('status_aktif', 'aktif')
                ->whereNull('tanggal_akhir')
                ->exists()
            );

            $wali->update([
                'status_aktif'  => 'tidak aktif',
                'periode_akhir' => $periodeAkhir,
                'updated_by'    => Auth::id(),
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
                'data'   => $wali,
            ];
        });
    }
}
