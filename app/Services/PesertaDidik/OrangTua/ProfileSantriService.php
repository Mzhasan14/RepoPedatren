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
                'kc.nama_kecamatan',
                'kb.nama_kabupaten',
                'pv.nama_provinsi',
                'ng.nama_negara',
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
            ->join('orang_tua_wali as ow', 'k.id_biodata', '=', 'ow.id_biodata')
            ->join('biodata as bo', 'ow.id_biodata', '=', 'bo.id')
            ->join('hubungan_keluarga as hk', 'ow.id_hubungan_keluarga', '=', 'hk.id')
            ->where('k.no_kk', $noKk)
            ->select([
                'bo.nama',
                'bo.nik',
                DB::raw('hk.nama_status as status'),
                'bo.jenjang_pendidikan_terakhir',
                'bo.nama_pendidikan_terakhir',
                'bo.email',
                'bo.no_telepon',
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
