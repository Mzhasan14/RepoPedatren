<?php

namespace App\Services\PesertaDidik\Fitur;

use App\Models\Biodata;
use App\Models\Pendidikan;
use App\Models\RiwayatPendidikan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PindahNaikJenjangService
{

    public function pindah(array $data)
    {
        $now = now();
        $userId = Auth::id();
        $bioIds = $data['biodata_id'];

        $biodataList = Biodata::whereIn('id', $bioIds)->pluck('nama', 'id');
        $pendidikanAktif = Pendidikan::whereIn('biodata_id', $bioIds)
            ->get()
            ->keyBy('biodata_id');

        $dataBaruNama = [];
        $dataGagal = [];

        DB::beginTransaction();
        try {
            foreach ($bioIds as $bioId) {
                $pendidikan = $pendidikanAktif->get($bioId);
                $nama = $biodataList[$bioId] ?? 'Tidak diketahui';

                if (is_null($pendidikan)) {
                    $dataGagal[] = [
                        'nama' => $nama,
                        'message' => 'Data pendidikan aktif tidak ditemukan.',
                    ];
                    continue;
                }

                RiwayatPendidikan::create([
                    'biodata_id' => $pendidikan->biodata_id,
                    'lembaga_id' => $pendidikan->lembaga_id,
                    'jurusan_id' => $pendidikan->jurusan_id ?? null,
                    'kelas_id' => $pendidikan->kelas_id ?? null,
                    'rombel_id' => $pendidikan->rombel_id ?? null,
                    'no_induk' => $pendidikan->no_induk ?? null,
                    'angkatan_id' => $pendidikan->angkatan_id ?? null,
                    'status' => 'pindah',
                    'tanggal_masuk' => $pendidikan->tanggal_masuk,
                    'tanggal_keluar' => $now,
                    'created_by' => $pendidikan->created_by,
                    'created_at' => $pendidikan->created_at,
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);

                $pendidikan->update([
                    'lembaga_id' => $data['lembaga_id'],
                    'jurusan_id' => $data['jurusan_id'],
                    'kelas_id' => $data['kelas_id'],
                    'rombel_id' => $data['rombel_id'],
                    'tanggal_masuk' => $now,
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);

                $dataBaruNama[] = [
                    'nama' => $nama,
                    'message' => 'Berhasil dipindahkan.',
                ];
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memindahkan data.',
                'error' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => 'Peserta didik berhasil dipindahkan.',
            'data_baru' => $dataBaruNama,
            'data_gagal' => $dataGagal,
        ];
    }


    public function naik(array $data)
    {
        $now = now();
        $userId = Auth::id();
        $bioIds = $data['biodata_id'];

        $biodataList = Biodata::whereIn('id', $bioIds)->pluck('nama', 'id');
        $pendidikanAktif = Pendidikan::whereIn('biodata_id', $bioIds)
            ->get()
            ->keyBy('biodata_id');

        $dataBaruNama = [];
        $dataGagal = [];

        DB::beginTransaction();
        try {
            foreach ($bioIds as $bioId) {
                $pendidikan = $pendidikanAktif->get($bioId);
                $nama = $biodataList[$bioId] ?? 'Tidak diketahui';

                if (is_null($pendidikan)) {
                    $dataGagal[] = [
                        'nama' => $nama,
                        'message' => 'Data pendidikan aktif tidak ditemukan.',
                    ];
                    continue;
                }

                RiwayatPendidikan::create([
                    'biodata_id' => $pendidikan->biodata_id,
                    'lembaga_id' => $pendidikan->lembaga_id,
                    'jurusan_id' => $pendidikan->jurusan_id ?? null,
                    'kelas_id' => $pendidikan->kelas_id ?? null,
                    'rombel_id' => $pendidikan->rombel_id ?? null,
                    'no_induk' => $pendidikan->no_induk ?? null,
                    'angkatan_id' => $pendidikan->angkatan_id ?? null,
                    'status' => 'selesai',
                    'tanggal_masuk' => $pendidikan->tanggal_masuk,
                    'tanggal_keluar' => $now,
                    'created_by' => $pendidikan->created_by,
                    'created_at' => $pendidikan->created_at,
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);

                $pendidikan->update([
                    'lembaga_id' => $data['lembaga_id'],
                    'jurusan_id' => $data['jurusan_id'],
                    'kelas_id' => $data['kelas_id'],
                    'rombel_id' => $data['rombel_id'],
                    'tanggal_masuk' => $now,
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);

                $dataBaruNama[] = [
                    'nama' => $nama,
                    'message' => 'Berhasil naik kelas.',
                ];
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses kenaikan kelas.',
                'error' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => 'Peserta didik berhasil naik kelas.',
            'data_baru' => $dataBaruNama,
            'data_gagal' => $dataGagal,
        ];
    }
}
