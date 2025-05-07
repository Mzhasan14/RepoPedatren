<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\RiwayatDomisili;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DomisiliService
{
    public function index(string $bioId): array
    {
        $domisili = DB::table('riwayat_domisili as rd')
            ->join('santri as s', 'rd.santri_id', 's.id')
            ->join('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
            ->join('blok AS bl', 'rd.blok_id', '=', 'bl.id')
            ->join('kamar AS km', 'rd.kamar_id', '=', 'km.id')
            ->join('biodata as b', 's.biodata_id', 'b.id')
            ->where('b.id', $bioId)
            ->select(
                'rd.id',
                'w.nama_wilayah',
                'bl.nama_blok',
                'km.nama_kamar',
                'rd.tanggal_masuk',
                'rd.tanggal_keluar',
                'rd.status'
            )
            ->get();

        return ['status' => true, 'data' => $domisili];
    }

    public function store(array $data, string $bioId)
    {
        // Cek apakah santri sudah memiliki domisili aktif
        $exist = DB::table('riwayat_domisili as rd')
            ->join('santri as s', 'rd.santri_id', 's.id')
            ->join('biodata as b', 's.biodata_id', 'b.id')
            ->where('b.id', $bioId)
            ->where('rd.status', 'aktif')
            ->first();

        if ($exist) {
            return ['status' => false, 'message' => 'Santri masih memiliki domisili aktif'];
        }

        // Cari santri_id berdasarkan biodata_id (bioId)
        $santri = DB::table('santri')
            ->where('biodata_id', $bioId)
            ->latest()
            ->first();

        if (!$santri) {
            return ['status' => false, 'message' => 'Santri tidak ditemukan untuk biodata ini'];
        }

        // Insert data baru
        $id = DB::table('riwayat_domisili')->insertGetId([
            'santri_id' => $santri->id,
            'wilayah_id' => $data['wilayah_id'],
            'blok_id' => $data['blok_id'],
            'kamar_id' => $data['kamar_id'],
            'tanggal_masuk' => $data['tanggal_masuk'] ?? now(),
            'status' => 'aktif',
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $new = DB::table('riwayat_domisili')->where('id', $id)->first();

        return ['status' => true, 'data' => $new];
    }

    public function edit($id): array
    {
        $domisili = DB::table('riwayat_domisili as rd')
            ->join('santri as s', 'rd.santri_id', 's.id')
            ->join('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
            ->join('blok AS bl', 'rd.blok_id', '=', 'bl.id')
            ->join('kamar AS km', 'rd.kamar_id', '=', 'km.id')
            ->where('rd.id', $id)
            ->select(
                'rd.id',
                'w.nama_wilayah',
                'bl.nama_blok',
                'km.nama_kamar',
                'rd.tanggal_masuk',
                'rd.tanggal_keluar',
            )
            ->first();
        if (!$domisili) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }
        return ['status' => true, 'data' => $domisili];
    }

    public function update(array $data, string $id)
    {
        $domisili = DB::table('riwayat_domisili')->where('id', $id)->first();

        if (!$domisili) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        // Cegah update jika tanggal_keluar sudah terisi sebelumnya
        if (!is_null($domisili->tanggal_keluar)) {
            return ['status' => false, 'message' => 'Data riwayat tidak boleh di rubah!'];
        }

        // Jika tanggal_keluar diisi manual, pastikan tanggal_keluar tidak lebih awal dari tanggal_masuk
        if (!empty($data['tanggal_keluar'])) {
            $tanggalMasuk = strtotime($domisili->tanggal_masuk);
            $tanggalKeluar = strtotime($data['tanggal_keluar']);

            if ($tanggalKeluar < $tanggalMasuk) {
                return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk.'];
            }

            DB::table('riwayat_domisili')
                ->where('id', $id)
                ->update([
                    'tanggal_keluar' => $data['tanggal_keluar'],
                    'status' => 'keluar',
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            $updated = DB::table('riwayat_domisili')->where('id', $id)->first();
            return ['status' => true, 'data' => $updated];
        }
        // Cek perubahan lokasi
        $isWilayahChanged = $domisili->wilayah_id !== $data['wilayah_id'];
        $isBlokChanged = $domisili->blok_id !== $data['blok_id'];
        $isKamarChanged = $domisili->kamar_id !== $data['kamar_id'];

        if ($isWilayahChanged || $isBlokChanged || $isKamarChanged) {

            DB::table('riwayat_domisili')
                ->where('id', $id)
                ->update([
                    'status' => 'pindah',
                    'tanggal_keluar' => now(),
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            $new = DB::table('riwayat_domisili')->insertGetId([
                'santri_id' => $domisili->santri_id,
                'wilayah_id' => $data['wilayah_id'],
                'blok_id' => $data['blok_id'],
                'kamar_id' => $data['kamar_id'],
                'tanggal_masuk' => now(),
                'status' => 'aktif',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $newData = DB::table('riwayat_domisili')->where('id', $new)->first();
            return ['status' => true, 'data' => $newData];
        }

        return ['status' => false, 'message' => 'Tidak ada perubahan data'];
    }
}
