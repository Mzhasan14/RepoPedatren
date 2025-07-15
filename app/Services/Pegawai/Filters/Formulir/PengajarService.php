<?php

namespace App\Services\Pegawai\Filters\Formulir;

use App\Models\Pegawai\JadwalPelajaran;
use App\Models\Pegawai\JamPelajaran;
use App\Models\Pegawai\MataPelajaran;
use App\Models\Pegawai\MateriAjar;
use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\Pengajar;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Lembaga;
use App\Models\Santri;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PengajarService
{
    public function index(string $bioId): array
    {
        $pengajar = Pengajar::whereHas('pegawai.biodata', fn ($query) => $query->where('id', $bioId))
            ->with(['lembaga', 'golongan'])
            ->orderBy('tahun_masuk','desc')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nama_lembaga' => optional($p->lembaga)->nama_lembaga,
                'nama_golongan' => optional($p->golongan)->nama_golongan,
                'jabatan_kontrak' => $p->jabatan,
                'tanggal_masuk' => $p->tahun_masuk,
                'tanggal_keluar' => $p->tahun_akhir,
                'status' => $p->status_aktif,
            ]);

        return [
            'status' => true,
            'data' => $pengajar,
        ];
    }

    public function show($id): array
    {
        $pengajar = Pengajar::with(['mataPelajaran'])->find($id);

        if (! $pengajar) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        $materi = $pengajar->mataPelajaran->map(fn ($m) => [
            'materi_id' => $m->id,
            'kode_mapel' => $m->kode_mapel,
            'nama_mapel' => $m->nama_mapel,
            'status' => $m->status,
        ]);

        return [
            'status' => true,
            'data' => [
                'id' => $pengajar->id,
                'lembaga_id' => $pengajar->lembaga_id,
                'golongan_id' => $pengajar->golongan_id,
                'jabatan_kontrak' => $pengajar->jabatan,
                'tanggal_masuk' => $pengajar->tahun_masuk,
                'tanggal_keluar' => $pengajar->tahun_akhir,
                'status_aktif' => $pengajar->status_aktif,
                'materi' => $materi,
            ],
        ];
    }

    public function update(array $input, string $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $pengajar = Pengajar::with('mataPelajaran')->find($id);
            if (! $pengajar) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            // Larangan update jika tahun_akhir sudah ada dan status aktif 'tidak aktif'
            if (! is_null($pengajar->tahun_akhir) && $pengajar->status_aktif === 'tidak aktif') {
                return [
                    'status' => false,
                    'message' => 'Data pengajar ini telah memiliki tahun akhir dan statusnya tidak aktif, tidak dapat diubah lagi demi menjaga keakuratan histori.',
                ];
            }

            $oldLembagaId = $pengajar->lembaga_id;
            $newLembagaId = $input['lembaga_id'];

            // Update data pengajar biasa
            $pengajar->update([
                'golongan_id' => $input['golongan_id'],
                'lembaga_id' => $newLembagaId,
                'jabatan' => $input['jabatan'] ?? $pengajar->jabatan,
                'tahun_masuk' => Carbon::parse($input['tahun_masuk']) ?? now(),
                'updated_by' => Auth::id(),
            ]);

            // Jika lembaga_id berubah, update juga di semua mata_pelajaran
            if ($oldLembagaId != $newLembagaId) {
                foreach ($pengajar->mataPelajaran as $mapel) {
                    $mapel->update([
                        'lembaga_id' => $newLembagaId,
                        'updated_by' => Auth::id(),
                    ]);
                }
            }

            return [
                'status' => true,
                'data' => $pengajar,
            ];
        });
    }

    // public function store(array $data, string $bioId): array
    // {
    //     // 1. Periksa apakah Pegawai sudah memiliki pengajar aktif
    //     $exist = Pengajar::whereHas('pegawai', fn ($q) => $q->where('biodata_id', $bioId))
    //         ->where('status_aktif', 'aktif')
    //         ->first();

    //     if ($exist) {
    //         return [
    //             'status' => false,
    //             'message' => 'Pegawai masih memiliki Pengajar aktif',
    //         ];
    //     }

    //     // 2. Cari Pegawai berdasarkan biodata_id
    //     $pegawai = Pegawai::where('biodata_id', $bioId)
    //         ->latest()
    //         ->first();

    //     if (! $pegawai) {
    //         return [
    //             'status' => false,
    //             'message' => 'Pegawai tidak ditemukan untuk biodata ini',
    //         ];
    //     }

    //     // 3. Buat Pengajar Baru dalam transaction
    //     return DB::transaction(function () use ($data, $pegawai) {
    //         $pengajar = Pengajar::create([
    //             'pegawai_id' => $pegawai->id,
    //             'golongan_id' => $data['golongan_id'],
    //             'lembaga_id' => $data['lembaga_id'],
    //             'jabatan' => $data['jabatan'],
    //             'tahun_masuk' => $data['tahun_masuk'] ?? now(),
    //             'status_aktif' => 'aktif',
    //             'created_by' => Auth::id(),
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);

    //         // 4. Tambahkan materi ajar jika ada
    //         if (! empty($data['nama_materi'])) {
    //             if (is_array($data['nama_materi'])) {
    //                 foreach ($data['nama_materi'] as $index => $nama) {
    //                     MateriAjar::create([
    //                         'pengajar_id' => $pengajar->id,
    //                         'nama_materi' => $nama,
    //                         'jumlah_menit' => $data['jumlah_menit'][$index] ?? 0,
    //                         'tahun_masuk' => now(),
    //                         'status_aktif' => 'aktif',
    //                         'created_by' => Auth::id(),
    //                         'created_at' => now(),
    //                         'updated_at' => now(),
    //                     ]);
    //                 }
    //             } else {
    //                 MateriAjar::create([
    //                     'pengajar_id' => $pengajar->id,
    //                     'nama_materi' => $data['nama_materi'],
    //                     'jumlah_menit' => $data['jumlah_menit'] ?? 0,
    //                     'tahun_masuk' => now(),
    //                     'status_aktif' => 'aktif',
    //                     'created_by' => Auth::id(),
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //             }
    //         }

    //         // 5. Return response
    //         return [
    //             'status' => true,
    //             'data' => $pengajar->fresh()->load('materiAjar'),
    //         ];
    //     });
    // }

    // public function pindahPengajar(array $input, int $id): array
    // {
    //     return DB::transaction(function () use ($input, $id) {
    //         $old = Pengajar::with('materiAjar')->find($id);
    //         if (! $old) {
    //             return ['status' => false, 'message' => 'Data tidak ditemukan.'];
    //         }

    //         if ($old->tahun_akhir) {
    //             return [
    //                 'status' => false,
    //                 'message' => 'Data pengajar sudah memiliki tahun akhir, tidak dapat diganti.',
    //             ];
    //         }

    //         $tahunMasukBaru = Carbon::parse($input['tahun_masuk'] ?? '');
    //         $hariIni = Carbon::now();

    //         if ($tahunMasukBaru->lt($hariIni)) {
    //             return [
    //                 'status' => false,
    //                 'message' => 'Tahun masuk baru tidak boleh sebelum hari ini.',
    //             ];
    //         }

    //         // Nonaktifkan semua materi ajar lama
    //         foreach ($old->materiAjar as $materi) {
    //             $materi->update([
    //                 'status_aktif' => 'tidak aktif',
    //                 'tahun_akhir' => $hariIni,
    //                 'updated_by' => Auth::id(),
    //             ]);
    //         }

    //         // Tutup pengajar lama
    //         $old->update([
    //             'status_aktif' => 'tidak aktif',
    //             'tahun_akhir' => $hariIni,
    //             'updated_by' => Auth::id(),
    //         ]);

    //         // Buat pengajar baru
    //         $new = Pengajar::create([
    //             'pegawai_id' => $old->pegawai_id,
    //             'golongan_id' => $input['golongan_id'],
    //             'lembaga_id' => $input['lembaga_id'],
    //             'jabatan' => $input['jabatan'] ?? $old->jabatan,
    //             'tahun_masuk' => $tahunMasukBaru,
    //             'status_aktif' => 'aktif',
    //             'created_by' => Auth::id(),
    //         ]);

    //         // Buat materi ajar baru dari input
    //         if (! empty($input['materi_ajar']) && is_array($input['materi_ajar'])) {
    //             foreach ($input['materi_ajar'] as $materiBaru) {
    //                 $new->materiAjar()->create([
    //                     'nama_materi' => $materiBaru['nama_materi'],
    //                     'tahun_masuk' => $tahunMasukBaru,
    //                     'jumlah_menit' => $materiBaru['jumlah_menit'] ?? 0,
    //                     'status_aktif' => 'aktif',
    //                     'created_by' => Auth::id(),
    //                 ]);
    //             }
    //         }

    //         return [
    //             'status' => true,
    //             'data' => $new->load('materiAjar'),
    //         ];
    //     });
    // }

    // public function keluarPengajar(array $input, int $id): array
    // {
    //     return DB::transaction(function () use ($input, $id) {
    //         $pengajar = Pengajar::find($id);
    //         if (! $pengajar) {
    //             return ['status' => false, 'message' => 'Data tidak ditemukan.'];
    //         }

    //         if ($pengajar->tahun_akhir) {
    //             return [
    //                 'status' => false,
    //                 'message' => 'Data pengajar sudah ditandai selesai/nonaktif.',
    //             ];
    //         }

    //         $tahunAkhir = Carbon::parse($input['tahun_akhir'] ?? '');
    //         if ($tahunAkhir->lt(Carbon::parse($pengajar->tahun_masuk))) {
    //             return [
    //                 'status' => false,
    //                 'message' => 'Tahun akhir tidak boleh sebelum tahun masuk.',
    //             ];
    //         }

    //         // Update status pengajar menjadi tidak aktif dan set tahun_akhir
    //         $pengajar->update([
    //             'status_aktif' => 'tidak aktif',
    //             'tahun_akhir' => $tahunAkhir,
    //             'updated_by' => Auth::id(),
    //         ]);

    //         // Nonaktifkan semua materi ajar terkait pengajar ini
    //         foreach ($pengajar->materiAjar as $materi) {
    //             $materi->update([
    //                 'status_aktif' => 'tidak aktif',
    //                 'tahun_akhir' => $tahunAkhir,
    //                 'updated_by' => Auth::id(),
    //             ]);
    //         }

    //         return [
    //             'status' => true,
    //             'data' => $pengajar->load('materiAjar'),
    //         ];
    //     });
    // }

    // public function nonaktifkan(string $pengajarId, string $materiId): array
    // {
    //     $materi = MateriAjar::where('pengajar_id', $pengajarId)
    //         ->where('id', $materiId)
    //         ->first();

    //     if (! $materi) {
    //         return ['status' => false, 'message' => 'Materi tidak ditemukan.'];
    //     }

    //     if (! is_null($materi->tahun_akhir) && $materi->status_aktif === 'tidak aktif') {
    //         return ['status' => false, 'message' => 'Materi sudah nonaktif dan tidak bisa diubah.'];
    //     }

    //     $materi->update([
    //         'tahun_akhir' => now(),
    //         'status_aktif' => 'tidak aktif',
    //         'updated_by' => Auth::id(),
    //     ]);

    //     return ['status' => true, 'message' => 'Materi berhasil dinonaktifkan.', 'data' => $materi];
    // }

    // public function tambahMateri(string $pengajarId, array $input): array
    // {
    //     $pengajar = Pengajar::find($pengajarId);

    //     if (! $pengajar) {
    //         return ['status' => false, 'message' => 'Pengajar tidak ditemukan.'];
    //     }

    //     if (empty($input['materi_ajar']) || ! is_array($input['materi_ajar'])) {
    //         return ['status' => false, 'message' => 'Data materi ajar tidak valid.'];
    //     }

    //     $tahunMasuk = $input['tahun_masuk'] ?? now();

    //     foreach ($input['materi_ajar'] as $materiBaru) {
    //         $pengajar->materiAjar()->create([
    //             'nama_materi' => $materiBaru['nama_materi'],
    //             'tahun_masuk' => $tahunMasuk,
    //             'jumlah_menit' => $materiBaru['jumlah_menit'] ?? 0,
    //             'status_aktif' => 'aktif',
    //             'created_by' => Auth::id(),
    //         ]);
    //     }

    //     return ['status' => true, 'message' => 'Materi ajar berhasil ditambahkan.', 'data' => $pengajar->load('materiAjar')];
    // }
    
    public function showMapelById(int $id): array
    {
        try {
            $materi = MataPelajaran::select('id', 'lembaga_id','kode_mapel', 'nama_mapel','pengajar_id','status')
                ->findOrFail($id);

            return [
                'status' => true,
                'data' => $materi,
            ];
        } catch (\Exception $e) {
            Log::error('Gagal ambil data materi: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'Mata pelajaran tidak ditemukan.',
                'error' => $e->getMessage(),
            ];
        }
    }
    public function updateMateri(int $materiId, array $input): array
    {
        try {
            $materi = MataPelajaran::findOrFail($materiId);

            // Validasi kode_mapel unik di data aktif (kecuali dirinya sendiri)
            $kodeSudahAda = MataPelajaran::where('kode_mapel', $input['kode_mapel'])
                ->where('status', true)
                ->where('id', '!=', $materi->id)
                ->exists();

            if ($kodeSudahAda) {
                return [
                    'status' => false,
                    'message' => 'Kode mata pelajaran '.$input['kode_mapel'].' sudah digunakan oleh data aktif lainnya.',
                ];
            }

            $materi->update([
                'lembaga_id'  => $input['lembaga_id'],
                'kode_mapel'  => $input['kode_mapel'],
                'nama_mapel'  => $input['nama_mapel'],
                'pengajar_id'  => $input['pengajar_id'],
                'updated_by'  => Auth::id(),
                'updated_at'  => now(),
            ]);

            return [
                'status' => true,
                'message' => 'Mata pelajaran berhasil diperbarui.',
                'data' => $materi->fresh(),
            ];
        } catch (\Exception $e) {
            Log::error('Gagal update mata pelajaran: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'Gagal memperbarui mata pelajaran.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function store(array $data, string $bioId): array
    {
        // 1. Cek apakah masih ada santri aktif untuk biodata ini
        $santriAktif = Santri::where('biodata_id', $bioId)
            ->where('status', 'aktif')
            ->first();

        if ($santriAktif) {
            return [
                'status' => false,
                'message' => 'Data masih terdaftar sebagai Santri aktif. Tidak bisa menjadi Pengajar',
            ];
        }

        // 2. Cek apakah sudah ada pengajar aktif untuk biodata ini
        $exist = Pengajar::whereHas('pegawai', fn ($q) => $q->where('biodata_id', $bioId))
            ->where('status_aktif', 'aktif')
            ->first();

        if ($exist) {
            return [
                'status' => false,
                'message' => 'Pegawai masih memiliki Pengajar aktif',
            ];
        }

        // 3. Cari pegawai berdasarkan biodata
        $pegawai = Pegawai::where('biodata_id', $bioId)->latest()->first();

        if (! $pegawai) {
            return [
                'status' => false,
                'message' => 'Pegawai tidak ditemukan untuk biodata ini',
            ];
        }

        // 4. Validasi kode mata pelajaran (sebelum transaksi)
        foreach ($data['mata_pelajaran'] ?? [] as $mapel) {
            $mapelAktif = MataPelajaran::where('kode_mapel', $mapel['kode_mapel'])
                ->where('status', true)
                ->first();

            if ($mapelAktif) {
                return [
                    'status' => false,
                    'message' => 'Kode mata pelajaran ' . $mapel['kode_mapel'] . ' sudah digunakan untuk mata pelajaran "' . $mapelAktif->nama_mapel . '".',
                ];
            }
        }

        // 5. Eksekusi penyimpanan dengan transaksi
        try {
            return DB::transaction(function () use ($data, $pegawai) {
                $pengajar = Pengajar::create([
                    'pegawai_id'   => $pegawai->id,
                    'golongan_id'  => $data['golongan_id'],
                    'lembaga_id'   => $data['lembaga_id'],
                    'jabatan'      => $data['jabatan'],
                    'tahun_masuk'  => $data['tahun_masuk'] ?? now(),
                    'status_aktif' => 'aktif',
                    'created_by'   => Auth::id(),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                // Simpan mata pelajaran
                foreach ($data['mata_pelajaran'] ?? [] as $mapel) {
                    MataPelajaran::create([
                        'lembaga_id'   => $pengajar->lembaga_id,
                        'kode_mapel'   => $mapel['kode_mapel'],
                        'nama_mapel'   => $mapel['nama_mapel'] ?? '(tidak diketahui)',
                        'pengajar_id'  => $pengajar->id,
                        'status'       => true,
                        'created_by'   => Auth::id(),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }

                return [
                    'status' => true,
                    'data' => $pengajar->fresh()->load('mataPelajaran'),
                ];
            });
        } catch (\Throwable $e) {
            DB::rollBack();

            return [
                'status'  => false,
                'message' => 'Gagal menyimpan data pengajar.',
                'error'   => $e->getMessage(),
            ];
        }
    }
    public function pindahPengajar(array $input, int $id): array
    {
        // Validasi awal data
        $old = Pengajar::with('mataPelajaran')->find($id);
        if (! $old) {
            return ['status' => false, 'message' => 'Data tidak ditemukan.'];
        }

        if ($old->tahun_akhir) {
            return [
                'status' => false,
                'message' => 'Data pengajar sudah memiliki tahun akhir, tidak dapat diganti.',
            ];
        }

        $tahunMasukBaru = Carbon::parse($input['tahun_masuk'] ?? '');
        $hariIni = Carbon::now();

        if ($tahunMasukBaru->lt($hariIni)) {
            return [
                'status' => false,
                'message' => 'Tahun masuk baru tidak boleh sebelum hari ini.',
            ];
        }

        // Validasi kode mata pelajaran duluan (di luar transaksi)
        foreach ($input['mata_pelajaran'] ?? [] as $mapel) {
            $mapelAktif = MataPelajaran::where('kode_mapel', $mapel['kode_mapel'])
                ->where('status', true)
                ->first();

            if ($mapelAktif) {
                return [
                    'status' => false,
                    'message' => 'Kode mata pelajaran ' . $mapel['kode_mapel'] . ' sudah digunakan untuk mata pelajaran "' . $mapelAktif->nama_mapel . '".',
                ];
            }
        }

        // Eksekusi perubahan dalam transaksi
        try {
            return DB::transaction(function () use ($input, $id, $old, $tahunMasukBaru, $hariIni) {
                // Hapus jadwal & nonaktifkan mapel lama
                foreach ($old->mataPelajaran as $mapel) {
                    $mapel->jadwalPelajaran()->delete();

                    $mapel->update([
                        'status'     => false,
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);
                }

                // Nonaktifkan data pengajar lama
                $old->update([
                    'status_aktif' => 'tidak aktif',
                    'tahun_akhir'  => $hariIni,
                    'updated_by'   => Auth::id(),
                ]);

                // Buat pengajar baru
                $new = Pengajar::create([
                    'pegawai_id'   => $old->pegawai_id,
                    'golongan_id'  => $input['golongan_id'],
                    'lembaga_id'   => $input['lembaga_id'],
                    'jabatan'      => $input['jabatan'] ?? $old->jabatan,
                    'tahun_masuk'  => $tahunMasukBaru,
                    'status_aktif' => 'aktif',
                    'created_by'   => Auth::id(),
                ]);

                // Simpan mata pelajaran baru
                foreach ($input['mata_pelajaran'] ?? [] as $mapel) {
                    MataPelajaran::create([
                        'lembaga_id'   => $new->lembaga_id, // <-- tambahkan baris ini
                        'kode_mapel'   => $mapel['kode_mapel'],
                        'nama_mapel'   => $mapel['nama_mapel'] ?? '(tidak diketahui)',
                        'pengajar_id'  => $new->id,
                        'status'       => true,
                        'created_by'   => Auth::id(),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }

                return [
                    'status' => true,
                    'message' => 'Pengajar berhasil dipindah dan mata pelajaran ditambahkan.',
                    'data'   => $new->load('mataPelajaran'),
                ];
            });
        } catch (\Throwable $e) {
            DB::rollBack(); // optional karena transaction() sudah auto rollback
            return [
                'status'  => false,
                'message' => 'Gagal memindah pengajar.',
                'error'   => $e->getMessage(),
            ];
        }
    }


    public function keluarPengajar(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $pengajar = Pengajar::with('mataPelajaran.jadwalPelajaran')->find($id);

            if (! $pengajar) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if ($pengajar->tahun_akhir) {
                return [
                    'status' => false,
                    'message' => 'Data pengajar sudah ditandai selesai/nonaktif.',
                ];
            }

            $tahunAkhir = Carbon::parse($input['tahun_akhir'] ?? '');

            if ($tahunAkhir->lt(Carbon::parse($pengajar->tahun_masuk))) {
                return [
                    'status' => false,
                    'message' => 'Tahun akhir tidak boleh sebelum tahun masuk.',
                ];
            }

            // Nonaktifkan pengajar
            $pengajar->update([
                'status_aktif' => 'tidak aktif',
                'tahun_akhir'  => $tahunAkhir,
                'updated_by'   => Auth::id(),
            ]);

            // Nonaktifkan mata pelajaran dan hapus jadwal terkait
            foreach ($pengajar->mataPelajaran as $mapel) {
                $mapel->jadwalPelajaran()->delete(); // Hapus jadwal secara permanen

                $mapel->update([
                    'status'     => false,
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);
            }

            return [
                'status' => true,
                'message' => 'Pengajar berhasil dinonaktifkan dan mata pelajaran dinonaktifkan.',
                'data'   => $pengajar->load('mataPelajaran.jadwalPelajaran'),
            ];
        });
    }
    public function nonaktifkanMataPelajaran(string $pengajarId, string $mataPelajaranId): array
    {
        $mapel = MataPelajaran::with('jadwalPelajaran')
            ->where('pengajar_id', $pengajarId)
            ->where('id', $mataPelajaranId)
            ->first();

        if (! $mapel) {
            return ['status' => false, 'message' => 'Mata pelajaran tidak ditemukan.'];
        }

        DB::beginTransaction();
        try {
            // Hapus semua jadwal pelajaran terkait secara permanen
            $mapel->jadwalPelajaran()->delete();

            // Nonaktifkan mata pelajaran (asumsikan kolom 'status' adalah boolean)
            $mapel->status = false;
            $mapel->save();

            DB::commit();

            return [
                'status' => true,
                'message' => 'Mata pelajaran berhasil dinonaktifkan dan jadwalnya dihapus.',
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            return [
                'status' => false,
                'message' => 'Terjadi kesalahan saat menonaktifkan mata pelajaran.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function tambahMataPelajaran(string $pengajarId, array $input): array
    {
        $pengajar = Pengajar::find($pengajarId);

        if (! $pengajar) {
            return [
                'status' => false,
                'message' => 'Pengajar tidak ditemukan.'
            ];
        }

        if (empty($input['mata_pelajaran']) || ! is_array($input['mata_pelajaran'])) {
            return [
                'status' => false,
                'message' => 'Data mata pelajaran tidak valid.'
            ];
        }

        try {
            DB::beginTransaction();

            foreach ($input['mata_pelajaran'] as $mapelInput) {
                // Cari data mapel aktif dengan kode yang sama
                $mapelAktif = MataPelajaran::where('kode_mapel', $mapelInput['kode_mapel'])
                    ->where('status', true)
                    ->first();

                if ($mapelAktif) {
                    DB::rollBack();
                    return [
                        'status'  => false,
                        'message' => 'Kode mata pelajaran ' . $mapelInput['kode_mapel'] . ' sudah digunakan untuk mata pelajaran "' . $mapelAktif->nama_mapel . '".',
                    ];
                }

                MataPelajaran::create([
                    'lembaga_id'   => $pengajar->lembaga_id, // Tambahkan ini!
                    'kode_mapel'   => $mapelInput['kode_mapel'],
                    'nama_mapel'   => $mapelInput['nama_mapel'],
                    'pengajar_id'  => $pengajar->id,
                    'status'       => true,
                    'created_by'   => Auth::id(),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            DB::commit();

            return [
                'status'  => true,
                'message' => 'Mata pelajaran berhasil ditambahkan.',
                'data'    => $pengajar->load('mataPelajaran'),
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            return [
                'status'  => false,
                'message' => 'Terjadi kesalahan saat menambahkan mata pelajaran.',
                'error'   => $e->getMessage(),
            ];
        }
    }
}
