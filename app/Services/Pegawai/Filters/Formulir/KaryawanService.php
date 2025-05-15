<?php

namespace App\Services\Pegawai\Filters\Formulir;

use App\Models\Pegawai\Karyawan;
use App\Models\Pegawai\Pegawai;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class KaryawanService
{
    public function index(string $bioId): array
    {
        $karyawan = Karyawan::whereHas('pegawai.biodata', fn($query) => $query->where('id', $bioId))
            ->with(['pegawai.biodata'])
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
            'data' => $karyawan
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
                'status_aktif as status'
            ])
            ->find($id);

        if (!$karyawan) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ];
        }

        return [
            'status' => true,
            'data' => $karyawan
        ];
    }

    public function store(array $data, string $bioId): array
    {
        // Periksa apakah Pegawai sudah memiliki karyawan aktif
        $exist = Karyawan::whereHas('pegawai', fn($q) => $q->where('biodata_id', $bioId))
            ->where('status_aktif', 'aktif')
            ->first();

        if ($exist) {
            return [
                'status' => false,
                'message' => 'Pegawai masih memiliki Karyawan aktif'
            ];
        }

        // Cari Pegawai berdasarkan biodata_id
        $pegawai = Pegawai::where('biodata_id', $bioId)
            ->latest()
            ->first();

        if (!$pegawai) {
            return [
                'status' => false,
                'message' => 'Pegawai tidak ditemukan untuk biodata ini'
            ];
        }

        // Buat Karyawan Baru
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
            'data' => $karyawan->fresh()
        ];
    }

    public function update(array $input, string $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            // 1. Pencarian data karyawan id
            $karyawan = Karyawan::find($id);
            if (!$karyawan) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            // 2. Validasi tanggal 
            if (!empty($input['tanggal_selesai'])) {
                $tglSelesai = Carbon::parse($input['tanggal_selesai']);
                $tglMulai = Carbon::parse($input['tanggal_mulai'] ?? $karyawan->tanggal_mulai);

                if ($tglSelesai->lt($tglMulai)) {
                    return [
                        'status' => false,
                        'message' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
                    ];
                }
            }

            // 3. Persiapkan data update
            $updateData = [
                'golongan_jabatan_id' => $input['golongan_jabatan_id'],
                'lembaga_id' => $input['lembaga_id'],
                'jabatan' => $input['jabatan'] ?? $karyawan->jabatan,
                'keterangan_jabatan' => $input['keterangan_jabatan'] ?? $karyawan->keterangan_jabatan,
                'tanggal_mulai' => Carbon::parse($input['tanggal_mulai']),
                'updated_by' => Auth::id(),
            ];

            // 4. Logika status aktif berdasarkan tanggal selesai
            if (!empty($input['tanggal_selesai'])) {
                $updateData['tanggal_selesai'] = Carbon::parse($input['tanggal_selesai']);
                $updateData['status_aktif'] = 'tidak aktif';
            } else {
                $updateData['tanggal_selesai'] = null;
                $updateData['status_aktif'] = 'aktif';
            }

            // 5. Eksekusi update
            $karyawan->update($updateData);

            // 6. Return
            return [
                'status' => true,
                'data' => $karyawan,
            ];
        });
    }

}