<?php

namespace App\Services\Pegawai;

use App\Models\Pegawai\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WaliKelasService
{
    public function baseWalikelasQuery(Request $request)
    {
        $pasFotoId = DB::table('jenis_berkas')->where('nama_jenis_berkas', 'Pas foto')->value('id');

        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        return DB::table('wali_kelas')
            ->join('pegawai', function ($join) {
                $join->on('wali_kelas.pegawai_id', '=', 'pegawai.id')
                    ->where('pegawai.status_aktif', 'aktif')
                    ->whereNull('pegawai.deleted_at');
            })
            ->join('biodata as b', 'b.id', '=', 'pegawai.biodata_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoin('rombel as r', 'r.id', '=', 'wali_kelas.rombel_id')
            ->leftJoin('kelas as k', 'k.id', '=', 'wali_kelas.kelas_id')
            ->leftJoin('jurusan as j', 'j.id', '=', 'wali_kelas.jurusan_id')
            ->leftJoin('lembaga as l', 'l.id', '=', 'wali_kelas.lembaga_id')
            ->leftJoin('angkatan as akt', 'akt.id', '=', 'wali_kelas.angkatan_id')
            ->leftJoin('pendidikan as pn', function ($join) {
                $join->on('pn.lembaga_id', '=', 'wali_kelas.lembaga_id')
                    ->on('pn.jurusan_id', '=', 'wali_kelas.jurusan_id')
                    ->on('pn.kelas_id', '=', 'wali_kelas.kelas_id')
                    ->where(function ($q) {
                        $q->whereColumn('pn.rombel_id', 'wali_kelas.rombel_id')
                            ->orWhere(function ($sub) {
                                $sub->whereNull('pn.rombel_id')
                                    ->whereNull('wali_kelas.rombel_id');
                            });
                    });
            })
            ->whereNull('wali_kelas.periode_akhir')
            ->where('wali_kelas.status_aktif', 'aktif');
    }
    public function getAllWalikelas(Request $request)
    {
        try {
            $query = $this->baseWalikelasQuery($request);

            $fields = [
                'pegawai.biodata_id as biodata_uuid',
                'b.nama',
                'wp.niup',
                DB::raw('COALESCE(b.nik, b.no_passport) as identitas'),
                'b.jenis_kelamin',
                'l.nama_lembaga',
                'j.nama_jurusan',
                'k.nama_kelas',
                'r.gender_rombel',
                'r.nama_rombel',
                DB::raw("DATE_FORMAT(wali_kelas.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
                DB::raw("DATE_FORMAT(wali_kelas.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
                DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil"),

                DB::raw("CONCAT(COUNT(CASE WHEN pn.status = 'aktif' THEN 1 END), ' murid aktif') as jumlah_murid")
            ];


            return $query->select($fields)->groupBy(
                'pegawai.biodata_id',
                'b.nama',
                'wp.niup',
                'b.nik',
                'b.no_passport',
                'b.jenis_kelamin',
                'l.nama_lembaga',
                'j.nama_jurusan',
                'k.nama_kelas',
                'r.nama_rombel',
                'r.gender_rombel',
                'wali_kelas.updated_at',
                'wali_kelas.created_at'
            );
        } catch (\Exception $e) {
            Log::error('Error fetching data Wali Kelas: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data Wali Kelas',
                'code' => 500,
            ], 500);
        }
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'id' => $item->biodata_uuid,
            'nama' => $item->nama,
            'niup' => $item->niup ?? '-',
            'nik_or_Passport' => $item->identitas,
            'JenisKelamin' => $item->jenis_kelamin === 'l' ? 'Laki-laki' : ($item->jenis_kelamin === 'p' ? 'Perempuan' : 'Tidak Diketahui'),
            'lembaga' => $item->nama_lembaga,
            'jurusan' => $item->nama_jurusan,
            'kelas' => $item->nama_kelas,
            'GenderRombel' => $item->gender_rombel,
            'JumlahMurid' => $item->jumlah_murid,
            'rombel' => $item->nama_rombel,
            'tgl_update' => $item->tgl_update ?? '-',
            'tgl_input' => $item->tgl_input,
            'foto_profil' => url($item->foto_profil),
        ]);
    }
}
