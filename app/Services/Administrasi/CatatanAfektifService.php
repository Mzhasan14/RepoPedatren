<?php

namespace App\Services\Administrasi;

use App\Models\Catatan_afektif;
use App\Models\Santri;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CatatanAfektifService
{
    public function baseCatatanAfektifQuery(Request $request)
    {
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        $query = DB::table('catatan_afektif')
            ->join('santri as cs', 'cs.id', '=', 'catatan_afektif.id_santri')
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
            ->leftJoin('wali_asuh', 'wali_asuh.id', '=', 'catatan_afektif.id_wali_asuh')
            ->leftJoin('santri as ps', 'ps.id', '=', 'wali_asuh.id_santri')
            ->leftJoin('biodata as bp', 'bp.id', '=', 'ps.biodata_id')
            
            // Foto santri (catatan)
            ->leftJoinSub($fotoLast, 'fotoLastCatatan', function ($join) {
                $join->on('bs.id', '=', 'fotoLastCatatan.biodata_id');
            })
            ->leftJoin('berkas as FotoCatatan', 'FotoCatatan.id', '=', 'fotoLastCatatan.last_id')

            // Foto pencatat
            ->leftJoinSub($fotoLast, 'fotoLastPencatat', function ($join) {
                $join->on('bp.id', '=', 'fotoLastPencatat.biodata_id');
            })
            ->leftJoin('berkas as FotoPencatat', 'FotoPencatat.id', '=', 'fotoLastPencatat.last_id')

            ->where('catatan_afektif.status', true)
            ->whereNull('catatan_afektif.tanggal_selesai');

        return $query;
    }
    public function getAllCatatanAfektif(Request $request)
    {
        try {
            $query = $this->baseCatatanAfektifQuery($request);

            return $query->select(
                    'catatan_afektif.id as id_catatan',
                    'bs.id as Biodata_uuid',
                    'bp.id as Pencatat_uuid',
                    'bs.nama',
                    DB::raw("GROUP_CONCAT(DISTINCT blok.nama_blok SEPARATOR ', ') as blok"),
                    DB::raw("GROUP_CONCAT(DISTINCT wilayah.nama_wilayah SEPARATOR ', ') as wilayah"),
                    DB::raw("GROUP_CONCAT(DISTINCT jurusan.nama_jurusan SEPARATOR ', ') as jurusan"),
                    DB::raw("GROUP_CONCAT(DISTINCT lembaga.nama_lembaga SEPARATOR ', ') as lembaga"),
                    'catatan_afektif.kepedulian_nilai',
                    'catatan_afektif.kepedulian_tindak_lanjut',
                    'catatan_afektif.kebersihan_nilai',
                    'catatan_afektif.kebersihan_tindak_lanjut',
                    'catatan_afektif.akhlak_nilai',
                    'catatan_afektif.akhlak_tindak_lanjut',
                    'bp.nama as pencatat',
                    DB::raw("CASE WHEN wali_asuh.id IS NOT NULL THEN 'wali asuh' ELSE NULL END as wali_asuh"),
                    'catatan_afektif.created_at',
                    DB::raw("COALESCE(FotoCatatan.file_path, 'default.jpg') as foto_catatan"),
                    DB::raw("COALESCE(FotoPencatat.file_path, 'default.jpg') as foto_pencatat")
                )
                ->groupBy(
                    'catatan_afektif.id',
                    'bs.id',
                    'bp.id',
                    'bs.nama',
                    'catatan_afektif.kepedulian_nilai',
                    'catatan_afektif.kepedulian_tindak_lanjut',
                    'catatan_afektif.kebersihan_nilai',
                    'catatan_afektif.kebersihan_tindak_lanjut',
                    'catatan_afektif.akhlak_nilai',
                    'catatan_afektif.akhlak_tindak_lanjut',
                    'bp.nama',
                    'wali_asuh.id',
                    'catatan_afektif.created_at',
                    'FotoCatatan.file_path',
                    'FotoPencatat.file_path'
                );
        } catch (\Exception $e) {
            Log::error('Error fetching data Catatan Afektif: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data Catatan Afektif',
                'code' => 500,
            ], 500);
        }
    }

    public function formatData($results, $kategori = null)
    {
        $kategori = strtolower($kategori);

        return collect($results->items())->flatMap(function ($item) use ($kategori) {
            $data = [];

            if ($kategori === 'akhlak') {
                if ($item->akhlak_nilai !== null) {
                    $data[] = [
                        'Biodata_uuid' => $item->Biodata_uuid,
                        'Pencatat_uuid' => $item->Pencatat_uuid,
                        'id_catatan' => $item->id_catatan,
                        'nama_santri' => $item->nama,
                        'blok' => $item->blok,
                        'wilayah' => $item->wilayah,
                        'pendidikan' => $item->jurusan,
                        'lembaga' => $item->lembaga,
                        'kategori' => 'Akhlak',
                        'nilai' => $item->akhlak_nilai,
                        'tindak_lanjut' => $item->akhlak_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        'jabatanPencatat' => $item->wali_asuh,
                        'waktu_pencatatan' => Carbon::parse($item->created_at)->format('d M Y H:i:s'),
                        'foto_catatan' => url($item->foto_catatan),
                        'foto_pencatat' => url($item->foto_pencatat),
                    ];
                }
            } elseif ($kategori === 'kepedulian') {
                if ($item->kepedulian_nilai !== null) {
                    $data[] = [
                        'Biodata_uuid' => $item->Biodata_uuid,
                        'Pencatat_uuid' => $item->Pencatat_uuid,
                        'id_catatan' => $item->id_catatan,
                        'nama_santri' => $item->nama,
                        'blok' => $item->blok,
                        'wilayah' => $item->wilayah,
                        'pendidikan' => $item->jurusan,
                        'lembaga' => $item->lembaga,
                        'kategori' => 'Kepedulian',
                        'nilai' => $item->kepedulian_nilai,
                        'tindak_lanjut' => $item->kepedulian_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        'jabatanPencatat' => $item->wali_asuh,
                        'waktu_pencatatan' => Carbon::parse($item->created_at)->format('d M Y H:i:s'),
                        'foto_catatan' => url($item->foto_catatan),
                        'foto_pencatat' => url($item->foto_pencatat),
                    ];
                }
            } elseif ($kategori === 'kebersihan') {
                if ($item->kebersihan_nilai !== null) {
                    $data[] = [
                        'Biodata_uuid' => $item->Biodata_uuid,
                        'Pencatat_uuid' => $item->Pencatat_uuid,
                        'id_catatan' => $item->id_catatan,
                        'nama_santri' => $item->nama,
                        'blok' => $item->blok,
                        'wilayah' => $item->wilayah,
                        'pendidikan' => $item->jurusan,
                        'lembaga' => $item->lembaga,
                        'kategori' => 'Kebersihan',
                        'nilai' => $item->kebersihan_nilai,
                        'tindak_lanjut' => $item->kebersihan_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        'jabatanPencatat' => $item->wali_asuh,
                        'waktu_pencatatan' => Carbon::parse($item->created_at)->format('d M Y H:i:s'),
                        'foto_catatan' => url($item->foto_catatan),
                        'foto_pencatat' => url($item->foto_pencatat),
                    ];
                }
            } else {
                // Kalau kategori null / tidak dipilih, munculkan semua kategori yang ada nilai
                if ($item->akhlak_nilai !== null) {
                    $data[] = [
                        'Biodata_uuid' => $item->Biodata_uuid,
                        'Pencatat_uuid' => $item->Pencatat_uuid,
                        'id_catatan' => $item->id_catatan,
                        'nama_santri' => $item->nama,
                        'blok' => $item->blok,
                        'wilayah' => $item->wilayah,
                        'pendidikan' => $item->jurusan,
                        'lembaga' => $item->lembaga,
                        'kategori' => 'Akhlak',
                        'nilai' => $item->akhlak_nilai,
                        'tindak_lanjut' => $item->akhlak_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        'jabatanPencatat' => $item->wali_asuh,
                        'waktu_pencatatan' => Carbon::parse($item->created_at)->format('d M Y H:i:s'),
                        'foto_catatan' => url($item->foto_catatan),
                        'foto_pencatat' => url($item->foto_pencatat),
                    ];
                }
                if ($item->kepedulian_nilai !== null) {
                    $data[] = [
                        'Biodata_uuid' => $item->Biodata_uuid,
                        'Pencatat_uuid' => $item->Pencatat_uuid,
                        'id_catatan' => $item->id_catatan,
                        'nama_santri' => $item->nama,
                        'blok' => $item->blok,
                        'wilayah' => $item->wilayah,
                        'pendidikan' => $item->jurusan,
                        'lembaga' => $item->lembaga,
                        'kategori' => 'Kepedulian',
                        'nilai' => $item->kepedulian_nilai,
                        'tindak_lanjut' => $item->kepedulian_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        'jabatanPencatat' => $item->wali_asuh,
                        'waktu_pencatatan' => Carbon::parse($item->created_at)->format('d M Y H:i:s'),
                        'foto_catatan' => url($item->foto_catatan),
                        'foto_pencatat' => url($item->foto_pencatat),
                    ];
                }
                if ($item->kebersihan_nilai !== null) {
                    $data[] = [
                        'Biodata_uuid' => $item->Biodata_uuid,
                        'Pencatat_uuid' => $item->Pencatat_uuid,
                        'id_catatan' => $item->id_catatan,
                        'nama_santri' => $item->nama,
                        'blok' => $item->blok,
                        'wilayah' => $item->wilayah,
                        'pendidikan' => $item->jurusan,
                        'lembaga' => $item->lembaga,
                        'kategori' => 'Kebersihan',
                        'nilai' => $item->kebersihan_nilai,
                        'tindak_lanjut' => $item->kebersihan_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        'jabatanPencatat' => $item->wali_asuh,
                        'waktu_pencatatan' => Carbon::parse($item->created_at)->format('d M Y H:i:s'),
                        'foto_catatan' => url($item->foto_catatan),
                        'foto_pencatat' => url($item->foto_pencatat),
                    ];
                }
            }

            return $data;
        });
    }

    public function store(array $input)
    {
        $santri = Santri::find($input['id_santri']);

        // Cek apakah santri ada dan status aktif = 'aktif'
        if (! $santri || $santri->status !== 'aktif') {
            return [
                'status' => false,
                'message' => 'Santri tidak aktif. Tidak bisa menambahkan catatan afektif.',
                'data' => null,
            ];
        }

        // Cek jika masih ada catatan aktif yang belum selesai
        $adaCatatanAktif = Catatan_afektif::where('id_santri', $input['id_santri'])
            ->where('status', 1)
            ->whereNull('tanggal_selesai')
            ->exists();

        if ($adaCatatanAktif) {
            return [
                'status' => false,
                'message' => 'Masih ada catatan afektif aktif yang belum diselesaikan.',
                'data' => null,
            ];
        }

        // Simpan catatan baru
        $catatan = Catatan_afektif::create([
            'id_santri' => $input['id_santri'],
            'id_wali_asuh' => $input['id_wali_asuh'],
            'kepedulian_nilai' => $input['kepedulian_nilai'],
            'kepedulian_tindak_lanjut' => $input['kepedulian_tindak_lanjut'],
            'kebersihan_nilai' => $input['kebersihan_nilai'],
            'kebersihan_tindak_lanjut' => $input['kebersihan_tindak_lanjut'],
            'akhlak_nilai' => $input['akhlak_nilai'],
            'akhlak_tindak_lanjut' => $input['akhlak_tindak_lanjut'],
            'tanggal_buat' => $input['tanggal_buat'] ?? now(),
            'status' => true,
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return [
            'status' => true,
            'message' => 'Catatan afektif berhasil ditambahkan.',
            'data' => $catatan,
        ];
    }
    public function updateKategori($id, Request $request)
    {
        $kategori = $request->kategori;
        $nilai = $request->nilai;
        $tindakLanjut = $request->tindak_lanjut;

        $kolomNilai = "{$kategori}_nilai";
        $kolomTindakLanjut = "{$kategori}_tindak_lanjut";

        $allowedColumns = [
            'akhlak_nilai', 'akhlak_tindak_lanjut',
            'kepedulian_nilai', 'kepedulian_tindak_lanjut',
            'kebersihan_nilai', 'kebersihan_tindak_lanjut',
        ];

        if (!in_array($kolomNilai, $allowedColumns) || !in_array($kolomTindakLanjut, $allowedColumns)) {
            throw new \Exception("Kolom tidak valid.");
        }

        $catatan = Catatan_afektif::findOrFail($id);

        // Cek kondisi sebelum update
        if (!is_null($catatan->tanggal_selesai)) {
            throw new \Exception("Data tidak bisa diubah karena sudah tidak aktif lagi.");
        }

        if ($catatan->status !== 1) {
            throw new \Exception("Data tidak bisa diubah karena status tidak aktif.");
        }

        // Update jika lolos pengecekan
        $catatan->$kolomNilai = $nilai;
        $catatan->$kolomTindakLanjut = $tindakLanjut;
        $catatan->updated_by = Auth::id();
        $catatan->save();

        return $catatan->refresh(); // Mengembalikan data terbaru
    }
}
