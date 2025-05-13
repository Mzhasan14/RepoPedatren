<?php

namespace App\Services\Pegawai\Filters\Formulir;

use App\Models\Pegawai\Karyawan;
use App\Models\Pegawai\Pegawai;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class KaryawanService
{
    public function index(string $bioId): array
    {
    $karyawan = Karyawan::whereHas('pegawai.biodata', function ($query) use ($bioId) {
            $query->where('id', $bioId);
        })
        ->with(['pegawai.biodata'])
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'jabatan_kontrak' => $item->jabatan,
                'keterangan_jabatan' => $item->keterangan_jabatan,
                'tanggal_masuk' => $item->tanggal_mulai,
                'tanggal_keluar' => $item->tanggal_selesai,
                'status' => $item->status_aktif,
            ];
        });

        return ['status' => true, 'data' => $karyawan];
    }

    public function edit($id): array
    {
        $karyawan = Karyawan::select(
                'id',
                'golongan_jabatan_id',
                'lembaga_id',
                'jabatan as jabatan_kontrak',
                'keterangan_jabatan',
                'tanggal_mulai as tanggal_masuk',
                'tanggal_selesai as tanggal_keluar',
                'status_aktif as status'
            )
            ->find($id);

        if (!$karyawan) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $karyawan];
    }

    public function store(array $data, string $bioId)
    {
        // Cek apakah pegawai sudah memiliki karyawan aktif
        $exist = Karyawan::whereHas('pegawai', function ($q) use ($bioId) {
                        $q->where('biodata_id', $bioId);
                    })
                    ->where('status_aktif', 'aktif')
                    ->first();

        if ($exist) {
            return ['status' => false, 'message' => 'Pegawai masih memiliki Karyawan aktif'];
        }

        // Cari pegawai berdasarkan biodata_id
        $pegawai = Pegawai::where('biodata_id', $bioId)->latest()->first();

        if (!$pegawai) {
            return ['status' => false, 'message' => 'Pegawai tidak ditemukan untuk biodata ini'];
        }

        // Insert data baru
        $karyawan = new Karyawan();
        $karyawan->pegawai_id = $pegawai->id;
        $karyawan->golongan_jabatan_id = $data['golongan_jabatan_id'];
        $karyawan->lembaga_id = $data['lembaga_id'];
        $karyawan->jabatan = $data['jabatan'];
        $karyawan->keterangan_jabatan = $data['keterangan_jabatan'];
        $karyawan->tanggal_mulai = $data['tanggal_mulai'] ?? now();
        $karyawan->status_aktif = 'aktif';
        $karyawan->created_by = Auth::id();
        $karyawan->created_at = now();
        $karyawan->updated_at = now();
        $karyawan->save();

        // Logging aktivitas
        activity('karyawan_create')
            ->causedBy(Auth::user())
            ->performedOn($karyawan)
            ->withProperties([
                'after' => $karyawan->toArray(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->event('create_karyawan')
            ->log('Karyawan baru berhasil ditambahkan.');


        return ['status' => true, 'data' => $karyawan->fresh()];
    }

    public function update(array $data, string $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $karyawan = Karyawan::find($id);

            if (!$karyawan) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            if (!is_null($karyawan->tanggal_selesai)) {
                return ['status' => false, 'message' => 'Data riwayat tidak boleh diubah!'];
            }

            $before = $karyawan->toArray();
            $batchUuid = Str::uuid()->toString();

            // Jika mengisi tanggal_selesai secara manual
            if (!empty($data['tanggal_selesai'])) {
                $tanggalMasuk = strtotime($karyawan->tanggal_mulai);
                $tanggalKeluar = strtotime($data['tanggal_selesai']);

                if ($tanggalKeluar < $tanggalMasuk) {
                    return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk.'];
                }

                $karyawan->tanggal_selesai = $data['tanggal_selesai'];
                $karyawan->status = 'keluar';
                $karyawan->updated_by = Auth::id();
                $karyawan->updated_at = now();
                $karyawan->save();

                activity('karyawan_update')
                    ->performedOn($karyawan)
                    ->withProperties([
                        'before' => $before,
                        'after' => $karyawan->toArray(),
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->tap(function ($activity) use ($batchUuid) {
                        $activity->batch_uuid = $batchUuid;
                    })
                    ->event('keluar_karyawan')
                    ->log('Karyawan diupdate (keluar).');

                return ['status' => true, 'data' => $karyawan->fresh()];
            }

            // Perubahan jabatan atau lembaga
            $isGolonganJabatanChanged = $karyawan->golongan_jabatan_id !== $data['golongan_jabatan_id'];
            $isLembagaChanged = $karyawan->lembaga_id !== $data['lembaga_id'];

            if ($isGolonganJabatanChanged || $isLembagaChanged) {
                $karyawan->status_aktif = 'tidak aktif';
                $karyawan->tanggal_selesai = now();
                $karyawan->updated_by = Auth::id();
                $karyawan->updated_at = now();
                $karyawan->save();

                activity('karyawan_update')
                    ->performedOn($karyawan)
                    ->withProperties([
                        'before' => $before,
                        'after' => $karyawan->toArray(),
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->tap(function ($activity) use ($batchUuid) {
                        $activity->batch_uuid = $batchUuid;
                    })
                    ->event('nonaktif_karyawan')
                    ->log('Karyawan dinonaktifkan karena perubahan jabatan/lembaga.');

                // Entri baru
                $newKaryawan = new Karyawan();
                $newKaryawan->pegawai_id = $karyawan->pegawai_id;
                $newKaryawan->golongan_jabatan_id = $data['golongan_jabatan_id'];
                $newKaryawan->lembaga_id = $data['lembaga_id'];
                $newKaryawan->jabatan = $data['jabatan'] ?? $karyawan->jabatan;
                $newKaryawan->keterangan_jabatan = $data['keterangan_jabatan'] ?? $karyawan->keterangan_jabatan;
                $newKaryawan->tanggal_mulai = now();
                $newKaryawan->status_aktif = 'aktif';
                $newKaryawan->created_by = Auth::id();
                $newKaryawan->created_at = now();
                $newKaryawan->updated_at = now();
                $newKaryawan->save();

                activity('karyawan_create')
                    ->performedOn($newKaryawan)
                    ->withProperties([
                        'before' => null,
                        'after' => $newKaryawan->toArray(),
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->tap(function ($activity) use ($batchUuid) {
                        $activity->batch_uuid = $batchUuid;
                    })
                    ->event('create_karyawan')
                    ->log('Karyawan baru ditambahkan karena perubahan jabatan/lembaga.');

                return ['status' => true, 'data' => $newKaryawan];
            }

            return ['status' => false, 'message' => 'Tidak ada perubahan data'];
        });
    }

}