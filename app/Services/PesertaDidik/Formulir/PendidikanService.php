<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\Santri;
use App\Models\Pendidikan;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\RiwayatPendidikan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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

        $gabungan = $gabungan->sortByDesc('created_at')->values();

        $data = $gabungan->map(function ($item) {
            return [
                'id'             => $item->id,
                'biodata_id'     => $item->biodata_id,
                'no_induk'       => $item->no_induk ?? null,
                'nama_lembaga'   => $item->lembaga->nama_lembaga ?? null,
                'nama_jurusan'   => $item->jurusan->nama_jurusan ?? null,
                'nama_kelas'     => $item->kelas->nama_kelas ?? null,
                'nama_rombel'    => $item->rombel->nama_rombel ?? null,
                'angkatan_id'    => $item->angkatan_id ?? null,
                'tanggal_masuk'  => $item->tanggal_masuk ?? null,
                'tanggal_keluar' => $item->tanggal_keluar ?? null,
                'status'         => $item->status ?? null,
            ];
         });

        return [
            'status' => true,
            'data'   => $data,
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

            $pendidikan = Pendidikan::create([
                'biodata_id'     => $bioId,
                'no_induk'       => $input['no_induk'] ?? null,
                'lembaga_id'     => $input['lembaga_id'],
                'jurusan_id'     => $input['jurusan_id'],
                'kelas_id'       => $input['kelas_id'],
                'rombel_id'      => $input['rombel_id'],
                'angkatan_id'    => $input['angkatan_id'] ?? null,
                'tanggal_masuk'  => isset($input['tanggal_masuk']) ? Carbon::parse($input['tanggal_masuk']) : now(),
                'status'         => $input['status'] ?? 'aktif',
                'created_by'     => Auth::id(),
            ]);

            return ['status' => true, 'data' => $pendidikan];
        });
    }

    public function show(int $id): array
    {
        $pendidikan = RiwayatPendidikan::with(['lembaga', 'jurusan', 'kelas', 'rombel', 'angkatan'])->find($id);
        $source = 'riwayat';

        if (! $pendidikan) {
            $pendidikan = Pendidikan::with(['lembaga', 'jurusan', 'kelas', 'rombel', 'angkatan'])->find($id);
            $source = 'aktif';
        }

        if (! $pendidikan) {
            return ['status' => false, 'message' => 'Data tidak ditemukan.'];
        }

        return [
            'status' => true,
            'data'   => [
                'id'             => $pendidikan->id,
                'biodata_id'     => $pendidikan->biodata_id,
                'no_induk'       => $pendidikan->no_induk ?? null,
                'nama_lembaga'   => $pendidikan->lembaga->nama_lembaga ?? '-',
                'nama_jurusan'   => $pendidikan->jurusan->nama_jurusan ?? '-',
                'nama_kelas'     => $pendidikan->kelas->nama_kelas ?? '-',
                'nama_rombel'    => $pendidikan->rombel->nama_rombel ?? '-',
                'nama_angkatan'  => $pendidikan->angkatan->nama_angkatan ?? '-',
                'tanggal_masuk'  => $pendidikan->tanggal_masuk,
                'tanggal_keluar' => $pendidikan->tanggal_keluar ?? ($source === 'riwayat' ? '-' : '-'),
                'status'         => $pendidikan->status,
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

            if (empty($input['tanggal_masuk']) || ! strtotime($input['tanggal_masuk'])) {
                return ['status' => false, 'message' => 'Tanggal masuk tidak valid.'];
            }

            $tglBaru = Carbon::parse($input['tanggal_masuk']);
            $today  = Carbon::now();

            if ($tglBaru->lt($today)) {
                return ['status' => false, 'message' => 'Tanggal masuk baru minimal hari ini.'];
            }

            // Arsipkan ke riwayat
            RiwayatPendidikan::create([
                'biodata_id'    => $aktif->biodata_id,
                'no_induk'      => $aktif->no_induk ?? null,
                'lembaga_id'    => $aktif->lembaga_id,
                'jurusan_id'    => $aktif->jurusan_id ?? null,
                'kelas_id'      => $aktif->kelas_id ?? null,
                'rombel_id'     => $aktif->rombel_id ?? null,
                'angkatan_id'   => $aktif->angkatan_id ?? null,
                'tanggal_masuk' => $aktif->tanggal_masuk,
                'tanggal_keluar' => $today,
                'status'        => 'pindah',
                'created_by'    => $aktif->created_by,
                'updated_by'    => Auth::id(),
            ]);

            // Update data aktif baru
            $aktif->update([
                'lembaga_id'     => $input['lembaga_id'],
                'jurusan_id'     => $input['jurusan_id'] ?? null,
                'kelas_id'       => $input['kelas_id'] ?? null,
                'rombel_id'      => $input['rombel_id'] ?? null,
                'tanggal_masuk'  => $tglBaru,
                'status'         => 'aktif',
                'updated_by'     => Auth::id(),
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
                'biodata_id'    => $aktif->biodata_id,
                'no_induk'      => $aktif->no_induk ?? null,
                'lembaga_id'    => $aktif->lembaga_id,
                'jurusan_id'    => $aktif->jurusan_id ?? null,
                'kelas_id'      => $aktif->kelas_id ?? null,
                'rombel_id'     => $aktif->rombel_id ?? null,
                'angkatan_id'   => $aktif->angkatan_id ?? null,
                'tanggal_masuk' => $aktif->tanggal_masuk,
                'tanggal_keluar' => $input['tanggal_keluar'],
                'status'        => $input['status'],
                'created_by'    => $aktif->created_by,
                'updated_by'    => Auth::id(),
            ]);

            // Hapus data aktif
            $aktif->delete();

            return ['status' => true, 'message' => 'Santri telah keluar dari pendidikan.'];
        });
    }

    public function update(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $pendidikan = Pendidikan::find($id);
            if (! $pendidikan) {
                return ['status' => false, 'message' => 'Data pendidikan aktif tidak ditemukan.'];
            }

            // Simpan data lama ke riwayat sebelum update
            RiwayatPendidikan::create([
                'biodata_id'    => $pendidikan->biodata_id,
                'no_induk'      => $pendidikan->no_induk ?? null,
                'lembaga_id'    => $pendidikan->lembaga_id,
                'jurusan_id'    => $pendidikan->jurusan_id ?? null,
                'kelas_id'      => $pendidikan->kelas_id ?? null,
                'rombel_id'     => $pendidikan->rombel_id ?? null,
                'angkatan_id'   => $pendidikan->angkatan_id ?? null,
                'tanggal_masuk' => $pendidikan->tanggal_masuk,
                'tanggal_keluar' => now(),
                'status'        => $pendidikan->status,
                'created_by'    => $pendidikan->updated_by ?? null,
                'created_at'    => $pendidikan->updated_at ?? now(),
            ]);

            // Update data aktif
            $pendidikan->update([
                'no_induk'       => $input['no_induk'] ?? $pendidikan->no_induk,
                'lembaga_id'     => $input['lembaga_id'],
                'jurusan_id'     => $input['jurusan_id'] ?? null,
                'kelas_id'       => $input['kelas_id'] ?? null,
                'rombel_id'      => $input['rombel_id'] ?? null,
                'angkatan_id'    => $input['angkatan_id'] ?? null,
                'tanggal_masuk'  => Carbon::parse($input['tanggal_masuk']),
                'status'         => $input['status'] ?? $pendidikan->status,
                'updated_by'     => Auth::id(),
            ]);

            return ['status' => true, 'data' => $pendidikan];
        });
    }
}
