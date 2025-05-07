<?php

namespace App\Services\PesertaDidik\Formulir;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PendidikanService
{
    public function index(string $bioId)
    {
        $pendidikan = DB::table('riwayat_pendidikan as rp')
            ->join('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
            ->join('jurusan AS j', 'rp.jurusan_id', '=', 'j.id')
            ->join('kelas AS kls', 'rp.kelas_id', '=', 'kls.id')
            ->join('santri as s', 'rp.santri_id', 's.id')
            ->join('biodata as b', 's.biodata_id', 'b.id')
            ->where('b.id', $bioId)
            ->select(
                'rp.id',
                'l.nama_lembaga',
                'j.nama_jurusan',
                'kls.nama_kelas',
                'r.nama_rombel',
            )
            ->get();

        return ['status' => true, 'data' => $pendidikan];
    }

    public function store(array $data, string $bioId)
    {
        // Cek apakah santri sudah memiliki pendidikan aktif
        $exist = DB::table('riwayat_pendidikan as rp')
            ->join('santri as s', 'rp.santri_id', 's.id')
            ->join('biodata as b', 's.biodata_id', 'b.id')
            ->where('b.id', $bioId)
            ->where('rp.status', 'aktif')
            ->first();

        if ($exist) {
            return ['status' => false, 'message' => 'Santri masih memiliki pendidikan aktif'];
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
        $id = DB::table('riwayat_pendidikan')->insertGetId([
            'santri_id' => $santri->id,
            'lembaga_id' => $data['lembaga_id'],
            'jurusan_id' => $data['jurusan_id'],
            'kelas_id' => $data['kelas_id'],
            'rombel_id' => $data['rombel_id'],
            'tanggal_masuk' => $data['tanggal_masuk'] ?? now(),
            'status' => 'aktif',
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $new = DB::table('riwayat_pendidikan')->where('id', $id)->first();

        return ['status' => true, 'data' => $new];
    }

    public function edit($id): array
    {
        $pendidikan = DB::table('riwayat_pendidikan as rp')
            ->join('santri as s', 'rp.santri_id', 's.id')
            ->join('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
            ->join('jurusan AS j', 'rp.jurusan_id', '=', 'j.id')
            ->join('kelas AS kl', 'rp.kelas_id', '=', 'kl.id')
            ->join('rombel AS r', 'rp.rombel_id', '=', 'r.id')
            ->where('rp.id', $id)
            ->select(
                'rp.id',
                'l.nama_lembaga',
                'j.nama_jurusan',
                'kl.nama_kelas',
                'r.nama_rombel',
                'rp.tanggal_masuk',
                'rp.tanggal_keluar',
            )
            ->first();
        if (!$pendidikan) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }
        return ['status' => true, 'data' => $pendidikan];
    }

    public function update(array $data, string $id)
    {
        $existing = DB::table('riwayat_pendidikan')->where('id', $id)->first();

        if (!$existing) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        // Cegah update jika tanggal_keluar sudah terisi sebelumnya
        if (!is_null($existing->tanggal_keluar)) {
            return ['status' => false, 'message' => 'Data riwayat tidak boleh di rubah!'];
        }

        // Jika tanggal_keluar diisi manual, pastikan tanggal_keluar tidak lebih awal dari tanggal_masuk
        if (!empty($data['tanggal_keluar'])) {
            $tanggalMasuk = strtotime($existing->tanggal_masuk);
            $tanggalKeluar = strtotime($data['tanggal_keluar']);

            if ($tanggalKeluar < $tanggalMasuk) {
                return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk.'];
            }

            DB::table('riwayat_pendidikan')
                ->where('id', $id)
                ->update([
                    'tanggal_keluar' => $data['tanggal_keluar'],
                    'status' => 'berhenti',
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            $updated = DB::table('riwayat_pendidikan')->where('id', $id)->first();
            return ['status' => true, 'data' => $updated];
        }
        // Cek perubahan lokasi
        $isLembagaChanged = $existing->lembaga_id !== $data['lembaga_id'];
        $idJurusanChanged = $existing->jurusan_id !== $data['jurusan_id'];
        $isKelasChanged = $existing->kelas_id !== $data['kelas_id'];
        $isRombelChanged = $existing->rombel_id !== $data['rombel_id'];

        if ($isLembagaChanged || $idJurusanChanged || $isKelasChanged || $isRombelChanged) {
            DB::table('riwayat_pendidikan')
                ->where('id', $id)
                ->update([
                    'status' => 'pindah',
                    'tanggal_keluar' => now(),
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            $newId = DB::table('riwayat_pendidikan')->insertGetId([
                'santri_id' => $existing->santri_id,
                'lembaga_id' => $data['lembaga_id'],
                'jurusan_id' => $data['jurusan_id'],
                'kelas_id' => $data['kelas_id'],
                'rombel_id' => $data['rombel_id'],
                'tanggal_masuk' => now(),
                'status' => 'aktif',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $newData = DB::table('riwayat_pendidikan')->where('id', $newId)->first();
            return ['status' => true, 'data' => $newData];
        }

        return ['status' => false, 'message' => 'Tidak ada perubahan data'];
    }
}
