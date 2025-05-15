<?php

namespace App\Services\Pegawai\Filters\Formulir;

use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\Pengurus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengurusService
{
    public function index(string $bioId): array
    {
        $pengurus = Pengurus::whereHas('pegawai.biodata', fn($query) => $query->where('id', $bioId))
            ->with(['pegawai.biodata'])
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
            'data' => $pengurus
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

        if (!$pengurus) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ];
        }

        return [
            'status' => true,
            'data' => $pengurus
        ];
    }

    public function update(array $input, string $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $pengurus = Pengurus::find($id);
            if (!$pengurus) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if (!empty($input['tanggal_akhir'])) {
                $tglSelesai = Carbon::parse($input['tanggal_akhir']);
                $tglMulai = Carbon::parse($input['tanggal_mulai'] ?? $pengurus->tanggal_mulai);

                if ($tglSelesai->lt($tglMulai)) {
                    return [
                        'status' => false,
                        'message' => 'Tanggal keluar tidak boleh sebelum tanggal masuk.',
                    ];
                }
            }

            $updateData = [
                'golongan_jabatan_id' => $input['golongan_jabatan_id'],
                'satuan_kerja' => $input['satuan_kerja'] ?? $pengurus->satuan_kerja,
                'jabatan' => $input['jabatan'] ?? $pengurus->jabatan,
                'keterangan_jabatan' => $input['keterangan_jabatan'] ?? $pengurus->keterangan_jabatan,
                'tanggal_mulai' => Carbon::parse($input['tanggal_mulai']),
                'updated_by' => Auth::id(),
            ];

            if (!empty($input['tanggal_akhir'])) {
                $updateData['tanggal_akhir'] = Carbon::parse($input['tanggal_akhir']);
                $updateData['status_aktif'] = 'tidak aktif';
            } else {
                $updateData['tanggal_akhir'] = null;
                $updateData['status_aktif'] = 'aktif';
            }

            $pengurus->update($updateData);

            return [
                'status' => true,
                'data' => $pengurus,
            ];
        });
    }
    public function store(array $data, string $bioId): array
    {
        $exist = Pengurus::whereHas('pegawai', fn($q) => $q->where('biodata_id', $bioId))
            ->where('status_aktif', 'aktif')
            ->first();

        if ($exist) {
            return [
                'status' => false,
                'message' => 'Pegawai masih memiliki Pengurus aktif'
            ];
        }

        $pegawai = Pegawai::where('biodata_id', $bioId)->latest()->first();

        if (!$pegawai) {
            return [
                'status' => false,
                'message' => 'Pegawai tidak ditemukan untuk biodata ini'
            ];
        }

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
            'data' => $pengurus->fresh()
        ];
    }

}