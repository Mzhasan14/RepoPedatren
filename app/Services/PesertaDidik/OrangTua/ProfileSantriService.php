<?php

namespace App\Services\PesertaDidik\OrangTua;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileSantriService
{
    public function ProfileSantri($request)
    {
        $user = Auth::user();
        $noKk = $user->no_kk;

        // 🔹 Ambil semua anak dari KK yang sama
        $anak = DB::table('keluarga as k')
            ->join('biodata as b', 'k.id_biodata', '=', 'b.id')
            ->join('santri as s', 'b.id', '=', 's.biodata_id')
            ->select('s.id as santri_id')
            ->where('k.no_kk', $noKk)
            ->get();

        if ($anak->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada data anak yang ditemukan.',
                'data'    => null,
                'status'  => 404,
            ];
        }

        // 🔹 Cek apakah santri_id valid
        $santriId = $request['santri_id'] ?? null;
        $dataAnak = $anak->firstWhere('santri_id', $santriId);

        if (!$dataAnak) {
            return [
                'success' => false,
                'message' => 'Santri tidak valid untuk user ini.',
                'data'    => null,
                'status'  => 403,
            ];
        }
        
        // 🔹 Ambil ID jenis berkas "Pas foto"
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        // 🔹 Subquery untuk ambil last_id pas foto per biodata
        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        // 🔹 Profil Santri
        $santri = DB::table('santri as s')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftJoin('warga_pesantren as wp', function ($j) {
                $j->on('b.id', '=', 'wp.biodata_id')
                    ->where('wp.status', true);
            })
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas as br', 'br.id', '=', 'fl.last_id')
            ->leftJoin(
                'domisili_santri AS ds',
                fn($join) =>
                $join->on('s.id', '=', 'ds.santri_id')
                    ->where('ds.status', 'aktif')
            )
            ->leftJoin('wilayah as w', 'w.id', '=', 'ds.wilayah_id')
            ->leftJoin('blok as bb', 'bb.id', '=', 'ds.blok_id')
            ->leftJoin('kamar as kk', 'kk.id', '=', 'ds.kamar_id')
            ->leftJoin(
                'pendidikan AS pd',
                fn($j) =>
                $j->on('b.id', '=', 'pd.biodata_id')
                    ->where('pd.status', 'aktif')
            )
            ->leftJoin('lembaga as la', 'pd.lembaga_id', '=', 'la.id')
            ->leftJoin('jurusan as js', 'pd.jurusan_id', '=', 'js.id')
            ->leftJoin('kelas as kl', 'pd.kelas_id', '=', 'kl.id')
            ->leftJoin('rombel as ro', 'pd.rombel_id', '=', 'ro.id')
            ->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
            ->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
            ->leftJoin('negara as ng', 'b.negara_id', '=', 'ng.id')
            ->where('s.id', $santriId)
            ->select([
                's.id as santri_id',
                'b.nama',
                DB::raw('COALESCE(b.nik, b.no_passport) as identitas'),
                'b.jenis_kelamin',
                'b.tempat_lahir',
                'b.tanggal_lahir',
                DB::raw("CONCAT(b.anak_keberapa, ' dari ', b.dari_saudara, ' bersaudara') as anak_ke"),
                DB::raw("TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) as umur"),
                'w.nama_wilayah as wilayah',
                'bb.nama_blok as blok',
                'kk.nama_kamar as kamar',
                'la.nama_lembaga as lembaga',
                'js.nama_jurusan as jurusan',
                'kl.nama_kelas as kelas',
                'ro.nama_rombel as rombel',
                'kc.nama_kecamatan as kecamatan',
                'kb.nama_kabupaten as kabupaten',
                'pv.nama_provinsi as provinsi',
                'ng.nama_negara as negara',
                'wp.niup',
                DB::raw("COALESCE(br.file_path, 'default.png') as pas_foto"),
            ])
            ->first();

        if ($santri) {
            $santri = collect($santri)->map(function ($val, $key) {
                if ($key === 'pas_foto') {
                    return $val === 'default.png'
                        ? asset('storage/berkas/default.png')
                        : asset('storage/' . ltrim($val, 'storage/')); // 🔹 buang prefix "storage/" kalau sudah ada
                }
                return $val;
            })->toArray();
        }


        // 🔹 Data Ortu & Wali
        $ortu = DB::table('keluarga as k')
            ->leftJoin('orang_tua_wali as ow', 'k.id_biodata', '=', 'ow.id_biodata')
            ->join('biodata as bo', 'ow.id_biodata', '=', 'bo.id')
            ->leftJoin('hubungan_keluarga as hk', 'ow.id_hubungan_keluarga', '=', 'hk.id')
            ->leftJoin('negara as n', 'n.id', '=', 'bo.negara_id')
            ->leftJoin('provinsi as pn', 'pn.id', '=', 'bo.negara_id')
            ->leftJoin('kabupaten as kn', 'kn.id', '=', 'bo.negara_id')
            ->where('k.no_kk', $noKk)
            ->select([
                'bo.nama',
                'bo.nik',
                DB::raw('hk.nama_status as status'),
                'bo.jenjang_pendidikan_terakhir',
                'bo.nama_pendidikan_terakhir',
                'bo.email',
                'bo.no_telepon',
                'bo.jalan',
                'n.nama_negara',
                'pn.nama_provinsi',
                'kn.nama_kabupaten',
                'ow.pekerjaan',
                'ow.penghasilan',
                'ow.wali',
            ])
            ->get();

        return [
            'success' => true,
            'message' => 'Profil santri ditemukan.',
            'data'    => [
                'Santri'    => $santri,
                'Ortu_Wali' => $ortu,
            ],
            'status'  => 200,
        ];
    }
}
