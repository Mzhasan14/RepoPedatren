<?php

namespace App\Services\Pegawai\Filters\Formulir;

use App\Models\Pegawai\MateriAjar;
use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\Pengajar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PengajarService
{
    public function index(string $bioId): array
    {
        $pengajar = Pengajar::with('materiAjar')
            ->whereHas('pegawai.biodata', function ($query) use ($bioId) {
                $query->where('id', $bioId);
            })
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'lembaga_id' => $p->lembaga_id,
                    'golongan_id' => $p->golongan_id,
                    'jabatan_kontrak' => $p->jabatan,
                    'tanggal_masuk' => $p->tahun_masuk,
                    'tanggal_keluar' => $p->tahun_akhir,
                    'status' => $p->status_aktif,
                    'nama_materi' => $p->materiAjar->pluck('nama_materi')->join(', '),
                    'jumlah_menit' => $p->materiAjar->sum('jumlah_menit'),
                ];
            });

        return ['status' => true, 'data' => $pengajar];
    }
    public function edit(string $pengajarId): array
    {
        $pengajar = Pengajar::with('materiAjar')
            ->find($pengajarId);

        if (!$pengajar) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
                'data' => null
            ];
        }

        $materi = $pengajar->materiAjar->map(function ($m) {
            return [
                'id' => $m->id,
                'nama_materi' => $m->nama_materi,
                'jumlah_menit' => $m->jumlah_menit,
                'tahun_masuk' => $m->tahun_masuk,
                'tahun_akhir' => $m->tahun_akhir,
            ];
        });

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
            ]
        ];
    }


    public function update(array $data, string $id)
    {
        $pengajar = Pengajar::with(['materiAjar' => function ($q) {
            $q->whereNull('tahun_akhir');
        }])->find($id);

        if (!$pengajar) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        if (!is_null($pengajar->tahun_akhir)) {
            return ['status' => false, 'message' => 'Data riwayat tidak boleh diubah!'];
        }

        $before = $pengajar->toArray();
        $batchUuid = Str::uuid()->toString();

        // Manual tanggal keluar
        if (!empty($data['tahun_akhir_pengajar']) && !empty($data['tahun_akhir_materi_ajar'])) {
            $tanggalMasuk = strtotime($pengajar->tahun_masuk);
            $tanggalKeluarPengajar = strtotime($data['tahun_akhir_pengajar']);
            $tanggalKeluarMateri = strtotime($data['tahun_akhir_materi_ajar']);

            if ($tanggalKeluarPengajar < $tanggalMasuk || $tanggalKeluarMateri < $tanggalMasuk) {
                return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk.'];
            }

            $pengajar->update([
                'tahun_akhir' => $data['tahun_akhir_pengajar'],
                'status_aktif' => 'tidak aktif',
                'updated_by' => Auth::id(),
            ]);

            $pengajar->materiAjar->each(function ($materi) use ($data) {
                $materi->update([
                    'tahun_akhir' => $data['tahun_akhir_materi_ajar'],
                    'status_aktif' => 'tidak aktif',
                    'updated_by' => Auth::id(),
                ]);
            });

            activity('pengajar_update_manual')
                ->performedOn($pengajar)
                ->withProperties([
                    'before' => $before,
                    'after' => $pengajar->toArray(),
                ])
                ->event('update_pengajar')
                ->tap(fn ($act) => $act->batch_uuid = $batchUuid)
                ->log('Pengajar dan materi ajar diupdate(keluar).');

            return ['status' => true, 'data' => $pengajar->fresh()];
        }

        // Deteksi perubahan
        $isGolonganChanged = $pengajar->golongan_id != $data['golongan_id'];
        $isLembagaChanged = $pengajar->lembaga_id != $data['lembaga_id'];
        $isJabatanChanged = $pengajar->jabatan != ($data['jabatan'] ?? $pengajar->jabatan);

        $materiLama = $pengajar->materiAjar->first();
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
            $pengajar->update([
                'status_aktif' => 'tidak aktif',
                'tahun_akhir' => now(),
                'updated_by' => Auth::id(),
            ]);

            $pengajar->materiAjar->each(function ($materi) {
                $materi->update([
                    'tahun_akhir' => now(),
                    'status_aktif' => 'tidak aktif',
                    'updated_by' => Auth::id(),
                ]);
            });

            activity('pengajar_update')
                ->performedOn($pengajar)
                ->withProperties([
                    'before' => $before,
                    'after' => $pengajar->toArray(),
                ])
                ->event('update_pengajar')
                ->tap(fn ($act) => $act->batch_uuid = $batchUuid)
                ->log('Data pengajar lama dinonaktifkan karena ada perubahan.');

            // Buat data baru
            $newPengajar = Pengajar::create([
                'pegawai_id' => $pengajar->pegawai_id,
                'lembaga_id' => $data['lembaga_id'] ?? $pengajar->lembaga_id,
                'golongan_id' => $data['golongan_id'] ?? $pengajar->golongan_id,
                'jabatan' => $data['jabatan'] ?? $pengajar->jabatan,
                'tahun_masuk' => now(),
                'status_aktif' => 'aktif',
                'created_by' => Auth::id(),
            ]);

            activity('pengajar_create')
                ->performedOn($newPengajar)
                ->withProperties([
                    'before' => null,
                    'after' => $newPengajar->toArray(),
                ])
                ->event('create_pengajar')
                ->tap(fn ($act) => $act->batch_uuid = $batchUuid)
                ->log('Data pengajar baru dibuat setelah perubahan.');

            if (isset($data['nama_materi']) && is_array($data['nama_materi'])) {
                foreach ($data['nama_materi'] as $i => $materi) {
                    $materiBaru = $newPengajar->materiAjar()->create([
                        'nama_materi' => $materi,
                        'jumlah_menit' => $data['jumlah_menit'][$i] ?? 0,
                        'status_aktif' => 'aktif',
                        'tahun_masuk' => now(),
                        'created_by' => Auth::id(),
                    ]);

                    activity('materi_ajar_create')
                        ->performedOn($materiBaru)
                        ->withProperties([
                            'before' => null,
                            'after' => $materiBaru->toArray(),
                        ])
                        ->event('create_materi_ajar')
                        ->tap(fn ($act) => $act->batch_uuid = $batchUuid)
                        ->log('Materi ajar baru dibuat.');
                }
            } else {
                $materiBaru = $newPengajar->materiAjar()->create([
                    'nama_materi' => $data['nama_materi'] ?? ($materiLama->nama_materi ?? null),
                    'jumlah_menit' => $data['jumlah_menit'] ?? ($materiLama->jumlah_menit ?? 0),
                    'status_aktif' => 'aktif',
                    'tahun_masuk' => now(),
                    'created_by' => Auth::id(),
                ]);

                activity('materi_ajar_create')
                    ->performedOn($materiBaru)
                    ->withProperties([
                        'before' => null,
                        'after' => $materiBaru->toArray(),
                    ])
                    ->event('create_materi_ajar')
                    ->tap(fn ($act) => $act->batch_uuid = $batchUuid)
                    ->log('Materi ajar baru dibuat.');
            }

            return ['status' => true, 'data' => $newPengajar->fresh('materiAjar')];
        }

        return ['status' => false, 'message' => 'Tidak ada perubahan yang dilakukan.'];
        }

        public function store(array $data, string $bioId)
        {
            $batchUuid = Str::uuid()->toString();

            // Cek apakah sudah ada pengajar aktif
            $exist = Pengajar::whereHas('pegawai.biodata', function ($query) use ($bioId) {
                    $query->where('id', $bioId);
                })
                ->where('status_aktif', 'aktif')
                ->first();

            if ($exist) {
                return ['status' => false, 'message' => 'Pegawai masih memiliki Pengajar aktif!'];
            }

            // Ambil pegawai berdasarkan biodata_id
            $pegawai = Pegawai::where('biodata_id', $bioId)->latest()->first();

            if (!$pegawai) {
                return ['status' => false, 'message' => 'Pegawai tidak ditemukan untuk biodata ini'];
            }

            // Buat pengajar baru
            $pengajar = Pengajar::create([
                'pegawai_id' => $pegawai->id,
                'lembaga_id' => $data['lembaga_id'],
                'golongan_id' => $data['golongan_id'],
                'jabatan' => $data['jabatan'],
                'tahun_masuk' => $data['tahun_masuk'] ?? now(),
                'status_aktif' => 'aktif',
                'created_by' => Auth::id(),
            ]);

            activity('pengajar_create')
                ->performedOn($pengajar)
                ->causedBy(Auth::user())
                ->withProperties([
                    'before' => null,
                    'after' => $pengajar->toArray(),
                ])
                ->event('create_pengajar')
                ->tap(fn ($act) => $act->batch_uuid = $batchUuid)
                ->log('Pengajar baru ditambahkan.');

            // Materi Ajar
            if (isset($data['nama_materi']) && is_array($data['nama_materi'])) {
                foreach ($data['nama_materi'] as $index => $nama) {
                    $materi = MateriAjar::create([
                        'pengajar_id' => $pengajar->id,
                        'nama_materi' => $nama,
                        'jumlah_menit' => $data['jumlah_menit'][$index] ?? 0,
                        'tahun_masuk' => now(),
                        'status_aktif' => 'aktif',
                        'created_by' => Auth::id(),
                    ]);

                    activity('materi_ajar_create')
                        ->performedOn($materi)
                        ->causedBy(Auth::user())
                        ->withProperties([
                            'before' => null,
                            'after' => $materi->toArray(),
                        ])
                        ->event('create_materi_ajar')
                        ->tap(fn ($act) => $act->batch_uuid = $batchUuid)
                        ->log('Materi ajar ditambahkan.');
                }
            } else {
                $materi = MateriAjar::create([
                    'pengajar_id' => $pengajar->id,
                    'nama_materi' => $data['nama_materi'],
                    'jumlah_menit' => $data['jumlah_menit'],
                    'tahun_masuk' => now(),
                    'status_aktif' => 'aktif',
                    'created_by' => Auth::id(),
                ]);

                activity('materi_ajar_create')
                    ->performedOn($materi)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'before' => null,
                        'after' => $materi->toArray(),
                    ])
                    ->event('create_materi_ajar')
                    ->tap(fn ($act) => $act->batch_uuid = $batchUuid)
                    ->log('Materi ajar ditambahkan.');
            }

            return ['status' => true, 'data' => $pengajar->load('materiAjar')];
    }


}