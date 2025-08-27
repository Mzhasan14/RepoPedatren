<?php

namespace App\Services\Administrasi;

use App\Models\Catatan_kognitif;
use App\Models\Santri;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CatatanKognitifService
{
    public function baseCatatanKognitifQuery(Request $request)
    {
        $user = $request->user();

        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        // Ambil ID wali_asuh jika user role wali_asuh
        $waliAsuhId = null;
        if ($user->hasRole('wali_asuh')) {
            $waliAsuhId = DB::table('wali_asuh as wa')
                ->join('santri as s', 's.id', '=', 'wa.id_santri')
                ->where('s.biodata_id', $user->biodata_id)
                ->value('wa.id');
        }

        $query = DB::table('catatan_kognitif')
            ->join('santri as cs', 'cs.id', '=', 'catatan_kognitif.id_santri')
            ->join('biodata as bs', 'bs.id', '=', 'cs.biodata_id')
            ->leftJoin('domisili_santri', 'domisili_santri.santri_id', '=', 'cs.id')
            ->leftJoin('wilayah', 'wilayah.id', '=', 'domisili_santri.wilayah_id')
            ->leftJoin('blok', 'blok.id', '=', 'domisili_santri.blok_id')
            ->leftJoin('kamar', 'kamar.id', '=', 'domisili_santri.kamar_id')
            ->leftJoin('pendidikan', 'pendidikan.biodata_id', '=', 'bs.id')
            ->leftJoin('lembaga', 'lembaga.id', '=', 'pendidikan.lembaga_id')
            ->leftJoin('jurusan', 'jurusan.id', '=', 'pendidikan.jurusan_id')
            ->leftJoin('kelas', 'kelas.id', '=', 'pendidikan.kelas_id')
            ->leftJoin('rombel', 'rombel.id', '=', 'pendidikan.rombel_id')

            // Relasi wali_asuh → pencatat
            ->leftJoin('wali_asuh', 'wali_asuh.id', '=', 'catatan_kognitif.id_wali_asuh')
            ->leftJoin('santri as ps', 'ps.id', '=', 'wali_asuh.id_santri')
            ->leftJoin('biodata as bp', 'bp.id', '=', 'ps.biodata_id')

            // Relasi created_by → user → role superadmin
            ->leftJoin('users as cu', 'cu.id', '=', 'catatan_kognitif.created_by')
            ->leftJoin('biodata as bsc', 'bsc.id', '=', 'cu.biodata_id') // langsung cu.biodata_id

            // Spatie roles
            ->leftJoin('model_has_roles as mhr', function ($join) {
                $join->on('cu.id', '=', 'mhr.model_id')
                    ->where('mhr.model_type', '=', DB::raw("'App\\\\Models\\\\User'"));
            })
            ->leftJoin('roles as r', 'r.id', '=', 'mhr.role_id')

            // Foto santri
            ->leftJoinSub($fotoLast, 'fotoLastCatatan', function ($join) {
                $join->on('bs.id', '=', 'fotoLastCatatan.biodata_id');
            })
            ->leftJoin('berkas as FotoCatatan', 'FotoCatatan.id', '=', 'fotoLastCatatan.last_id')

            // Foto pencatat (wali_asuh)
            ->leftJoinSub($fotoLast, 'fotoLastPencatat', function ($join) {
                $join->on('bp.id', '=', 'fotoLastPencatat.biodata_id');
            })
            ->leftJoin('berkas as FotoPencatat', 'FotoPencatat.id', '=', 'fotoLastPencatat.last_id')

            // Foto superadmin
            ->leftJoinSub($fotoLast, 'fotoLastSuperAdmin', function ($join) {
                $join->on('bsc.id', '=', 'fotoLastSuperAdmin.biodata_id');
            })
            ->leftJoin('berkas as FotoSuperAdmin', 'FotoSuperAdmin.id', '=', 'fotoLastSuperAdmin.last_id')

            ->orderBy('catatan_kognitif.tanggal_buat', 'desc');

        // Filter khusus wali_asuh
        if ($user->hasRole('wali_asuh') && $waliAsuhId) {
            $query->where('catatan_kognitif.id_wali_asuh', $waliAsuhId);
        } elseif ($user->hasRole('wali_asuh') && !$waliAsuhId) {
            $query->whereRaw('1=0');
        }

        return $query;
    }

    public function getAllCatatanKognitif(Request $request)
    {
        try {
            $query = $this->baseCatatanKognitifQuery($request);

            return $query->select(
                'catatan_kognitif.id as id_catatan',
                'bs.id as Biodata_uuid',

                // Pencatat UUID
                DB::raw("
                CASE
                    WHEN r.name = 'superadmin' THEN bsc.id
                    WHEN catatan_kognitif.id_wali_asuh IS NOT NULL THEN bp.id
                    ELSE NULL
                END as Pencatat_uuid
            "),

                'bs.nama',
                DB::raw("GROUP_CONCAT(DISTINCT blok.nama_blok SEPARATOR ', ') as blok"),
                DB::raw("GROUP_CONCAT(DISTINCT wilayah.nama_wilayah SEPARATOR ', ') as wilayah"),
                DB::raw("GROUP_CONCAT(DISTINCT jurusan.nama_jurusan SEPARATOR ', ') as jurusan"),
                DB::raw("GROUP_CONCAT(DISTINCT lembaga.nama_lembaga SEPARATOR ', ') as lembaga"),

                'catatan_kognitif.kebahasaan_nilai',
                'catatan_kognitif.kebahasaan_tindak_lanjut',
                'catatan_kognitif.baca_kitab_kuning_nilai',
                'catatan_kognitif.baca_kitab_kuning_tindak_lanjut',
                'catatan_kognitif.hafalan_tahfidz_nilai',
                'catatan_kognitif.hafalan_tahfidz_tindak_lanjut',
                'catatan_kognitif.furudul_ainiyah_nilai',
                'catatan_kognitif.furudul_ainiyah_tindak_lanjut',
                'catatan_kognitif.tulis_alquran_nilai',
                'catatan_kognitif.tulis_alquran_tindak_lanjut',
                'catatan_kognitif.baca_alquran_nilai',
                'catatan_kognitif.baca_alquran_tindak_lanjut',

                // Nama pencatat
                DB::raw("
                CASE
                    WHEN r.name = 'superadmin' THEN COALESCE(bsc.nama, cu.name, 'Super Admin')
                    WHEN catatan_kognitif.id_wali_asuh IS NOT NULL THEN bp.nama
                    ELSE 'Tidak diketahui'
                END as pencatat
            "),

                // Jabatan pencatat
                DB::raw("
                CASE
                    WHEN r.name = 'superadmin' THEN 'superadmin'
                    WHEN catatan_kognitif.id_wali_asuh IS NOT NULL THEN 'wali asuh'
                    ELSE NULL
                END as wali_asuh
            "),

                'catatan_kognitif.tanggal_buat',
                DB::raw("COALESCE(FotoCatatan.file_path, 'default.jpg') as foto_catatan"),

                // Foto pencatat
                DB::raw("
                CASE
                    WHEN r.name = 'superadmin' THEN COALESCE(FotoSuperAdmin.file_path, 'default.jpg')
                    WHEN catatan_kognitif.id_wali_asuh IS NOT NULL THEN COALESCE(FotoPencatat.file_path, 'default.jpg')
                    ELSE 'default.jpg'
                END as foto_pencatat
            ")
            )
                ->groupBy(
                    'catatan_kognitif.id',
                    'catatan_kognitif.id_wali_asuh',
                    'bs.id',
                    'bs.nama',
                    'bp.id',
                    'bp.nama',
                    'cu.id',
                    'cu.name',
                    'bsc.id',
                    'bsc.nama',
                    'wali_asuh.id',
                    'r.name',
                    'catatan_kognitif.kebahasaan_nilai',
                    'catatan_kognitif.kebahasaan_tindak_lanjut',
                    'catatan_kognitif.baca_kitab_kuning_nilai',
                    'catatan_kognitif.baca_kitab_kuning_tindak_lanjut',
                    'catatan_kognitif.hafalan_tahfidz_nilai',
                    'catatan_kognitif.hafalan_tahfidz_tindak_lanjut',
                    'catatan_kognitif.furudul_ainiyah_nilai',
                    'catatan_kognitif.furudul_ainiyah_tindak_lanjut',
                    'catatan_kognitif.tulis_alquran_nilai',
                    'catatan_kognitif.tulis_alquran_tindak_lanjut',
                    'catatan_kognitif.baca_alquran_nilai',
                    'catatan_kognitif.baca_alquran_tindak_lanjut',
                    'catatan_kognitif.tanggal_buat',
                    'FotoCatatan.file_path',
                    'FotoPencatat.file_path',
                    'FotoSuperAdmin.file_path'
                );
        } catch (\Exception $e) {
            Log::error('Error fetching data Catatan Kognitif: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data Catatan Kognitif',
                'code' => 500,
            ], 500);
        }
    }


    public function formatData($results, $kategoriFilter = null)
    {
        $kategoriMap = [
            'kebahasaan' => ['field' => 'kebahasaan_nilai', 'tindak' => 'kebahasaan_tindak_lanjut'],
            'baca kitab kuning' => ['field' => 'baca_kitab_kuning_nilai', 'tindak' => 'baca_kitab_kuning_tindak_lanjut'],
            'hafalan tahfidz' => ['field' => 'hafalan_tahfidz_nilai', 'tindak' => 'hafalan_tahfidz_tindak_lanjut'],
            'furudul ainiyah' => ['field' => 'furudul_ainiyah_nilai', 'tindak' => 'furudul_ainiyah_tindak_lanjut'],
            'tulis al-quran' => ['field' => 'tulis_alquran_nilai', 'tindak' => 'tulis_alquran_tindak_lanjut'],
            'baca al-quran' => ['field' => 'baca_alquran_nilai', 'tindak' => 'baca_alquran_tindak_lanjut'],
        ];

        return collect($results->items())->flatMap(function ($item) use ($kategoriMap, $kategoriFilter) {
            $entries = [];

            foreach ($kategoriMap as $kategori => $fields) {
                if ($kategoriFilter && strtolower($kategoriFilter) !== strtolower($kategori)) {
                    continue;
                }

                $entries[] = [
                    'Biodata_uuid' => $item->Biodata_uuid,
                    'Pencatat_uuid' => $item->Pencatat_uuid,
                    'id_catatan' => $item->id_catatan,
                    'nama_santri' => $item->nama,
                    'blok' => $item->blok,
                    'wilayah' => $item->wilayah,
                    'pendidikan' => $item->jurusan,
                    'lembaga' => $item->lembaga,
                    'kategori' => $kategori,
                    'nilai' => $item->{$fields['field']},
                    'tindak_lanjut' => $item->{$fields['tindak']},
                    'pencatat' => $item->pencatat,
                    'jabatanPencatat' => $item->wali_asuh,
                    'waktu_pencatatan' => Carbon::parse($item->tanggal_buat)->format('d M Y H:i:s'),
                    'foto_catatan' => url($item->foto_catatan),
                    'foto_pencatat' => url($item->foto_pencatat),
                ];
            }

            return $entries;
        });
    }

    public function storeCatatanKognitif(array $input, Request $request)
    {
        $user = $request->user();

        if (! $user->hasRole('waliasuh') && ! $user->hasRole('superadmin')) {
            return [
                'status' => false,
                'message' => 'Hanya wali asuh atau superadmin yang dapat membuat catatan kognitif.',
                'data' => null,
            ];
        }

        // Ambil data anak_asuh dari dropdown
        $anakAsuh = DB::table('anak_asuh')
            ->where('id', $input['id_anak_asuh'])
            ->first();

        if (! $anakAsuh) {
            return [
                'status' => false,
                'message' => 'Data anak asuh tidak ditemukan.',
                'data' => null,
            ];
        }

        $idSantri = $anakAsuh->id_santri;

        // Tentukan id_wali_asuh
        if ($user->hasRole('waliasuh')) {
            // Ambil ID wali_asuh dari user login
            $waliAsuhId = DB::table('wali_asuh as wa')
                ->join('santri as s', 's.id', '=', 'wa.id_santri')
                ->join('biodata as b', 'b.id', '=', 's.biodata_id')
                ->join('users as u', 'u.biodata_id', '=', 'b.id') // langsung ke users
                ->where('u.id', $user->id)
                ->value('wa.id');


            if (! $waliAsuhId) {
                return [
                    'status' => false,
                    'message' => 'Anda tidak memiliki anak asuh.',
                    'data' => null,
                ];
            }

            // Cek apakah anak_asuh milik wali_asuh ini
            $cek = DB::table('kewaliasuhan')
                ->where('id_wali_asuh', $waliAsuhId)
                ->where('id_anak_asuh', $anakAsuh->id)
                ->first();

            if (! $cek) {
                return [
                    'status' => false,
                    'message' => 'Santri bukan anak asuh Anda.',
                    'data' => null,
                ];
            }
        } else {
            // Superadmin → ambil wali_asuh sesuai anak_asuh
            $waliAsuhId = DB::table('kewaliasuhan')
                ->where('id_anak_asuh', $anakAsuh->id)
                ->value('id_wali_asuh');

            if (! $waliAsuhId) {
                return [
                    'status' => false,
                    'message' => 'Belum ada wali asuh untuk anak asuh ini.',
                    'data' => null,
                ];
            }
        }

        // Cek status santri
        $santriObj = Santri::find($idSantri);
        if (! $santriObj || $santriObj->status !== 'aktif') {
            return [
                'status' => false,
                'message' => 'Santri tidak aktif. Tidak bisa menambahkan catatan kognitif.',
                'data' => null,
            ];
        }

        // Simpan catatan baru
        $catatan = Catatan_kognitif::create([
            'id_santri' => $idSantri,
            'id_wali_asuh' => $waliAsuhId,
            'kebahasaan_nilai' => $input['kebahasaan_nilai'],
            'kebahasaan_tindak_lanjut' => $input['kebahasaan_tindak_lanjut'],
            'baca_kitab_kuning_nilai' => $input['baca_kitab_kuning_nilai'],
            'baca_kitab_kuning_tindak_lanjut' => $input['baca_kitab_kuning_tindak_lanjut'],
            'hafalan_tahfidz_nilai' => $input['hafalan_tahfidz_nilai'],
            'hafalan_tahfidz_tindak_lanjut' => $input['hafalan_tahfidz_tindak_lanjut'],
            'furudul_ainiyah_nilai' => $input['furudul_ainiyah_nilai'],
            'furudul_ainiyah_tindak_lanjut' => $input['furudul_ainiyah_tindak_lanjut'],
            'tulis_alquran_nilai' => $input['tulis_alquran_nilai'],
            'tulis_alquran_tindak_lanjut' => $input['tulis_alquran_tindak_lanjut'],
            'baca_alquran_nilai' => $input['baca_alquran_nilai'],
            'baca_alquran_tindak_lanjut' => $input['baca_alquran_tindak_lanjut'],
            'tanggal_buat' => $input['tanggal_buat'] ?? now(),
            'status' => true,
            'created_by' => $user->id,
            'created_at' => now(),
        ]);

        return [
            'status' => true,
            'message' => 'Catatan kognitif berhasil ditambahkan.',
            'data' => $catatan,
        ];
    }

    public function updateKategori($id, Request $request)
    {
        $kategori = $request->kategori;
        $nilai = $request->nilai;
        $tindakLanjut = $request->tindak_lanjut;

        $kolomNilai = "{$kategori}_nilai";
        $kolomTL = "{$kategori}_tindak_lanjut";

        $allowedColumns = [
            'kebahasaan_nilai',
            'kebahasaan_tindak_lanjut',
            'baca_kitab_kuning_nilai',
            'baca_kitab_kuning_tindak_lanjut',
            'hafalan_tahfidz_nilai',
            'hafalan_tahfidz_tindak_lanjut',
            'furudul_ainiyah_nilai',
            'furudul_ainiyah_tindak_lanjut',
            'tulis_alquran_nilai',
            'tulis_alquran_tindak_lanjut',
            'baca_alquran_nilai',
            'baca_alquran_tindak_lanjut'
        ];

        if (!in_array($kolomNilai, $allowedColumns) || !in_array($kolomTL, $allowedColumns)) {
            throw new \Exception("Kolom tidak valid.");
        }

        $catatan = Catatan_kognitif::findOrFail($id);

        // Cek kondisi sebelum update
        if (!is_null($catatan->tanggal_selesai)) {
            throw new \Exception("Data tidak bisa diubah karena sudah tidak aktif lagi.");
        }

        if ((int)$catatan->status !== 1) {
            throw new \Exception("Data tidak bisa diubah karena status tidak aktif.");
        }

        // Update jika lolos pengecekan
        $catatan->$kolomNilai = $nilai;
        $catatan->$kolomTL = $tindakLanjut;
        $catatan->updated_by = Auth::id();
        $catatan->save();

        return $catatan->fresh(); // Mengembalikan data terkini setelah update
    }
}
