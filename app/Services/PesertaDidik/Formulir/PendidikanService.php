<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\Biodata;
use App\Models\Pendidikan;
use Illuminate\Support\Carbon;
use App\Models\Pendidikan\Rombel;
use App\Models\RiwayatPendidikan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PendidikanService
{
    public function index(string $bioId): array
    {
        $riwayat = RiwayatPendidikan::with([
            'lembaga:id,nama_lembaga',
            'jurusan:id,nama_jurusan',
            'kelas:id,nama_kelas',
            'rombel:id,nama_rombel',
        ])
            ->where('biodata_id', $bioId)
            ->get();

        $aktif = Pendidikan::with([
            'lembaga:id,nama_lembaga',
            'jurusan:id,nama_jurusan',
            'kelas:id,nama_kelas',
            'rombel:id,nama_rombel',
        ])
            ->where('biodata_id', $bioId)
            ->where('status', 'aktif')
            ->first();

        $gabungan = collect($riwayat);
        if ($aktif) {
            $gabungan->push($aktif);
        }

        $gabungan = $gabungan->sortByDesc('tanggal_masuk')->values();

        $data = $gabungan->map(function ($item) {
            return [
                'id' => $item->id,
                'biodata_id' => $item->biodata_id,
                'no_induk' => $item->no_induk ?? null,
                'nama_lembaga' => $item->lembaga->nama_lembaga ?? null,
                'nama_jurusan' => $item->jurusan->nama_jurusan ?? null,
                'nama_kelas' => $item->kelas->nama_kelas ?? null,
                'nama_rombel' => $item->rombel->nama_rombel ?? null,
                'angkatan_id' => $item->angkatan_id ?? null,
                'tanggal_masuk' => $item->tanggal_masuk ?? null,
                'tanggal_keluar' => $item->tanggal_keluar ?? null,
                'status' => $item->status ?? null,
            ];
        });

        return [
            'status' => true,
            'data' => $data,
        ];
    }

    public function store(array $input, string $bioId): array
    {
        return DB::transaction(function () use ($input, $bioId) {
            $pendidikanAktif = Pendidikan::where('biodata_id', $bioId)
                ->where('status', 'aktif')
                ->exists();

            if ($pendidikanAktif) {
                return ['status' => false, 'message' => 'Data ini sudah memiliki pendidikan aktif.'];
            }

            // CEK KEC SESUAIAN JENIS KELAMIN DAN ROMBEL BARU
            $biodata = Biodata::find($bioId);
            $rombel = Rombel::find($input['rombel_id'] ?? null);

            if ($biodata && $rombel && $rombel->gender_rombel) {
                $genderSantri = ($biodata->jenis_kelamin == 'l') ? 'putra' : 'putri';
                if ($rombel->gender_rombel !== $genderSantri) {
                    return [
                        'status' => false,
                        'message' => 'Rombel yang dipilih hanya untuk ' . $rombel->gender_rombel . '. Data santri saat ini adalah ' . $genderSantri . '. Silakan pilih rombel yang sesuai.',
                    ];
                }
            }

            $tanggalMasuk = $input['tanggal_masuk'] ? Carbon::parse($input['tanggal_masuk']) : now();

            // Ambil tanggal terakhir dari riwayat, jika ada
            $riwayatTerakhir = RiwayatPendidikan::where('biodata_id', $bioId)
                ->orderByDesc('tanggal_masuk')
                ->first();

            if ($riwayatTerakhir && $tanggalMasuk->lt(Carbon::parse($riwayatTerakhir->tanggal_masuk))) {
                return [
                    'status' => false,
                    'message' => 'Tanggal masuk tidak boleh lebih awal dari riwayat pendidikan terakhir (' . $riwayatTerakhir->tanggal_masuk->format('Y-m-d') . '). Harap periksa kembali tanggal yang Anda input.',
                ];
            }

            $pendidikan = Pendidikan::create([
                'biodata_id' => $bioId,
                'no_induk' => $input['no_induk'] ?? null,
                'lembaga_id' => $input['lembaga_id'],
                'jurusan_id' => $input['jurusan_id'],
                'kelas_id' => $input['kelas_id'],
                'rombel_id' => $input['rombel_id'],
                'angkatan_id' => $input['angkatan_id'] ?? null,
                'tanggal_masuk' => isset($input['tanggal_masuk']) ? Carbon::parse($input['tanggal_masuk']) : now(),
                'status' => $input['status'] ?? 'aktif',
                'created_by' => Auth::id(),
            ]);

            return ['status' => true, 'data' => $pendidikan];
        });
    }

    public function show(int $id): array
    {
        $pendidikan = RiwayatPendidikan::find($id);
        $source = 'riwayat';

        if (! $pendidikan) {
            $pendidikan = Pendidikan::find($id);
            $source = 'aktif';
        }

        if (! $pendidikan) {
            return ['status' => false, 'message' => 'Data tidak ditemukan.'];
        }

        return [
            'status' => true,
            'data' => [
                'id' => $pendidikan->id,
                'biodata_id' => $pendidikan->biodata_id,
                'no_induk' => $pendidikan->no_induk ?? null,
                'nama_lembaga' => $pendidikan->lembaga_id ?? '-',
                'nama_jurusan' => $pendidikan->jurusan_id ?? '-',
                'nama_kelas' => $pendidikan->kelas_id ?? '-',
                'nama_rombel' => $pendidikan->rombel_id ?? '-',
                'nama_angkatan' => $pendidikan->angkatan_id ?? '-',
                'tanggal_masuk' => $pendidikan->tanggal_masuk,
                'tanggal_keluar' => $pendidikan->tanggal_keluar ?? ($source === 'riwayat' ? '-' : '-'),
                'status' => $pendidikan->status,
            ],
        ];
    }

    public function pindahPendidikan(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $aktif = Pendidikan::find($id);
            if (! $aktif) {
                return ['status' => false, 'message' => 'Data pendidikan aktif tidak ditemukan.'];
            }

            if ($aktif->tanggal_keluar) {
                return ['status' => false, 'message' => 'Riwayat sudah ditutup.'];
            }

            // CEK KEC SESUAIAN JENIS KELAMIN DAN ROMBEL BARU
            $biodata = Biodata::find($aktif->biodata_id);
            $rombelBaru = Rombel::find($input['rombel_id'] ?? null);

            if ($biodata && $rombelBaru && $rombelBaru->gender_rombel) {
                $genderSantri = ($biodata->jenis_kelamin == 'l') ? 'putra' : 'putri';
                if ($rombelBaru->gender_rombel !== $genderSantri) {
                    return [
                        'status' => false,
                        'message' => 'Rombel yang dipilih hanya untuk ' . $rombelBaru->gender_rombel . '. Data santri saat ini adalah ' . $genderSantri . '. Silakan pilih rombel yang sesuai.',
                    ];
                }
            }

            if (empty($input['tanggal_masuk']) || ! strtotime($input['tanggal_masuk'])) {
                return ['status' => false, 'message' => 'Tanggal masuk tidak valid.'];
            }

            $today = Carbon::now();
            $tanggalBaru = Carbon::parse($input['tanggal_masuk']);
            $tanggalLama = Carbon::parse($aktif->tanggal_masuk);

            if ($tanggalBaru->lt($tanggalLama)) {
                return [
                    'status' => false,
                    'message' => 'Tanggal masuk baru tidak boleh lebih awal dari tanggal masuk sebelumnya (' . $tanggalLama->format('Y-m-d') . '). Silakan periksa kembali tanggal yang Anda input.',
                ];
            }

            // Arsipkan ke riwayat
            RiwayatPendidikan::create([
                'biodata_id' => $aktif->biodata_id,
                'no_induk' => $aktif->no_induk ?? null,
                'lembaga_id' => $aktif->lembaga_id,
                'jurusan_id' => $aktif->jurusan_id ?? null,
                'kelas_id' => $aktif->kelas_id ?? null,
                'rombel_id' => $aktif->rombel_id ?? null,
                'angkatan_id' => $aktif->angkatan_id ?? null,
                'tanggal_masuk' => $aktif->tanggal_masuk,
                'tanggal_keluar' => $today,
                'status' => 'pindah',
                'created_by' => $aktif->created_by,
            ]);

            // Update data aktif baru
            $aktif->update([
                'lembaga_id' => $input['lembaga_id'],
                'jurusan_id' => $input['jurusan_id'] ?? null,
                'kelas_id' => $input['kelas_id'] ?? null,
                'rombel_id' => $input['rombel_id'] ?? null,
                'tanggal_masuk' => $tanggalBaru,
                'status' => 'aktif',
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            return ['status' => true, 'data' => $aktif];
        });
    }

    public function keluarPendidikan(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $aktif = Pendidikan::find($id);
            if (! $aktif) {
                return ['status' => false, 'message' => 'Data pendidikan aktif tidak ditemukan.'];
            }

            if ($aktif->tanggal_keluar) {
                return ['status' => false, 'message' => 'Riwayat sudah ditutup.'];
            }

            if (empty($input['tanggal_keluar']) || ! strtotime($input['tanggal_keluar'])) {
                return ['status' => false, 'message' => 'Tanggal keluar tidak valid.'];
            }

            $tglKeluar = Carbon::parse($input['tanggal_keluar']);
            if ($tglKeluar->lt(Carbon::parse($aktif->tanggal_masuk))) {
                return ['status' => false, 'message' => 'Tanggal keluar tidak boleh sebelum tanggal masuk.'];
            }

            // Simpan ke riwayat
            RiwayatPendidikan::create([
                'biodata_id' => $aktif->biodata_id,
                'no_induk' => $aktif->no_induk ?? null,
                'lembaga_id' => $aktif->lembaga_id,
                'jurusan_id' => $aktif->jurusan_id ?? null,
                'kelas_id' => $aktif->kelas_id ?? null,
                'rombel_id' => $aktif->rombel_id ?? null,
                'angkatan_id' => $aktif->angkatan_id ?? null,
                'tanggal_masuk' => $aktif->tanggal_masuk,
                'tanggal_keluar' => $tglKeluar,
                'status' => $input['status'],
                'created_by' => $aktif->created_by,
            ]);

            // update data aktif
            $aktif->update([
                'status' => $input['status'],
                'tanggal_keluar' => $input['tanggal_keluar'],
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            return ['status' => true, 'message' => 'Pelajar telah keluar dari pendidikan.'];
        });
    }

    public function update(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $pendidikan = Pendidikan::find($id);
            if (! $pendidikan) {
                return ['status' => false, 'message' => 'Data pendidikan aktif tidak ditemukan.'];
            }

            // Ambil tanggal masuk dan keluar dari input atau fallback ke nilai lama
            $tanggalBaru = Carbon::parse($input['tanggal_masuk']);
            $tanggalLama = Carbon::parse($pendidikan->tanggal_masuk);

            if ($tanggalBaru->lt($tanggalLama)) {
                return [
                    'status' => false,
                    'message' => 'Tanggal masuk baru tidak boleh lebih awal dari tanggal masuk sebelumnya (' . $tanggalLama->format('Y-m-d') . '). Silakan periksa kembali tanggal yang Anda input.',
                ];
            }

            // Ambil status dari input atau dari database
            $status = $input['status'] ?? $pendidikan->status;

            // Validasi: jika status 'aktif', tanggal_keluar tidak boleh diisi
            if (strtolower($status) === 'aktif' && isset($input['tanggal_keluar'])) {
                return [
                    'status' => false,
                    'message' => 'Tanggal keluar tidak boleh diisi jika status santri masih aktif.',
                ];
            }

            // --- VALIDASI JENIS KELAMIN DAN ROMBEL ---
            $biodata = $pendidikan->biodata ?? Biodata::find($pendidikan->biodata_id);
            $rombelBaru = Rombel::find($input['rombel_id'] ?? $pendidikan->rombel_id);

            if ($biodata && $rombelBaru && $rombelBaru->gender_rombel) {
                $genderSantri = ($biodata->jenis_kelamin == 'l') ? 'putra' : 'putri';
                if ($rombelBaru->gender_rombel !== $genderSantri) {
                    return [
                        'status' => false,
                        'message' => 'Rombel yang dipilih hanya untuk ' . $rombelBaru->gender_rombel . '. Data santri saat ini adalah ' . $genderSantri . '. Silakan pilih rombel yang sesuai.',
                    ];
                }
            }

            // Update data aktif
            $pendidikan->update([
                'no_induk' => $input['no_induk'] ?? $pendidikan->no_induk,
                'lembaga_id' => $input['lembaga_id'],
                'jurusan_id' => $input['jurusan_id'] ?? null,
                'kelas_id' => $input['kelas_id'] ?? null,
                'rombel_id' => $input['rombel_id'] ?? null,
                'angkatan_id' => $input['angkatan_id'] ?? null,
                'status' => $status,
                'tanggal_masuk' => Carbon::parse($input['tanggal_masuk']),
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            return ['status' => true, 'data' => $pendidikan];
        });
    }
}
