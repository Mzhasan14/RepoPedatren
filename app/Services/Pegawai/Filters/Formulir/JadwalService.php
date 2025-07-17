<?php

namespace App\Services\Pegawai\Filters\Formulir;

use App\Models\Pegawai\JadwalPelajaran;
use App\Models\Pegawai\JamPelajaran;
use App\Models\Pegawai\MataPelajaran;
use App\Models\Pegawai\Pengajar;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Lembaga;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
class JadwalService
{
    public function getJadwalByMateriId($materiId): array
    {
        $jadwal = DB::table('jadwal_pelajaran')
            ->join('mata_pelajaran', 'mata_pelajaran.id', '=', 'jadwal_pelajaran.mata_pelajaran_id')
            ->join('lembaga', 'lembaga.id', '=', 'jadwal_pelajaran.lembaga_id')
            ->join('jurusan', 'jurusan.id', '=', 'jadwal_pelajaran.jurusan_id')
            ->join('kelas', 'kelas.id', '=', 'jadwal_pelajaran.kelas_id')
            ->join('jam_pelajaran', 'jam_pelajaran.id', '=', 'jadwal_pelajaran.jam_pelajaran_id')
            ->where('mata_pelajaran.id', $materiId)
            ->select(
                'jadwal_pelajaran.id',
                'lembaga.nama_lembaga',
                'jurusan.nama_jurusan',
                'kelas.nama_kelas',
                'mata_pelajaran.kode_mapel',
                'mata_pelajaran.nama_mapel',
                'jam_pelajaran.jam_ke',
                'jam_pelajaran.jam_mulai',
                'jam_pelajaran.jam_selesai',
                'jadwal_pelajaran.hari'
            )
            ->orderByRaw("FIELD(jadwal_pelajaran.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('jam_pelajaran.jam_ke')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_lembaga' => $item->nama_lembaga,
                    'nama_jurusan' => $item->nama_jurusan,
                    'nama_kelas' => $item->nama_kelas,
                    'kode_mapel' => $item->kode_mapel,
                    'nama_mapel' => $item->nama_mapel,
                    'jam_ke' => $item->jam_ke,
                    'jam' => $item->jam_mulai . ' - ' . $item->jam_selesai,
                    'hari' => $item->hari,
                ];
            });

        return [
            'status' => true,
            'data' => $jadwal,
        ];
    }
    public function simpanJadwalPengajar(array $input, $new, $materiId, $mapelName): array
    {
        DB::beginTransaction();

        try {
            foreach ($input['jadwal'] ?? [] as $jadwal) {
                $hari = $jadwal['hari'] ?? 'Hari tidak diketahui';

                // Validasi bentrok kelas berdasarkan data dari database
                $jadwalBentrokKelas = JadwalPelajaran::with(['kelas', 'jurusan', 'lembaga', 'jamPelajaran'])
                    ->where('hari', $hari)
                    ->where('kelas_id', $jadwal['kelas_id'])
                    ->where('jam_pelajaran_id', $jadwal['jam_pelajaran_id'])
                    ->where(function ($query) use ($materiId) {
                        $query->whereNull('mata_pelajaran_id')
                            ->orWhere('mata_pelajaran_id', '!=', $materiId);
                    })
                    ->first();

                if ($jadwalBentrokKelas) {
                    $kelasNama   = optional($jadwalBentrokKelas->kelas)->nama_kelas ?? "(Kelas tidak ditemukan)";
                    $jurusanNama = optional($jadwalBentrokKelas->jurusan)->nama_jurusan ?? "(Jurusan tidak ditemukan)";
                    $lembagaNama = optional($jadwalBentrokKelas->lembaga)->nama_lembaga ?? "(Lembaga tidak ditemukan)";
                    $jamKe       = optional($jadwalBentrokKelas->jamPelajaran)->jam_ke ?? "(Jam tidak ditemukan)";

                    throw new \Exception("Gagal menambahkan '$mapelName': kelas $kelasNama ($jurusanNama - $lembagaNama) sudah memiliki jadwal pada hari $hari, jam ke-$jamKe.");
                }

                // Validasi bentrok pengajar berdasarkan data dari database
                $jadwalBentrokPengajar = JadwalPelajaran::with(['kelas', 'jurusan', 'lembaga', 'jamPelajaran', 'mataPelajaran'])
                    ->where('hari', $hari)
                    ->where('jam_pelajaran_id', $jadwal['jam_pelajaran_id'])
                    ->whereHas('mataPelajaran', function ($q) use ($new) {
                        $q->where('pengajar_id', $new->id);
                    })
                    ->where('mata_pelajaran_id', '!=', $materiId)
                    ->first();

                if ($jadwalBentrokPengajar) {
                    $kelasNama   = optional($jadwalBentrokPengajar->kelas)->nama_kelas ?? "(Kelas tidak ditemukan)";
                    $jurusanNama = optional($jadwalBentrokPengajar->jurusan)->nama_jurusan ?? "(Jurusan tidak ditemukan)";
                    $lembagaNama = optional($jadwalBentrokPengajar->lembaga)->nama_lembaga ?? "(Lembaga tidak ditemukan)";
                    $jamKe       = optional($jadwalBentrokPengajar->jamPelajaran)->jam_ke ?? "(Jam tidak ditemukan)";

                    throw new \Exception("Gagal menambahkan '$mapelName': pengajar sudah mengajar pada hari $hari, jam ke-$jamKe di kelas $kelasNama ($jurusanNama - $lembagaNama).");
                }

                // Simpan data ke database
                JadwalPelajaran::create([
                    'hari'                => $hari,
                    'semester_id'         => $jadwal['semester_id'],
                    'lembaga_id'          => $input['lembaga_id'],
                    'jurusan_id'          => $jadwal['jurusan_id'],
                    'kelas_id'            => $jadwal['kelas_id'],
                    'rombel_id'           => $jadwal['rombel_id'] ?? null,
                    'mata_pelajaran_id'   => $materiId,
                    'jam_pelajaran_id'    => $jadwal['jam_pelajaran_id'],
                    'created_by'          => Auth::id(),
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            DB::commit();

            return [
                'status' => true,
                'data' => $new->load('mataPelajaran.jadwalPelajaran'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal menambahkan jadwal pelajaran: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    public function hapusJadwalPelajaran(int $jadwalId): array
    {
        try {
            $jadwal = JadwalPelajaran::findOrFail($jadwalId);
            $jadwal->delete();

            return [
                'status' => true,
                'message' => 'Jadwal pelajaran berhasil dihapus.',
            ];
        } catch (\Exception $e) {
            Log::error('Gagal menghapus jadwal pelajaran: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'Gagal menghapus jadwal pelajaran: ' . $e->getMessage(),
            ];
        }
    }
    public function updateJadwalPelajaran(array $input, int $jadwalId): array
    {
        DB::beginTransaction();

        try {
            $jadwal = JadwalPelajaran::with('mataPelajaran')->findOrFail($jadwalId);
            $materiId   = $jadwal->mata_pelajaran_id;
            $pengajarId = optional($jadwal->mataPelajaran)->pengajar_id;
            $mapelName  = optional($jadwal->mataPelajaran)->nama_mapel ?? '(Tidak diketahui)';

            $hari = $input['hari'];

            // Ambil data jam untuk keperluan pesan
            $jam = JamPelajaran::find($input['jam_pelajaran_id']);
            $jamKe = optional($jam)->jam_ke ?? "(Jam tidak diketahui)";

            // Validasi bentrok kelas
            $bentrokKelas = JadwalPelajaran::with(['kelas', 'jurusan', 'lembaga', 'jamPelajaran'])
                ->where('hari', $hari)
                ->where('kelas_id', $input['kelas_id'])
                ->where('jam_pelajaran_id', $input['jam_pelajaran_id'])
                ->where('id', '!=', $jadwalId)
                ->first();

            if ($bentrokKelas) {
                $kelasNama   = optional($bentrokKelas->kelas)->nama_kelas ?? "(kelas tidak diketahui)";
                $jurusanNama = optional($bentrokKelas->jurusan)->nama_jurusan ?? "(jurusan tidak diketahui)";
                $lembagaNama = optional($bentrokKelas->lembaga)->nama_lembaga ?? "(lembaga tidak diketahui)";
                $jamKe       = optional($bentrokKelas->jamPelajaran)->jam_ke ?? $jamKe;

                throw new \Exception(
                    "Jadwal tidak dapat diperbarui: hari $hari, jam ke-$jamKe sudah digunakan oleh kelas $kelasNama di jurusan $jurusanNama ($lembagaNama)."
                );
            }

            // Validasi bentrok pengajar
            $bentrokPengajar = JadwalPelajaran::with(['kelas', 'jurusan', 'lembaga', 'jamPelajaran'])
                ->where('hari', $hari)
                ->where('jam_pelajaran_id', $input['jam_pelajaran_id'])
                ->where('id', '!=', $jadwalId)
                ->whereHas('mataPelajaran', fn($q) => $q->where('pengajar_id', $pengajarId))
                ->first();

            if ($bentrokPengajar) {
                $kelasNama   = optional($bentrokPengajar->kelas)->nama_kelas ?? "(kelas tidak diketahui)";
                $jurusanNama = optional($bentrokPengajar->jurusan)->nama_jurusan ?? "(jurusan tidak diketahui)";
                $lembagaNama = optional($bentrokPengajar->lembaga)->nama_lembaga ?? "(lembaga tidak diketahui)";
                $jamKe       = optional($bentrokPengajar->jamPelajaran)->jam_ke ?? $jamKe;

                throw new \Exception(
                    "Pengajar tidak dapat dijadwalkan ulang: sudah mengajar pada hari $hari, jam ke-$jamKe di kelas $kelasNama ($jurusanNama - $lembagaNama)."
                );
            }

            // Update jadwal
            $jadwal->update([
                'hari'               => $hari,
                'semester_id'        => $input['semester_id'],
                'lembaga_id'         => $input['lembaga_id'],
                'jurusan_id'         => $input['jurusan_id'],
                'kelas_id'           => $input['kelas_id'],
                'rombel_id'          => $input['rombel_id'] ?? null,
                'jam_pelajaran_id'   => $input['jam_pelajaran_id'],
                'updated_by'         => Auth::id(),
                'updated_at'         => now(),
            ]);

            DB::commit();

            return [
                'status' => true,
                'message' => 'Jadwal pelajaran berhasil diperbarui.',
                'data' => $jadwal->fresh(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal update jadwal pelajaran: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    public function list()
    {
        return JamPelajaran::orderBy('jam_ke')
            ->select('id', 'jam_ke', 'label', 'jam_mulai', 'jam_selesai')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'jam_ke' => $item->jam_ke,
                    'label' => $item->label,
                    'jam_mulai' => $item->jam_mulai,
                    'jam_selesai' => $item->jam_selesai,
                ];
            });
    }
    public function create(array $input)
    {
        DB::beginTransaction();

        try {
            // Validasi jam_ke sudah digunakan
            $existing = JamPelajaran::where('jam_ke', $input['jam_ke'])->exists();
            if ($existing) {
                return [
                    'status' => false,
                    'message' => 'Maaf, Jam ke-' . $input['jam_ke'] . ' sudah digunakan. Silakan pilih nomor jam pelajaran lain.'
                ];
            }

            // Validasi jam_mulai dan jam_selesai tidak overlap dengan yang sudah ada
            $overlap = JamPelajaran::where(function($query) use ($input) {
                $query->where('jam_mulai', '<', $input['jam_selesai'])
                    ->where('jam_selesai', '>', $input['jam_mulai']);
            })->exists();

            if ($overlap) {
                return [
                    'status' => false,
                    'message' => 'Rentang waktu ' . $input['jam_mulai'] . ' - ' . $input['jam_selesai'] . ' tumpang tindih dengan jam pelajaran lain. Silakan pilih waktu yang tidak bertabrakan.'
                ];
            }

            $jam = JamPelajaran::create([
                'jam_ke' => $input['jam_ke'],
                'label' => $input['label'] ?? null,
                'jam_mulai' => $input['jam_mulai'],
                'jam_selesai' => $input['jam_selesai'],
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return ['status' => true, 'data' => $jam];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menambah jam pelajaran: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'Terjadi kesalahan saat menambah jam pelajaran. Silakan coba lagi.'
            ];
        }
    }
    public function show($id)
    {
        $jam = JamPelajaran::select('id', 'jam_ke', 'label', 'jam_mulai', 'jam_selesai')->find($id);

        if (!$jam) {
            return ['status' => false, 'message' => 'Jam pelajaran tidak ditemukan.'];
        }

        return [
            'status' => true,
            'data' => [
                'id' => $jam->id,
                'jam_ke' => $jam->jam_ke,
                'label' => $jam->label,
                'jam_mulai' => $jam->jam_mulai,
                'jam_selesai' => $jam->jam_selesai,
            ],
        ];
    }
    public function update($id, array $input)
    {
        DB::beginTransaction();

        try {
            $jam = JamPelajaran::findOrFail($id);

            // Cek apakah jam_ke sudah digunakan oleh jam pelajaran lain
            $existing = JamPelajaran::where('jam_ke', $input['jam_ke'])
                ->where('id', '!=', $id)
                ->exists();

            if ($existing) {
                return [
                    'status' => false,
                    'message' => 'Jam ke-' . $input['jam_ke'] . ' sudah terdaftar.',
                ];
            }

            $jam->update([
                'jam_ke' => $input['jam_ke'],
                'label' => $input['label'] ?? null,
                'jam_mulai' => $input['jam_mulai'],
                'jam_selesai' => $input['jam_selesai'],
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return ['status' => true, 'data' => $jam];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal mengubah jam pelajaran: ' . $e->getMessage());

            return ['status' => false, 'message' => 'Gagal mengubah jam pelajaran.'];
        }
    }
    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $jam = JamPelajaran::findOrFail($id);
            $jam->delete();

            DB::commit();

            return ['status' => true, 'message' => 'Jam pelajaran berhasil dihapus.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus jam pelajaran: ' . $e->getMessage());

            return ['status' => false, 'message' => 'Gagal menghapus jam pelajaran.'];
        }
    }
    public function getAllMapelQuery(Request $request)
    {
        return DB::table('mata_pelajaran as mp')
            ->leftJoin('lembaga as l', 'l.id', '=', 'mp.lembaga_id')
            ->leftJoin('pengajar as p', 'p.id', '=', 'mp.pengajar_id')
            ->leftJoin('pegawai as pg', 'pg.id', '=', 'p.pegawai_id')
            ->leftJoin('biodata as b', 'b.id', '=', 'pg.biodata_id')
            ->where('mp.status',1)
            ->select([
                'mp.id',
                'l.id as lembaga_id',
                'l.nama_lembaga',
                'mp.kode_mapel',
                'mp.nama_mapel',
                'p.id as pengajar_id',
                'b.nama as nama_pengajar',
                'b.nik as nik_pengajar',
                'mp.status'
            ])
            ->orderBy('l.nama_lembaga');
    }

    public function formatData($results)
    {
        return $results->map(function ($item) {
            return [
                'id' => $item->id,
                'lembaga_id' => $item->lembaga_id,
                'lembaga' => $item->nama_lembaga,
                'kode_mapel' => $item->kode_mapel,
                'nama_mapel' => $item->nama_mapel,
                'pengajar_id' => $item->pengajar_id,
                'nama_pengajar' => $item->nama_pengajar ?? '-',
                'nik_pengajar' => $item->nik_pengajar ?? '-',
                'status' => $item->status == 1 ? 'Aktif' : 'Tidak Aktif',
            ];
        });
    }

    public function createMataPelajaran(array $input): array
    {
        if (empty($input['pengajar_id'])) {
            return [
                'status' => false,
                'message' => 'Pengajar harus dipilih.',
            ];
        }

        $pengajar = Pengajar::find($input['pengajar_id']);

        if (!$pengajar) {
            return [
                'status' => false,
                'message' => 'Pengajar tidak ditemukan.',
            ];
        }

        // Tambahkan validasi status_aktif = 'aktif'
        if ($pengajar->status_aktif !== 'aktif') {
            return [
                'status' => false,
                'message' => 'Pengajar tidak aktif.',
            ];
        }

        if (empty($input['mata_pelajaran']) || !is_array($input['mata_pelajaran'])) {
            return [
                'status' => false,
                'message' => 'Data mata pelajaran tidak valid.',
            ];
        }

        // Cek duplikat kode_mapel dalam array input
        $kodeMapelInput = array_column($input['mata_pelajaran'], 'kode_mapel');
        $duplikat = array_diff_assoc($kodeMapelInput, array_unique($kodeMapelInput));

        if (!empty($duplikat)) {
            return [
                'status' => false,
                'message' => 'Terdapat duplikat kode mata pelajaran dalam input.',
            ];
        }

        try {
            DB::beginTransaction();

            foreach ($input['mata_pelajaran'] as $mapel) {
                // Validasi: kode_mapel tidak boleh duplikat untuk data yang masih aktif
                $kodeSudahAda = MataPelajaran::where('kode_mapel', $mapel['kode_mapel'])
                    ->where('status', true)
                    ->exists();

                if ($kodeSudahAda) {
                    DB::rollBack();
                    return [
                        'status'  => false,
                        'message' => 'Kode mata pelajaran ' . $mapel['kode_mapel'] . ' sudah digunakan untuk data aktif.',
                    ];
                }

                MataPelajaran::create([
                    'lembaga_id'  => $mapel['lembaga_id'],
                    'kode_mapel'  => $mapel['kode_mapel'],
                    'nama_mapel'  => $mapel['nama_mapel'],
                    'pengajar_id' => $pengajar->id,
                    'status'      => true,
                    'created_by'  => Auth::id(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
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
            Log::error('Gagal menambahkan mata pelajaran: ' . $e->getMessage());

            return [
                'status'  => false,
                'message' => 'Terjadi kesalahan saat menambahkan mata pelajaran.',
                'error'   => $e->getMessage(),
            ];
        }
    }
    public function DestroyMapel(string $mataPelajaranId): array
    {
        $mapel = MataPelajaran::with('jadwalPelajaran')->find($mataPelajaranId);

        if (! $mapel) {
            return [
                'status' => false,
                'message' => 'Mata pelajaran tidak ditemukan.',
            ];
        }

        DB::beginTransaction();
        try {
            // Hapus semua jadwal pelajaran yang berelasi dengan mata pelajaran ini
            $mapel->jadwalPelajaran()->delete();

            // Update status mata pelajaran menjadi nonaktif
            $mapel->status = false; // atau bisa 'nonaktif' tergantung tipe kolom
            $mapel->save();

            DB::commit();

            return [
                'status' => true,
                'message' => 'Jadwal pelajaran berhasil dihapus dan mata pelajaran dinonaktifkan.',
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal memproses nonaktifkan mapel: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'Terjadi kesalahan saat menonaktifkan mata pelajaran.',
                'error' => $e->getMessage(),
            ];
        }
    }
    public function getAllJadwalQuery(Request $request)
    {
        return DB::table('jadwal_pelajaran as jp')
            ->leftJoin('semester', 'jp.semester_id', '=', 'semester.id')
            ->leftJoin('mata_pelajaran as mp', function ($join) {
                $join->on('mp.id', '=', 'jp.mata_pelajaran_id')
                    ->where('mp.status', true);
            })
            ->join('lembaga as l', 'l.id', '=', 'jp.lembaga_id')
            ->join('jurusan as j', 'j.id', '=', 'jp.jurusan_id')
            ->join('kelas as k', 'k.id', '=', 'jp.kelas_id')
            ->leftJoin('rombel as r', 'r.id', '=', 'jp.rombel_id')
            ->join('jam_pelajaran as jam', 'jam.id', '=', 'jp.jam_pelajaran_id')
            ->leftJoin('pengajar as p', 'p.id', '=', 'mp.pengajar_id')
            ->leftJoin('pegawai as pg', 'pg.id', '=', 'p.pegawai_id')
            ->leftJoin('biodata as b', 'b.id', '=', 'pg.biodata_id')
            ->when($request->lembaga_id, fn($query, $value) => $query->where('jp.lembaga_id', $value))
            ->when($request->jurusan_id, fn($query, $value) => $query->where('jp.jurusan_id', $value))
            ->when($request->kelas_id, fn($query, $value) => $query->where('jp.kelas_id', $value))
            ->when($request->rombel_id, fn($query, $value) => $query->where('jp.rombel_id', $value))
            ->when($request->semester_id, fn($query, $value) => $query->where('jp.semester_id', $value))
            ->select([
                'jp.id',
                'l.id as lembaga_id',
                'l.nama_lembaga',
                'j.id as jurusan_id',
                'j.nama_jurusan',
                'k.id as kelas_id',
                'k.nama_kelas',
                'r.id as rombel_id',
                'r.nama_rombel',
                'semester.semester',
                'mp.kode_mapel',
                'mp.nama_mapel',
                'b.nama as nama_pengajar',
                'b.nik as nik_pengajar',
                'jam.jam_ke',
                'jam.jam_mulai',
                'jam.jam_selesai',
                'jp.hari',
            ])
            ->orderByRaw("FIELD(jp.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('jam.jam_ke');
    }

    public function groupJadwalByHari($data)
    {
        $grouped = [];

        foreach ($data as $item) {
            $hari = $item->hari;

            if (!isset($grouped[$hari])) {
                $grouped[$hari] = [];
            }

            $grouped[$hari][] = [
                'id' => $item->id,
                'kode_mapel' => $item->kode_mapel,
                'nama_mapel' => $item->nama_mapel,
                'nama_pengajar' => $item->nama_pengajar,
                'jam_ke' => $item->jam_ke,
                'jam_mulai' => $item->jam_mulai,
                'jam_selesai' => $item->jam_selesai,
            ];
        }

        // Urutkan hari secara manual
        $orderedDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu','Minggu'];
        $sorted = [];

        foreach ($orderedDays as $day) {
            if (isset($grouped[$day])) {
                $sorted[$day] = $grouped[$day];
            }
        }

        return $sorted;
    }
    public function storeJadwalMataPelajaran(array $input): array
    {
        DB::beginTransaction();

        try {
            $now = now();
            $jadwalTersimpan = [];

            // Ambil informasi lembaga/jurusan/kelas/semester/rombel dari input global
            $lembagaId  = $input['lembaga_id'];
            $jurusanId  = $input['jurusan_id'];
            $kelasId    = $input['kelas_id'];
            $semesterId = $input['semester_id'];
            $rombelId   = $input['rombel_id'] ?? null;

            // Ambil nama lembaga dari input untuk error message
            $lembagaInput = Lembaga::find($lembagaId);
            $lembagaInputNama = $lembagaInput ? $lembagaInput->nama_lembaga : "(Lembaga tidak ditemukan)";

            foreach ($input['jadwal'] ?? [] as $jadwal) {
                $hari              = $jadwal['hari'] ?? 'Hari tidak diketahui';
                $jamPelajaranId    = $jadwal['jam_pelajaran_id'];
                $mataPelajaranId   = $jadwal['mata_pelajaran_id'];

                // Ambil data mata pelajaran beserta relasi lembaga
                $mataPelajaran = MataPelajaran::with('lembaga')->findOrFail($mataPelajaranId);
                $pengajarId = $mataPelajaran->pengajar_id;

                $lembagaMapelNama = $mataPelajaran->lembaga ? $mataPelajaran->lembaga->nama_lembaga : "(Lembaga tidak ditemukan)";

                // Validasi lembaga_id harus sama
                if ($mataPelajaran->lembaga_id != $lembagaId) {
                    throw new \Exception(
                        "Gagal menambahkan jadwal untuk '{$mataPelajaran->nama_mapel}': lembaga pada mata pelajaran ($lembagaMapelNama) tidak cocok dengan lembaga jadwal ($lembagaInputNama)."
                    );
                }

                // Validasi bentrok kelas
                $bentrokKelas = JadwalPelajaran::with(['kelas', 'jurusan', 'lembaga', 'jamPelajaran'])
                    ->where('hari', $hari)
                    ->where('kelas_id', $kelasId)
                    ->where('jam_pelajaran_id', $jamPelajaranId)
                    ->where('semester_id', $semesterId)
                    ->first();

                if ($bentrokKelas) {
                    $kelasNama   = optional($bentrokKelas->kelas)->nama_kelas ?? "(Kelas tidak ditemukan)";
                    $jamKe       = optional($bentrokKelas->jamPelajaran)->jam_ke ?? "(Jam tidak ditemukan)";
                    $jurusanNama = optional($bentrokKelas->jurusan)->nama_jurusan ?? "(Jurusan tidak ditemukan)";
                    $lembagaNama = optional($bentrokKelas->lembaga)->nama_lembaga ?? "(Lembaga tidak ditemukan)";

                    throw new \Exception("Gagal menambahkan '{$mataPelajaran->nama_mapel}': kelas $kelasNama ($jurusanNama - $lembagaNama) sudah memiliki jadwal pada hari $hari, jam ke-$jamKe.");
                }

                // Validasi bentrok pengajar
                $bentrokPengajar = JadwalPelajaran::with(['kelas', 'jurusan', 'lembaga', 'jamPelajaran'])
                    ->where('hari', $hari)
                    ->where('jam_pelajaran_id', $jamPelajaranId)
                    ->where('semester_id', $semesterId)
                    ->whereHas('mataPelajaran', function ($query) use ($pengajarId) {
                        $query->where('pengajar_id', $pengajarId);
                    })
                    ->first();

                if ($bentrokPengajar) {
                    $kelasNama   = optional($bentrokPengajar->kelas)->nama_kelas ?? "(Kelas tidak ditemukan)";
                    $jamKe       = optional($bentrokPengajar->jamPelajaran)->jam_ke ?? "(Jam tidak ditemukan)";
                    $jurusanNama = optional($bentrokPengajar->jurusan)->nama_jurusan ?? "(Jurusan tidak ditemukan)";
                    $lembagaNama = optional($bentrokPengajar->lembaga)->nama_lembaga ?? "(Lembaga tidak ditemukan)";

                    throw new \Exception("Gagal menambahkan '{$mataPelajaran->nama_mapel}': pengajar sudah mengajar pada hari $hari, jam ke-$jamKe di kelas $kelasNama ($jurusanNama - $lembagaNama).");
                }

                // Simpan jadwal
                $data = JadwalPelajaran::create([
                    'hari'               => $hari,
                    'semester_id'        => $semesterId,
                    'lembaga_id'         => $lembagaId,
                    'jurusan_id'         => $jurusanId,
                    'kelas_id'           => $kelasId,
                    'rombel_id'          => $rombelId,
                    'mata_pelajaran_id'  => $mataPelajaranId,
                    'jam_pelajaran_id'   => $jamPelajaranId,
                    'created_by'         => Auth::id(),
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);

                $jadwalTersimpan[] = $data;
            }

            DB::commit();

            return [
                'status' => true,
                'message' => 'Jadwal berhasil ditambahkan.',
                'data' => $jadwalTersimpan,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menambahkan jadwal pelajaran: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    public function getById(int $id): array
    {
        $jadwal = JadwalPelajaran::select([
                'id',
                'hari',
                'mata_pelajaran_id',
                'jam_pelajaran_id'
            ])
            ->find($id);

        if (! $jadwal) {
            return [
                'status' => false,
                'message' => 'Jadwal pelajaran tidak ditemukan.'
            ];
        }

        return [
            'status' => true,
            'message' => 'Data jadwal berhasil ditemukan.',
            'data' => $jadwal,
        ];
    }
    public function updateJadwal(int $id, array $data): array
    {
        DB::beginTransaction();

        try {
            // Ambil data jadwal berdasarkan ID
            $jadwal = JadwalPelajaran::find($id);
            if (! $jadwal) {
                return [
                    'status' => false,
                    'message' => 'Jadwal pelajaran tidak ditemukan.'
                ];
            }

            $hari   = $data['hari'];
            $jamId  = $data['jam_pelajaran_id'];
            $mataPelajaranId = $data['mata_pelajaran_id'];
            $kelasId = $jadwal->kelas_id;
            $lembagaId = $jadwal->lembaga_id; // Lembaga pada jadwal

            // Ambil data mata pelajaran beserta relasi lembaga
            $mataPelajaran = MataPelajaran::with('lembaga')->findOrFail($mataPelajaranId);
            $pengajarId = $mataPelajaran->pengajar_id;

            // --- Validasi lembaga_id ---
            $lembagaInput = Lembaga::find($lembagaId);
            $lembagaInputNama = $lembagaInput ? $lembagaInput->nama_lembaga : "(Lembaga tidak ditemukan)";
            $lembagaMapelNama = $mataPelajaran->lembaga ? $mataPelajaran->lembaga->nama_lembaga : "(Lembaga tidak ditemukan)";

            if ($mataPelajaran->lembaga_id != $lembagaId) {
                throw new \Exception(
                    "Gagal mengubah jadwal untuk '{$mataPelajaran->nama_mapel}': lembaga pada mata pelajaran ($lembagaMapelNama) tidak cocok dengan lembaga jadwal ($lembagaInputNama)."
                );
            }
            // --------------------------

            // Validasi bentrok kelas (kecuali data ini sendiri)
            $bentrokKelas = JadwalPelajaran::where('id', '!=', $id)
                ->where('hari', $hari)
                ->where('kelas_id', $kelasId)
                ->where('jam_pelajaran_id', $jamId)
                ->first();

            if ($bentrokKelas) {
                $kelasNama   = optional($bentrokKelas->kelas)->nama_kelas ?? "(Kelas tidak ditemukan)";
                $jamKe       = optional($bentrokKelas->jamPelajaran)->jam_ke ?? "(Jam tidak ditemukan)";
                $jurusanNama = optional($bentrokKelas->jurusan)->nama_jurusan ?? "(Jurusan tidak ditemukan)";
                $lembagaNama = optional($bentrokKelas->lembaga)->nama_lembaga ?? "(Lembaga tidak ditemukan)";

                throw new \Exception("Gagal mengubah '{$mataPelajaran->nama_mapel}': kelas $kelasNama ($jurusanNama - $lembagaNama) sudah memiliki jadwal pada hari $hari, jam ke-$jamKe.");
            }

            // Validasi bentrok pengajar
            $bentrokPengajar = JadwalPelajaran::where('id', '!=', $id)
                ->where('hari', $hari)
                ->where('jam_pelajaran_id', $jamId)
                ->whereHas('mataPelajaran', function ($q) use ($pengajarId) {
                    $q->where('pengajar_id', $pengajarId);
                })
                ->first();

            if ($bentrokPengajar) {
                $kelasNama   = optional($bentrokPengajar->kelas)->nama_kelas ?? "(Kelas tidak ditemukan)";
                $jamKe       = optional($bentrokPengajar->jamPelajaran)->jam_ke ?? "(Jam tidak ditemukan)";
                $jurusanNama = optional($bentrokPengajar->jurusan)->nama_jurusan ?? "(Jurusan tidak ditemukan)";
                $lembagaNama = optional($bentrokPengajar->lembaga)->nama_lembaga ?? "(Lembaga tidak ditemukan)";

                throw new \Exception("Gagal mengubah '{$mataPelajaran->nama_mapel}': pengajar sudah mengajar pada hari $hari, jam ke-$jamKe di kelas $kelasNama ($jurusanNama - $lembagaNama).");
            }

            // Simpan perubahan
            $jadwal->update([
                'hari'               => $hari,
                'mata_pelajaran_id'  => $mataPelajaranId,
                'jam_pelajaran_id'   => $jamId,
                'updated_at'         => now(),
            ]);

            DB::commit();

            return [
                'status' => true,
                'message' => 'Jadwal pelajaran berhasil diperbarui.',
                'data' => $jadwal->fresh([
                    'mataPelajaran:id,nama_mapel',
                    'kelas:id,nama_kelas',
                    'jurusan:id,nama_jurusan',
                    'lembaga:id,nama_lembaga',
                    'rombel:id,nama_rombel',
                    'jamPelajaran:id,jam_ke,jam_mulai,jam_selesai',
                    'semester:id,semester'
                ]),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal mengupdate jadwal pelajaran: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    public function deleteById(int $id): void
    {
        $jadwal = JadwalPelajaran::findOrFail($id);
        $jadwal->delete();
    }
}