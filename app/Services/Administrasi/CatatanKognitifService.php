<?php

namespace App\Services\Administrasi;

use App\Models\Catatan_kognitif;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CatatanKognitifService
{
    public function getAllCatatanKognitif(Request $request)
    {
        try {
            // Ambil ID untuk jenis berkas "Pas foto"
            $pasFotoId = DB::table('jenis_berkas')
                ->where('nama_jenis_berkas', 'Pas foto')
                ->value('id');

            if (!$pasFotoId) {
                throw new \Exception('Jenis berkas "Pas foto" tidak ditemukan');
            }

            // Subquery: foto terakhir per biodata
            $fotoLast = DB::table('berkas')
                ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
                ->where('jenis_berkas_id', $pasFotoId)
                ->groupBy('biodata_id');

            return Catatan_kognitif::Active()
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
                ->leftJoin('wali_asuh', 'wali_asuh.id', '=', 'catatan_kognitif.id_wali_asuh')
                ->leftJoin('santri as ps', 'ps.id', '=', 'wali_asuh.id_santri')
                ->leftJoin('biodata as bp', 'bp.id', '=', 'ps.biodata_id')

                // join foto santri
                ->leftJoinSub($fotoLast, 'fotoLastCatatan', function ($join) {
                    $join->on('bs.id', '=', 'fotoLastCatatan.biodata_id');
                })
                ->leftJoin('berkas as FotoCatatan', 'FotoCatatan.id', '=', 'fotoLastCatatan.last_id')

                // join foto pencatat
                ->leftJoinSub($fotoLast, 'fotoLastPencatat', function ($join) {
                    $join->on('bp.id', '=', 'fotoLastPencatat.biodata_id');
                })
                ->leftJoin('berkas as FotoPencatat', 'FotoPencatat.id', '=', 'fotoLastPencatat.last_id')

                ->whereNull('catatan_kognitif.deleted_at')

                ->select(
                    'catatan_kognitif.id',
                    'bs.id as Biodata_uuid',
                    'bp.id as Pencatat_uuid',
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
                    'bp.nama as pencatat',
                    DB::raw("CASE WHEN wali_asuh.id IS NOT NULL THEN 'wali asuh' ELSE NULL END as wali_asuh"),
                    'catatan_kognitif.created_at',
                    DB::raw("COALESCE(FotoCatatan.file_path, 'default.jpg') as foto_catatan"),
                    DB::raw("COALESCE(FotoPencatat.file_path, 'default.jpg') as foto_pencatat")
                )
                ->groupBy(
                    'catatan_kognitif.id',
                    'bs.id',
                    'bp.id',
                    'bs.nama',
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
                    'bp.nama',
                    'wali_asuh.id',
                    'catatan_kognitif.created_at',
                    'FotoCatatan.file_path',
                    'FotoPencatat.file_path'
                );
        } catch (\Exception $e) {
            Log::error('Error fetching data Catatan Kognitif: '.$e->getMessage());

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
                    'id_santri' => $item->id,
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
                    'waktu_pencatatan' => $item->created_at->format('d M Y H:i:s'),
                    'foto_catatan' => url($item->foto_catatan),
                    'foto_pencatat' => url($item->foto_pencatat),
                ];
            }

            return $entries;
        });
    }

    public function storeCatatanKognitif(array $input)
    {
        $santri = Santri::find($input['id_santri']);

        // Cek apakah santri ada dan status aktif = 'aktif'
        if (! $santri || $santri->status !== 'aktif') {
            return [
                'status' => false,
                'message' => 'Santri tidak aktif. Tidak bisa menambahkan catatan kognitif.',
                'data' => null,
            ];
        }

        // Cek jika masih ada catatan kognitif aktif yang belum selesai
        $adaCatatanAktif = Catatan_kognitif::where('id_santri', $input['id_santri'])
            ->where('status', 1)
            ->whereNull('tanggal_selesai')
            ->exists();

        if ($adaCatatanAktif) {
            return [
                'status' => false,
                'message' => 'Masih ada catatan kognitif aktif yang belum diselesaikan.',
                'data' => null,
            ];
        }

        // Simpan catatan baru
        $catatan = Catatan_kognitif::create([
            'id_santri' => $input['id_santri'],
            'id_wali_asuh' => $input['id_wali_asuh'],
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
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return [
            'status' => true,
            'message' => 'Catatan kognitif berhasil ditambahkan.',
            'data' => $catatan,
        ];
    }
}
