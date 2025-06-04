<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\Santri;
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
        $collection = RiwayatPendidikan::with([
            'lembaga:id,nama_lembaga',
            'jurusan:id,nama_jurusan',
            'kelas:id,nama_kelas',
            'rombel:id,nama_rombel',
        ])
            ->where('biodata_id', $bioId)
            ->get();

        $data = $collection->map(fn(RiwayatPendidikan $rp) => [
            'id'             => $rp->id,
            'no_induk'       => $rp->no_induk,
            'nama_lembaga'   => $rp->lembaga->nama_lembaga ?? null,
            'nama_jurusan'   => $rp->jurusan->nama_jurusan ?? null,
            'nama_kelas'     => $rp->kelas->nama_kelas ?? null,
            'nama_rombel'    => $rp->rombel->nama_rombel ?? null,
            'angkatan_id'    => $rp->angkatan_id,
            'tanggal_masuk'  => $rp->tanggal_masuk,
            'tanggal_keluar' => $rp->tanggal_keluar,
            'status'         => $rp->status,
        ]);

        return ['status' => true, 'data' => $data];
    }


    public function store(array $input, string $bioId): array
    {
        return DB::transaction(function () use ($input, $bioId) {
            // Cek duplikasi pendidikan aktif
            if (RiwayatPendidikan::where('biodata_id', $bioId)
                ->where('status', 'aktif')
                ->exists()
            ) {
                return ['status' => false, 'message' => 'Data ini sudah memiliki pendidikan aktif.'];
            }

            $rp = RiwayatPendidikan::create([
                'biodata_id'     => $bioId,
                'no_induk'       => $input['no_induk'] ?? null,
                'lembaga_id'     => $input['lembaga_id'],
                'jurusan_id'     => $input['jurusan_id'],
                'kelas_id'       => $input['kelas_id'],
                'rombel_id'      => $input['rombel_id'],
                'angkatan_id'    => $input['angkatan_id'] ?? null,
                'tanggal_masuk'  => isset($input['tanggal_masuk'])
                    ? Carbon::parse($input['tanggal_masuk'])
                    : Carbon::now(),
                'status'         => 'aktif',
                'created_by'     => Auth::id(),
            ]);

            return ['status' => true, 'data' => $rp];
        });
    }

    public function show(int $id): array
    {
        $rp = RiwayatPendidikan::with(['lembaga', 'jurusan', 'kelas', 'rombel', 'angkatan'])->find($id);
        if (! $rp) {
            return ['status' => false, 'message' => 'Data tidak ditemukan.'];
        }

        return [
            'status' => true,
            'data'   => [
                'id'             => $rp->id,
                'no_induk'       => $rp->no_induk,
                'nama_lembaga'   => $rp->lembaga->nama_lembaga ?? null,
                'nama_jurusan'   => $rp->jurusan->nama_jurusan ?? null,
                'nama_kelas'     => $rp->kelas->nama_kelas ?? null,
                'nama_rombel'    => $rp->rombel->nama_rombel ?? null,
                'nama_angkatan'  => $rp->angkatan->nama_angkatan ?? null,
                'tanggal_masuk'  => $rp->tanggal_masuk,
                'tanggal_keluar' => $rp->tanggal_keluar,
                'status'         => $rp->status,
            ],
        ];
    }

    public function pindahPendidikan(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $old = RiwayatPendidikan::find($id);
            if (! $old) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if ($old->tanggal_keluar) {
                return ['status' => false, 'message' => 'Riwayat sudah ditutup.'];
            }

            if (empty($input['tanggal_masuk']) || ! strtotime($input['tanggal_masuk'])) {
                return ['status' => false, 'message' => 'Tanggal masuk tidak valid.'];
            }

            $tglBaru = Carbon::parse($input['tanggal_masuk']);
            $today   = Carbon::now();
            if ($tglBaru->lt($today)) {
                return ['status' => false, 'message' => 'Tanggal masuk baru minimal hari ini.'];
            }

            $old->update([
                'status'         => 'pindah',
                'tanggal_keluar' => $today,
                'updated_by'     => Auth::id(),
            ]);

            $new = RiwayatPendidikan::create([
                'biodata_id'     => $old->biodata_id,
                'no_induk'       => $old->no_induk,
                'lembaga_id'     => $input['lembaga_id'],
                'jurusan_id'     => $input['jurusan_id'],
                'kelas_id'       => $input['kelas_id'],
                'rombel_id'      => $input['rombel_id'],
                'tanggal_masuk'  => $tglBaru,
                'status'         => 'aktif',
                'created_by'     => Auth::id(),
            ]);

            return ['status' => true, 'data' => $new];
        });
    }

    public function keluarPendidikan(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $rp = RiwayatPendidikan::find($id);
            if (! $rp) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if ($rp->tanggal_keluar) {
                return ['status' => false, 'message' => 'Riwayat sudah ditutup.'];
            }

            if (empty($input['tanggal_keluar']) || ! strtotime($input['tanggal_keluar'])) {
                return ['status' => false, 'message' => 'Tanggal keluar tidak valid.'];
            }

            $tglKeluar = Carbon::parse($input['tanggal_keluar']);
            if ($tglKeluar->lt(Carbon::parse($rp->tanggal_masuk))) {
                return ['status' => false, 'message' => 'Tanggal keluar sebelum tanggal masuk.'];
            }

            $rp->update([
                'status'         => $input['status'] ?? 'nonaktif',
                'tanggal_keluar' => $tglKeluar,
                'updated_by'     => Auth::id(),
            ]);

            return ['status' => true, 'data' => $rp];
        });
    }

    public function update(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $rp = RiwayatPendidikan::find($id);
            if (! $rp) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if (! is_null($rp->tanggal_keluar)) {
                return [
                    'status'  => false,
                    'message' => 'Data riwayat ini telah memiliki tanggal keluar dan tidak dapat diubah lagi demi menjaga keakuratan histori.',
                ];
            }

            $rp->update([
                'no_induk'       => $input['no_induk'] ?? $rp->no_induk,
                'lembaga_id'     => $input['lembaga_id'],
                'jurusan_id'     => $input['jurusan_id'],
                'kelas_id'       => $input['kelas_id'],
                'rombel_id'      => $input['rombel_id'],
                'tanggal_masuk'  => Carbon::parse($input['tanggal_masuk']),
                'angkatan_id'    => $input['angkatan_id'] ?? null,
                'updated_by'     => Auth::id(),
            ]);

            return ['status' => true, 'data' => $rp];
        });
    }
}
