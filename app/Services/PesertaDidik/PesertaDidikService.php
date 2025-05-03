<?php

namespace App\Services\PesertaDidik;

use App\Models\Santri;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\PesertaDidik\Filters\FilterPesertaDidikService;

class PesertaDidikService
{
    public function getAllPesertaDidik(Request $request)
    {
        // 1) Ambil ID untuk jenis berkas "Pas foto"
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        // 2) Subquery: foto terakhir per biodata
        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        // 3) Subquery: warga_pesantren terakhir per biodata (status = true)
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        return DB::table('santri AS s')
            ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
            ->leftjoin('riwayat_pendidikan AS rp', fn($j) => $j->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
            ->leftjoin('riwayat_domisili AS rd', fn($join) => $join->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
            ->leftJoin('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->where(fn($q) => $q->where('s.status', 'aktif')
                ->orWhere('rp.status', '=', 'aktif'))
            ->where(fn($q) => $q->whereNull('b.deleted_at')
                ->whereNull('s.deleted_at'))
            ->select([
                's.id',
                DB::raw("COALESCE(b.nik, b.no_passport) AS identitas"),
                'b.nama',
                'wp.niup',
                'l.nama_lembaga',
                'w.nama_wilayah',
                'kb.nama_kabupaten AS kota_asal',
                's.created_at',
                // ambil updated_at terbaru antar s, rp, rd
                DB::raw("
             GREATEST(
                 s.updated_at,
                 COALESCE(rp.updated_at, s.updated_at),
                 COALESCE(rd.updated_at, s.updated_at)
             ) AS updated_at
         "),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ])
            ->orderBy('s.id');
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'id'               => $item->id,
            'nik_or_passport'  => $item->identitas,
            'nama'             => $item->nama,
            'niup'             => $item->niup ?? '-',
            'lembaga'          => $item->nama_lembaga ?? '-',
            'wilayah'          => $item->nama_wilayah ?? '-',
            'kota_asal'        => $item->kota_asal,
            'tgl_update'       => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            'tgl_input'        => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            'foto_profil'      => url($item->foto_profil),
        ]);
    }

    public function store(array $data)
    {
        $pesertaDidik = DB::transaction(function () use ($data) {
            // Generate unique NIS (sebagai string)
            do {
                $nis = (string) now()->format('YmdHis') . Str::random(2);
            } while (DB::table('santri')->where('nis', $nis)->exists());

            // Generate unique smartcard
            do {
                $smartcard = 'SC-' . strtoupper(Str::random(10));
            } while (DB::table('biodata')->where('smartcard', $smartcard)->exists());

            $biodataId = DB::table('biodata')->insertGetId([
                'negara_id' => $data['negara_id'],
                'provinsi_id' => $data['provinsi_id'] ?? null,
                'kabupaten_id' => $data['kabupaten_id'] ?? null,
                'kecamatan_id' => $data['kecamatan_id'] ?? null,
                'jalan' => $data['jalan'] ?? null,
                'kode_pos' => $data['kode_pos'] ?? null,
                'nama' => $data['nama'],
                'no_passport' => $data['no_passport'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'tempat_lahir' => $data['tempat_lahir'],
                'nik' => $data['nik'] ?? null,
                'no_telepon' => $data['no_telepon'],
                'no_telepon_2' => $data['no_telepon_2'] ?? null,
                'email' => $data['email'],
                'jenjang_pendidikan_terakhir' => $data['jenjang_pendidikan_terakhir'] ?? null,
                'nama_pendidikan_terakhir' => $data['nama_pendidikan_terakhir'] ?? null,
                'anak_keberapa' => $data['anak_keberapa'] ?? null,
                'dari_saudara' => $data['dari_saudara'] ?? null,
                'tinggal_bersama' => $data['tinggal_bersama'] ?? null,
                'smartcard' => $smartcard,
                'status' => true,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Generate UUID untuk santri_id
            do {
                $santriId = Str::uuid()->toString();
            } while (DB::table('santri')->where('id', $santriId)->exists()); // Pastikan UUID unik

            DB::table('santri')->insert([
                'id' => $santriId,
                'biodata_id' => $biodataId,
                'nis' => $nis,
                'tanggal_masuk' => now(),
                'status' => 'aktif',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Generate unique no_induk (string)
            do {
                $noInduk = now()->format('His') . Str::random(3);
            } while (DB::table('riwayat_pendidikan')->where('no_induk', $noInduk)->exists());

            DB::table('riwayat_pendidikan')->insert([
                'santri_id' => $santriId,
                'no_induk' => $noInduk,
                'lembaga_id' => $data['lembaga_id'],
                'jurusan_id' => $data['jurusan_id'] ?? null,
                'kelas_id' => $data['kelas_id'] ?? null,
                'rombel_id' => $data['rombel_id'] ?? null,
                'tanggal_masuk' => now()->toDateString(),
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert ke tabel riwayat_domisili
            DB::table('riwayat_domisili')->insert([
                'santri_id' => $santriId,
                'wilayah_id' => $data['wilayah_id'] ?? null,
                'blok_id' => $data['blok_id'] ?? null,
                'kamar_id' => $data['kamar_id'] ?? null,
                'tanggal_masuk' => now(),
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'santri_id' => $santriId,
                'nama' => $data['nama'],
                'email' => $data['email'],
                'tanggal_masuk' => now()->toDateString(),
            ];
        });
        return $pesertaDidik;
    }

    public function update(array $data, string $santriId)
    {
        return DB::transaction(function () use ($data, $santriId) {
            $userId = Auth::id();

            // 1) ambil biodata_id dari tabel santri
            $biodataId = DB::table('santri')
                ->where('id', $santriId)
                ->value('biodata_id');

            if (! $biodataId) {
                throw new \Exception("Santri #{$santriId} tidak memiliki biodata_id.");
            }

            // 2) update biodata by id = $biodataId
            DB::table('biodata')
                ->where('id', $biodataId)
                ->update([
                    'negara_id'                   => $data['negara_id'],
                    'provinsi_id'                 => $data['provinsi_id'] ?? null,
                    'kabupaten_id'                => $data['kabupaten_id'] ?? null,
                    'kecamatan_id'                => $data['kecamatan_id'] ?? null,
                    'jalan'                       => $data['jalan'] ?? null,
                    'kode_pos'                    => $data['kode_pos'] ?? null,
                    'nama'                        => $data['nama'],
                    'no_passport'                 => $data['no_passport'] ?? null,
                    'jenis_kelamin'               => $data['jenis_kelamin'],
                    'tanggal_lahir'               => $data['tanggal_lahir'],
                    'tempat_lahir'                => $data['tempat_lahir'],
                    'nik'                         => $data['nik'] ?? null,
                    'no_telepon'                  => $data['no_telepon'],
                    'no_telepon_2'                => $data['no_telepon_2'] ?? null,
                    'email'                       => $data['email'],
                    'jenjang_pendidikan_terakhir' => $data['jenjang_pendidikan_terakhir'] ?? null,
                    'nama_pendidikan_terakhir'    => $data['nama_pendidikan_terakhir'] ?? null,
                    'anak_keberapa'               => $data['anak_keberapa'] ?? null,
                    'dari_saudara'                => $data['dari_saudara'] ?? null,
                    'tinggal_bersama'             => $data['tinggal_bersama'] ?? null,
                    'status'                      => $data['status_biodata'] ?? true,
                    'updated_by'                  => $userId,
                    'updated_at'                  => now(),
                ]);

            // 3) update status & updated_by di santri
            DB::table('santri')
                ->where('id', $santriId)
                ->update([
                    'status'     => $data['status_santri'] ?? 'aktif',
                    'updated_by' => $userId,
                    'updated_at' => now(),
                ]);

            // 1) tutup record domisili lama (jika ada yang masih "berjalan")
            DB::table('riwayat_domisili')
                ->where('santri_id', $santriId)
                ->whereNull('tanggal_keluar')
                ->update([
                    'status' => $data['status_riwayat_domisili'] ?? 'aktif',
                    'tanggal_keluar' => now(),
                    'updated_by'      => $userId,
                    'updated_at'      => now(),
                ]);

            // 2) insert record domisili baru
            DB::table('riwayat_domisili')->insert([
                'santri_id'       => $santriId,
                'wilayah_id'      => $data['wilayah_id'] ?? null,
                'blok_id'         => $data['blok_id'] ?? null,
                'kamar_id'        => $data['kamar_id'] ?? null,
                'tanggal_masuk'   => now(),
                'tanggal_keluar' => null,
                'created_by'      => $userId,
                'created_at'      => now(),
            ]);

            // === RIWAYAT PENDIDIKAN ===
            // 1) tutup record pendidikan lama
            DB::table('riwayat_pendidikan')
                ->where('santri_id', $santriId)
                ->whereNull('tanggal_keluar')
                ->update([
                    'status'          => $data['status_riwayat_pendidikan'] ?? 'aktif',
                    'tanggal_keluar' => now()->toDateString(),
                    'updated_by'      => $userId,
                    'updated_at'      => now(),
                ]);

            // 2) insert record pendidikan baru
            DB::table('riwayat_pendidikan')->insert([
                'santri_id'       => $santriId,
                'lembaga_id'      => $data['lembaga_id'],
                'jurusan_id'      => $data['jurusan_id'] ?? null,
                'kelas_id'        => $data['kelas_id'] ?? null,
                'rombel_id'       => $data['rombel_id'] ?? null,
                'tanggal_masuk'   => now()->toDateString(),
                'tanggal_keluar' => null,
                'created_by'      => $userId,
                'created_at'      => now(),
            ]);
        });
    }

    public function destroy(string $santriId)
    {
        return DB::transaction(function () use ($santriId) {
            $userId = Auth::id();

            $santri  = Santri::with('biodata')->findOrFail($santriId);
            $biodata = $santri->biodata;

            if ($biodata) {
                $biodata->deleted_by = $userId;
                $biodata->save();
                $biodata->delete();
            }

            $santri->deleted_by = $userId;
            $santri->save();
            $santri->delete();

            return $santri;
        });
    }
}
