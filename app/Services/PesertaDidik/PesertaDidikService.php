<?php

namespace App\Services\PesertaDidik;

use App\Models\Santri;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Services\PesertaDidik\Formulir\BerkasService;
use App\Services\PesertaDidik\Formulir\BiodataService;
use App\Services\PesertaDidik\Formulir\DomisiliService;
use App\Services\PesertaDidik\Formulir\PendidikanService;

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
                'b.id as biodata_id',
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
            'biodata_id'       => $item->biodata_id,
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
        DB::beginTransaction();
        try {
            do {
                $smartcard = 'SC-' . strtoupper(Str::random(10));
            } while (DB::table('biodata')->where('smartcard', $smartcard)->exists());

            $biodataId = DB::table('biodata')->insertGetId([
                'nama' => $data['nama'],
                'negara_id' => $data['negara_id'],
                'provinsi_id' => $data['provinsi_id'] ?? null,
                'kabupaten_id' => $data['kabupaten_id'] ?? null,
                'kecamatan_id' => $data['kecamatan_id'] ?? null,
                'jalan' => $data['jalan'] ?? null,
                'kode_pos' => $data['kode_pos'] ?? null,
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

            // Ambil semua id_biodata dari keluarga yang memiliki nomor KK yang sama dengan input
            $existingParents = DB::table('keluarga')
                ->where('no_kk', $data['no_kk'])
                ->pluck('id_biodata');

            // Jika ada data keluarga dengan nomor KK tersebut
            if ($existingParents->isNotEmpty()) {
                // Ambil semua NIK dari biodata yang sesuai dengan id_biodata yang ditemukan
                $existingNIKs = DB::table('biodata')
                    ->whereIn('id', $existingParents)
                    ->pluck('nik');

                // Cek apakah NIK ayah, ibu, atau wali dari input ada di daftar NIK yang sudah terdaftar
                foreach (['nik_ayah', 'nik_ibu', 'nik_wali'] as $nikKey) {
                    // Jika NIK diinputkan tidak kosong dan belum ada di daftar NIK yang terdaftar
                    if (!empty($data[$nikKey]) && !$existingNIKs->contains($data[$nikKey])) {
                        throw ValidationException::withMessages([
                            'no_kk' => ['No KK ini sudah digunakan oleh kombinasi orang tua yang berbeda.'],
                        ]);
                    }
                }
            }

            // Simpan no_kk untuk Peserta Didik
            DB::table('keluarga')->insert([
                'id_biodata' => $biodataId,
                'no_kk' => $data['no_kk'],
                'status' => true,
                'created_by' => Auth::id(),
            ]);

            do {
                $nis = (string) now()->format('YmdHis') . Str::random(2);
            } while (DB::table('santri')->where('nis', $nis)->exists());

            do {
                $santriId = Str::uuid()->toString();
            } while (DB::table('santri')->where('id', $santriId)->exists());

            DB::table('santri')->insertGetId([
                'id' => $santriId,
                'biodata_id' => $biodataId,
                'nis' => $nis,
                'tanggal_masuk' => now(),
                'created_by' => Auth::id(),
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Ambil id_hubungan_keluarga
            $hubungan = DB::table('hubungan_keluarga')
                ->whereIn('nama_status', ['ayah', 'ibu', 'wali'])
                ->pluck('id', 'nama_status');

            // 3. Cek dan Simpan Ayah
            if (!empty($data['nama_ayah'])) {
                // Cek apakah ayah sudah ada di biodata berdasarkan nik
                $ayahId = DB::table('biodata')->where('nik', $data['nik_ayah'])->value('id');

                if (!$ayahId) {
                    // Insert ayah baru jika nik tidak ditemukan
                    $ayahId = DB::table('biodata')->insertGetId([
                        'nama' => $data['nama_ayah'],
                        'nik' => $data['nik_ayah'],
                        'tempat_lahir' => $data['tempat_lahir_ayah'] ?? null,
                        'tanggal_lahir' => $data['tanggal_lahir_ayah'] ?? null,
                        'no_telepon' => $data['no_telepon_ayah'] ?? null,
                        'jenjang_pendidikan_terakhir' => $data['pendidikan_terakhir_ayah'] ?? null,
                        'status' => true,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('orang_tua_wali')->insert([
                        'id_biodata' => $ayahId,
                        'pekerjaan' => $data['pekerjaan_ayah'] ?? null,
                        'penghasilan' => $data['penghasilan_ayah'] ?? null,
                        'id_hubungan_keluarga' => $hubungan['ayah'] ?? null,
                        'status' => true,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Periksa apakah kombinasi no_kk dan id_biodata ayah sudah ada di keluarga
                $existingKeluarga = DB::table('keluarga')
                    ->where('no_kk', $data['no_kk'])
                    ->where('id_biodata', $ayahId)
                    ->exists();

                if (!$existingKeluarga) {
                    DB::table('keluarga')->insert([
                        'id_biodata' => $ayahId,
                        'no_kk' => $data['no_kk'],
                        'status' => true,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            // 4. Cek dan Simpan Ibu
            if (!empty($data['nama_ibu'])) {
                // Cek apakah ibu sudah ada di biodata berdasarkan nik
                $ibuId = DB::table('biodata')->where('nik', $data['nik_ibu'])->value('id');

                if (!$ibuId) {
                    // Insert ibu baru jika nik tidak ditemukan
                    $ibuId = DB::table('biodata')->insertGetId([
                        'nama' => $data['nama_ibu'],
                        'nik' => $data['nik_ibu'],
                        'tempat_lahir' => $data['tempat_lahir_ibu'] ?? null,
                        'tanggal_lahir' => $data['tanggal_lahir_ibu'] ?? null,
                        'no_telepon' => $data['no_telepon_ibu'] ?? null,
                        'jenjang_pendidikan_terakhir' => $data['pendidikan_terakhir_ibu'] ?? null,
                        'status' => true,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('orang_tua_wali')->insert([
                        'id_biodata' => $ibuId,
                        'pekerjaan' => $data['pekerjaan_ibu'] ?? null,
                        'penghasilan' => $data['penghasilan_ibu'] ?? null,
                        'id_hubungan_keluarga' => $hubungan['ibu'] ?? null,
                        'status' => true,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Periksa apakah kombinasi no_kk dan id_biodata ibu sudah ada di keluarga
                $existingKeluarga = DB::table('keluarga')
                    ->where('no_kk', $data['no_kk'])
                    ->where('id_biodata', $ibuId)
                    ->exists();

                if (!$existingKeluarga) {
                    DB::table('keluarga')->insert([
                        'id_biodata' => $ibuId,
                        'no_kk' => $data['no_kk'],
                        'status' => true,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            // 5. Cek dan Simpan Wali jika ada
            if (!empty($data['nama_wali'])) {
                $waliNik = $data['nik_wali'] ?? null;
                $waliId = DB::table('biodata')->where('nik', $waliNik)->value('id');

                if (!$waliId) {
                    // Insert wali baru jika nik tidak ditemukan
                    $waliId = DB::table('biodata')->insertGetId([
                        'nama' => $data['nama_wali'],
                        'nik' => $waliNik,
                        'tempat_lahir' => $data['tempat_lahir_wali'] ?? null,
                        'tanggal_lahir' => $data['tanggal_lahir_wali'] ?? null,
                        'no_telepon' => $data['no_telepon_wali'] ?? null,
                        'jenjang_pendidikan_terakhir' => $data['pendidikan_terakhir_wali'] ?? null,
                        'status' => true,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('orang_tua_wali')->insert([
                        'id_biodata' => $waliId,
                        'pekerjaan' => $data['pekerjaan_wali'] ?? null,
                        'penghasilan' => $data['penghasilan_wali'] ?? null,
                        'id_hubungan_keluarga' => $hubungan['wali'] ?? null,
                        'wali' => true,
                        'status' => true,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // Jika nik wali sudah ada, update status 'wali' menjadi true di tabel orang_tua_wali
                    DB::table('orang_tua_wali')
                        ->where('id_biodata', $waliId)
                        ->update(['wali' => true]);
                }

                // Periksa apakah kombinasi no_kk dan id_biodata wali sudah ada di keluarga
                $existingKeluarga = DB::table('keluarga')
                    ->where('no_kk', $data['no_kk'])
                    ->where('id_biodata', $waliId)
                    ->exists();

                if (!$existingKeluarga) {
                    // Insert ke tabel keluarga untuk wali
                    DB::table('keluarga')->insert([
                        'id_biodata' => $waliId,
                        'no_kk' => $data['no_kk'],
                        'status' => true,
                        'created_by' => Auth::id(),
                    ]);
                }

                // 6. Simpan data berkas jika ada
                if (!empty($data['berkas']) && is_array($data['berkas'])) {
                    foreach ($data['berkas'] as $item) {

                        $path = $item['file_path']->store('PesertaDidik', 'public');

                        $jenisBerkasId = (int) $item['jenis_berkas_id'];

                        $filePath = Storage::url($path);

                        DB::table('berkas')->insert([
                            'biodata_id' => $biodataId,
                            'jenis_berkas_id' => $jenisBerkasId,
                            'file_path' => $filePath,
                            'status' => true,
                            'created_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return [
                'santri_id' => $santriId,
                'biodata_diri' => $biodataId
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
