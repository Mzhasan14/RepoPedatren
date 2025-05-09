<?php

namespace App\Services\Pegawai\Filters\Formulir;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengajarService
{
    public function index(string $bioId): array
    {
        $pengajar = DB::table('pengajar as p')
            ->join('pegawai as pg', 'p.pegawai_id', 'pg.id')
            ->join('biodata as b', 'pg.biodata_id', 'b.id')
            ->join('materi_ajar as ma', 'p.id', 'ma.pengajar_id')
            ->where('b.id', $bioId)
            ->select(
                'p.id',
                'p.lembaga_id',
                'p.golongan_id',
                'p.jabatan as jabatan_kontrak',
                'p.tahun_masuk as tanggal_masuk',
                'p.tahun_akhir as tanggal_keluar',
                'p.status_aktif as status',
                DB::raw('GROUP_CONCAT(ma.nama_materi SEPARATOR ", ") as nama_materi'),
                DB::raw('SUM(ma.jumlah_menit) as jumlah_menit')
            )
            ->groupBy(
                'p.id',
                'p.lembaga_id',
                'p.golongan_id',
                'p.jabatan',
                'p.tahun_masuk',
                'p.tahun_akhir',
                'p.status_aktif'
            )    
            ->get();

        return ['status' => true, 'data' => $pengajar];
    }
    public function edit(string $pengajarId): array
    {
        // Ambil semua data pengajar dan materi ajarnya (kalau ada)
        $result = DB::table('pengajar as p')
            ->join('pegawai as pg', 'p.pegawai_id', 'pg.id')
            ->leftJoin('materi_ajar as ma', 'p.id', '=', 'ma.pengajar_id')
            ->where('p.id', $pengajarId)
            ->select(
                'p.id',
                'p.lembaga_id',
                'p.golongan_id',
                'p.jabatan as jabatan_kontrak',
                'p.tahun_masuk as tanggal_masuk',
                'p.tahun_akhir as tanggal_keluar',
                'p.status_aktif',
                'ma.nama_materi',
                'ma.jumlah_menit',
                'ma.tahun_masuk',
                'ma.tahun_akhir',
            )
            ->get();

        // Jika tidak ada data, kembalikan pesan
        if ($result->isEmpty()) {
            return [
                'message' => 'Data tidak ditemukan',
                'data' => null
            ];
        }

        // Ambil data utama dari baris pertama
        $pengajar = $result->first();

        // Ambil semua materi ajar, filter yang null
        $materi = $result->filter(function ($item) {
            return !is_null($item->nama_materi);
        })->map(function ($item) {
            return [
                'nama_materi' => $item->nama_materi,
                'jumlah_menit' => $item->jumlah_menit,
                'tahun_masuk' => $item->tahun_masuk,
                'tahun_akhir' => $item->tahun_akhir,
            ];
        })->values();

        // Susun hasil akhir
        return [
            'status' => true,
            'data' => [
                'id' => $pengajar->id,
                'lembaga_id' => $pengajar->lembaga_id,
                'golongan_id' => $pengajar->golongan_id,
                'jabatan_kontrak' => $pengajar->jabatan_kontrak,
                'tanggal_masuk' => $pengajar->tanggal_masuk,
                'tanggal_keluar' => $pengajar->tanggal_keluar,
                'status_aktif' => $pengajar->status_aktif,
                'materi' => $materi
            ]
        ];
    }

    public function update(array $data, string $id)
    {
        $pengajar = DB::table('pengajar')->where('id', $id)->first();

        if (!$pengajar) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        if (!is_null($pengajar->tahun_akhir)) {
            return ['status' => false, 'message' => 'Data riwayat tidak boleh diubah!'];
        }

    // Jika user isi tanggal keluar secara manual
    if (!empty($data['tahun_akhir_pengajar']) && !empty($data['tahun_akhir_materi_ajar'])) {
        $tanggalMasuk = strtotime($pengajar->tahun_masuk);
        $tanggalKeluarPengajar = strtotime($data['tahun_akhir_pengajar']);
        $tanggalKeluarMateri = strtotime($data['tahun_akhir_materi_ajar']);

        if ($tanggalKeluarPengajar < $tanggalMasuk || $tanggalKeluarMateri < $tanggalMasuk) {
            return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk.'];
        }

            // Update pengajar keluar
            DB::table('pengajar')->where('id', $id)->update([
                'tahun_akhir' => $data['tahun_akhir_pengajar'],
                'status_aktif' => 'tidak aktif',
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            // Update materi ajar keluar
            DB::table('materi_ajar')
                ->where('pengajar_id', $id)
                ->whereNull('tahun_akhir')
                ->update([
                    'tahun_akhir' => $data['tahun_akhir_materi_ajar'],
                    'status_aktif' => 'tidak aktif',
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            $updated = DB::table('pengajar')->where('id', $id)->first();
            return ['status' => true, 'data' => $updated];
        }

        // Deteksi perubahan data
        $isGolonganChanged = $pengajar->golongan_id != $data['golongan_id'];
        $isLembagaChanged = $pengajar->lembaga_id != $data['lembaga_id'];
        $isJabatanChanged = $pengajar->jabatan != ($data['jabatan'] ?? $pengajar->jabatan);

        $materiLama = DB::table('materi_ajar')
            ->where('pengajar_id', $id)
            ->whereNull('tahun_akhir')
            ->first();

        $isMateriChanged = false;
        if ($materiLama) {
            if (isset($data['nama_materi']) && is_array($data['nama_materi'])) {
                $isMateriChanged = $materiLama->nama_materi !== $data['nama_materi'][0];
            } else {
                $isMateriChanged = $materiLama->nama_materi !== ($data['nama_materi'] ?? $materiLama->nama_materi);
            }

            if (isset($data['jumlah_menit']) && is_array($data['jumlah_menit'])) {
                $isMateriChanged = $isMateriChanged || $materiLama->jumlah_menit !== (int) $data['jumlah_menit'][0];
            } else {
                $isMateriChanged = $isMateriChanged || $materiLama->jumlah_menit !== ($data['jumlah_menit'] ?? $materiLama->jumlah_menit);
            }
        }

        if ($isGolonganChanged || $isLembagaChanged || $isJabatanChanged || $isMateriChanged) {
            // Tutup data lama
            DB::table('pengajar')->where('id', $id)->update([
                'status_aktif' => 'tidak aktif',
                'tahun_akhir' => now(),
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            DB::table('materi_ajar')
                ->where('pengajar_id', $id)
                ->whereNull('tahun_akhir')
                ->update([
                    'tahun_akhir' => now(),
                    'status_aktif' => 'tidak aktif',
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            // Buat data baru
            $newId = DB::table('pengajar')->insertGetId([
                'pegawai_id' => $pengajar->pegawai_id,
                'lembaga_id' => $data['lembaga_id'] ?? $pengajar->lembaga_id,
                'golongan_id' => $data['golongan_id'] ?? $pengajar->golongan_id,
                'jabatan' => $data['jabatan'] ?? $pengajar->jabatan,
                'tahun_masuk' => now(),
                'status_aktif' => 'aktif',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (isset($data['nama_materi']) && is_array($data['nama_materi'])) {
                foreach ($data['nama_materi'] as $i => $materi) {
                    DB::table('materi_ajar')->insert([
                        'pengajar_id' => $newId,
                        'nama_materi' => $materi,
                        'jumlah_menit' => $data['jumlah_menit'][$i] ?? 0,
                        'status_aktif' => 'aktif',
                        'tahun_masuk' => now(),
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } else {
                // fallback kalau input-nya bukan array
                DB::table('materi_ajar')->insert([
                    'pengajar_id' => $newId,
                    'nama_materi' => $data['nama_materi'] ?? ($materiLama->nama_materi ?? null),
                    'jumlah_menit' => $data['jumlah_menit'] ?? ($materiLama->jumlah_menit ?? 0),
                    'status_aktif' => 'aktif',
                    'tahun_masuk' => now(),
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $newData = DB::table('pengajar')->where('id', $newId)->first();
            return ['status' => true, 'data' => $newData];
        }

        return ['status' => false, 'message' => 'Tidak ada perubahan yang dilakukan.'];
    }

    public function store(array $data, string $bioId)
    {
        // Cek apakah sudah ada pengajar aktif
        $exist = DB::table('pengajar')
            ->join('pegawai as p', 'pengajar.pegawai_id', '=', 'p.id')
            ->join('biodata as b', 'p.biodata_id', '=', 'b.id')
            ->where('b.id', $bioId)
            ->where('pengajar.status_aktif', 'aktif')
            ->first();

        if ($exist) {
            return ['status' => false, 'message' => 'Pegawai masih memiliki Pengajar aktif!'];
        }

        // Ambil pegawai berdasarkan biodata_id
        $pegawai = DB::table('pegawai')
            ->where('biodata_id', $bioId)
            ->latest()
            ->first();

        if (!$pegawai) {
            return ['status' => false, 'message' => 'Pegawai tidak ditemukan untuk biodata ini'];
        }

        // Insert pengajar
        $pengajarId = DB::table('pengajar')->insertGetId([
            'pegawai_id'   => $pegawai->id,
            'lembaga_id'   => $data['lembaga_id'],
            'golongan_id'  => $data['golongan_id'],
            'jabatan'      => $data['jabatan'],
            'tahun_masuk'  => $data['tahun_masuk'] ?? now(),
            'status_aktif' => 'aktif',
            'created_by'   => Auth::id(),
            'created_at'   => now(),
        ]);

        // Buat materi ajar - support 1 atau lebih
        $materiData = [];

        if (isset($data['nama_materi']) && is_array($data['nama_materi'])) {
            foreach ($data['nama_materi'] as $index => $nama) {
                $materiData[] = [
                    'pengajar_id' => $pengajarId,
                    'nama_materi' => $nama,
                    'jumlah_menit' => $data['jumlah_menit'][$index] ?? 0,
                    'tahun_masuk' => now(),
                    'status_aktif' => 'aktif',
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        } else {
            // Asumsikan satu materi ajar
            $materiData[] = [
                'pengajar_id' => $pengajarId,
                'nama_materi' => $data['nama_materi'],
                'jumlah_menit' => $data['jumlah_menit'],
                'tahun_masuk' => now(),
                'status_aktif' => 'aktif',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('materi_ajar')->insert($materiData);

        $newData = DB::table('pengajar')->where('id', $pengajarId)->first();
        return ['status' => true, 'data' => $newData];
    }


}