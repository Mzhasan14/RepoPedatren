<?php

namespace App\Services\Kewaliasuhan;

use App\Models\Kewaliasuhan\Anak_asuh;
use App\Models\Kewaliasuhan\Kewaliasuhan;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnakasuhService
{
    public function getAllAnakasuh(Request $request)
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

        return DB::table('anak_asuh AS as')
            ->join('santri AS s', 'as.id_santri', '=', 's.id')
            ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
            ->join('kewaliasuhan as ks', 'ks.id_anak_asuh', '=', 'as.id')
            ->join('wali_asuh as ws', 'ks.id_wali_asuh', '=', 'ws.id')
            ->join('grup_wali_asuh as gs', 'ws.id_grup_wali_asuh', '=', 'gs.id')
            ->leftjoin('riwayat_domisili AS rd', fn ($join) => $join->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
            ->leftjoin('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
            ->leftjoin('blok AS bl', 'rd.blok_id', '=', 'bl.id')
            ->leftjoin('kamar AS km', 'rd.kamar_id', '=', 'km.id')
            ->leftjoin('riwayat_pendidikan AS rp', fn ($j) => $j->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
            ->leftJoinSub($fotoLast, 'fl', fn ($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn ($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->where('ws.status', true)
            ->select([
                's.biodata_id',
                'as.id',
                's.nis',
                'b.nama',
                DB::raw("CONCAT(km.nama_kamar,' - ',w.nama_wilayah) As kamar"),
                'gs.nama_grup',
                DB::raw('YEAR(s.tanggal_masuk) as angkatan'),
                'kb.nama_kabupaten AS kota_asal',
                's.created_at',
                // ambil updated_at terbaru antar s, rp, rd
                DB::raw('
                   GREATEST(
                       s.updated_at,
                       COALESCE(as.updated_at, s.updated_at),
                       COALESCE(gs.updated_at, s.updated_at)
                   ) AS updated_at
               '),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ])
            ->orderBy('as.id');
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn ($item) => [
            'biodata_id' => $item->biodata_id,
            'id' => $item->id,
            'nis' => $item->nis,
            'nama' => $item->nama,
            'kamar' => $item->kamar,
            'Group_Waliasuh' => $item->nama_grup,
            'kota_asal' => $item->kota_asal,
            'angkatan' => $item->angkatan,
            'tgl_update' => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            'tgl_input' => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            'foto_profil' => url($item->foto_profil),
        ]);
    }

    public function index(string $bioId): array
    {
        $list = DB::table('anak_asuh as a')
            ->join('santri as s', 'w.id_santri', '=', 's.id')
            ->join('kewaliasuhan as ks', 'id_wali_asuh', '=', 'w.id')
            ->where('s.biodata_id', $bioId)
            ->select([
                'a.id',
                's.nis',
                'ks.tanggal_mulai',
                'ks.tanggal_berakhir',
                'a.status',
            ])
            ->get();

        return [
            'status' => true,
            'data' => $list->map(fn ($item) => [
                'id' => $item->id,
                'nis' => $item->nis,
                'tanggal_mulai' => $item->tanggal_mulai,
                'tanggal_akhir' => $item->tanggal_berakhir,
                'status' => $item->status,
            ]),
        ];
    }

    public function store(array $data)
    {
        $now = Carbon::now();
        $userId = Auth::id();
        $santriIds = $data['santri_id'];
        $waliAsuhId = $data['id_wali_asuh'];

        $anakAsuhAktif = Anak_Asuh::whereIn('id_santri', $santriIds)
            ->where('status', true)
            ->pluck('id_santri')
            ->toArray();

        $dataBaru = [];
        $dataGagal = [];
        $relasiBaru = [];

        DB::beginTransaction();
        try {
            foreach ($santriIds as $idSantri) {
                if (in_array($idSantri, $anakAsuhAktif)) {
                    $dataGagal[] = [
                        'santri_id' => $idSantri,
                        'message' => 'Santri sudah menjadi anak asuh aktif.',
                    ];

                    continue;
                }

                // Tambah ke tabel anak_asuh
                $anakAsuh = Anak_Asuh::create([
                    'id_santri' => $idSantri,
                    'status' => true,
                    'created_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Tambah ke tabel kewaliasuhan
                Kewaliasuhan::create([
                    'id_wali_asuh' => $waliAsuhId,
                    'id_anak_asuh' => $anakAsuh->id,
                    'tanggal_mulai' => $now,
                    'status' => true,
                    'created_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $dataBaru[] = $idSantri;
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Santri berhasil ditambahkan sebagai anak asuh dan dikaitkan dengan wali asuh.',
                'data_baru' => $dataBaru,
                'data_gagal' => $dataGagal,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
                'data_baru' => [],
                'data_gagal' => $santriIds,
            ];
        }
    }

    protected function assignToWaliAsuh($anakAsuhId, $waliAsuhId)
    {
        // Validasi apakah wali asuh aktif
        $waliAsuh = Wali_asuh::where('id', $waliAsuhId)
            ->where('status', true)
            ->firstOrFail();

        // // Cek batas maksimal anak asuh per wali (contoh: maks 5)
        // $jumlahAnakAsuh = Kewaliasuhan::where('id_wali_asuh', $waliAsuhId)
        //     ->whereNull('tanggal_berakhir')
        //     ->count();

        // if ($jumlahAnakAsuh >= 5) {
        //     throw new \Exception("Wali asuh sudah mencapai batas maksimal anak asuh");
        // }

        // Buat relasi kewaliasuhan
        return Kewaliasuhan::create([
            'id_wali_asuh' => $waliAsuhId,
            'id_anak_asuh' => $anakAsuhId,
            'tanggal_mulai' => now(),
            'created_by' => Auth::id(),
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            if (! Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengguna tidak terautentikasi',
                ], 401);
            }

            $anakAsuh = Anak_asuh::withTrashed()->find($id);

            if (! $anakAsuh) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data anak asuh tidak ditemukan',
                ], 404);
            }

            if ($anakAsuh->trashed()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data anak asuh sudah dihapus sebelumnya',
                ], 410);
            }

            // Cek relasi aktif sebelum hapus
            $hasActiveRelation = Kewaliasuhan::where('id_anak_asuh', $id)
                ->whereNull('tanggal_berakhir')
                ->exists();

            if ($hasActiveRelation) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak dapat menghapus anak asuh yang masih memiliki relasi aktif',
                ], 400);
            }

            // Soft delete
            $anakAsuh->delete();

            // // Update status santri
            // Santri::where('id', $anakAsuh->id_santri)->update(['status_anak_asuh' => false]);

            // Log activity
            activity('anak_asuh_delete')
                ->performedOn($anakAsuh)
                ->withProperties([
                    'deleted_at' => now(),
                    'deleted_by' => Auth::id(),
                ])
                ->event('delete_anak_asuh')
                ->log('Anak asuh berhasil dihapus (soft delete)');

            return response()->json([
                'status' => true,
                'message' => 'Anak asuh berhasil dihapus',
                'data' => [
                    'deleted_at' => $anakAsuh->deleted_at,
                ],
            ]);
        });
    }
}
