<?php

namespace App\Services\PesertaDidik\Fitur;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Kartu;
use App\Models\PresensiSholat;
use App\Models\LogPresensi;
use App\Models\JadwalSholat;
use Exception;

class PresensiJamaahService
{
    protected $tz = 'Asia/Jakarta';

    public function scanByUid(string $uid, ?int $operatorUserId = null): array
    {
        $now = Carbon::now($this->tz);

        // Mulai transaksi untuk konsistensi
        return DB::transaction(function () use ($uid, $operatorUserId, $now) {
            $kartu = Kartu::with('santri.biodata')->where('uid_kartu', $uid)->first();

            // 1) kartu tidak ditemukan
            if (! $kartu) {
                $this->createLog(null, null, null, $now, 'Gagal', "Kartu tidak terdaftar: {$uid}", 'Kartu', $operatorUserId);
                throw new Exception('Kartu tidak terdaftar.');
            }

            // 2) kartu tidak aktif
            if (! $kartu->aktif) {
                $this->createLog($kartu->santri_id, $kartu->id, null, $now, 'Gagal', 'Kartu tidak aktif', 'Kartu', $operatorUserId);
                throw new Exception('Kartu tidak aktif.');
            }

            $santri = $kartu->santri;
            if (! $santri) {
                $this->createLog(null, $kartu->id, null, $now, 'Gagal', 'Santri tidak ditemukan pada kartu', 'Kartu', $operatorUserId);
                throw new Exception('Data santri tidak ditemukan.');
            }

            // 3) cari jadwal yang sedang aktif pada waktu sekarang
            // kondisi: jadwal.berlaku_mulai <= date(now) AND (berlaku_sampai IS NULL OR berlaku_sampai >= date(now))
            // dan jam_mulai <= time(now) <= jam_selesai
            $todayDate = $now->toDateString();
            $nowTime = $now->toTimeString();

            $jadwal = JadwalSholat::where('berlaku_mulai', '<=', $todayDate)
                ->where(function ($q) use ($todayDate) {
                    $q->whereNull('berlaku_sampai')
                      ->orWhere('berlaku_sampai', '>=', $todayDate);
                })
                ->whereTime('jam_mulai', '<=', $nowTime)
                ->whereTime('jam_selesai', '>=', $nowTime)
                ->with('sholat')
                ->first();

            if (! $jadwal) {
                // tidak ada jadwal aktif
                $this->createLog($santri->id, $kartu->id, null, $now, 'Diluar Jadwal', 'Scan dilakukan diluar jadwal aktif', 'Kartu', $operatorUserId);
                return [
                    'status' => 'Diluar Jadwal',
                    'message' => 'Diluar jadwal.',
                ];
            }

            $sholat = $jadwal->sholat;

            // 4) cek duplikat presensi hari ini
            $exists = PresensiSholat::where('santri_id', $santri->id)
                ->where('sholat_id', $sholat->id)
                ->whereDate('tanggal', $todayDate)
                ->exists();

            if ($exists) {
                $this->createLog($santri->id, $kartu->id, $sholat->id, $now, 'Duplikat', 'Sudah Presensi', 'Kartu', $operatorUserId);
                return [
                    'status' => 'Duplikat',
                    'message' => 'Sudah melakukan presensi untuk sholat ini hari ini.',
                ];
            }

            // 5) simpan presensi
            $presensi = PresensiSholat::create([
                'santri_id'      => $santri->id,
                'sholat_id'      => $sholat->id,
                'tanggal'        => $todayDate,
                'waktu_presensi' => $now->toTimeString(),
                'status'         => 'Hadir',
                'metode'         => 'Kartu',
                'created_by'     => $operatorUserId,
            ]);

            // 6) log sukses
            $this->createLog($santri->id, $kartu->id, $sholat->id, $now, 'Sukses', 'Presensi sukses', 'Kartu', $operatorUserId);

            // 7) build response konfirmasi
            return [
                'status' => 'Sukses',
                'data' => [
                    'nama_santri'    => $santri->biodata->nama ?? null,
                    'nis'            => $santri->nis ?? null,
                    'nama_sholat'    => $sholat->nama_sholat ?? null,
                    'waktu_presensi' => $now->toDateTimeString(),
                    'status_presensi'=> 'Hadir',
                ]
            ];
        });
    }

    protected function createLog($santriId, $kartuId, $sholatId, $waktuScan, $hasil, $pesan = null, $metode = 'Kartu', $userId = null)
    {
        return LogPresensi::create([
            'santri_id'  => $santriId,
            'kartu_id'   => $kartuId,
            'sholat_id'  => $sholatId,
            'waktu_scan' => $waktuScan,
            'hasil'      => $hasil,
            'pesan'      => $pesan,
            'metode'     => $metode,
            'user_id'    => $userId,
            'created_by' => $userId,
        ]);
    }
}
