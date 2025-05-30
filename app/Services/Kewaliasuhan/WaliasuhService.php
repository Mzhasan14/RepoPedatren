<?php

namespace App\Services\Kewaliasuhan;

use App\Models\Santri;
use App\Models\Biodata;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Kewaliasuhan\Anak_asuh;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\Kewaliasuhan\Kewaliasuhan;

class WaliasuhService
{
    public function getAllWaliasuh(Request $request) {
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

        return DB::table('wali_asuh AS ws')
            ->join('santri AS s', 'ws.id_santri','=','s.id')
            ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
            ->leftjoin('riwayat_domisili AS rd', fn($join) => $join->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
            ->leftjoin('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
            ->leftjoin('blok AS bl', 'rd.blok_id', '=', 'bl.id')
            ->leftjoin('kamar AS km', 'rd.kamar_id', '=', 'km.id')
            ->leftjoin('riwayat_pendidikan AS rp', fn($j) => $j->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->where('ws.status', true)
            ->select([
                's.biodata_id',
                'ws.id',
                's.nis',
                'b.nama',
                'km.nama_kamar',
                'bl.nama_blok',
                'w.nama_wilayah',
                DB::raw('YEAR(s.tanggal_masuk) as angkatan'),
                'kb.nama_kabupaten AS kota_asal',
                's.created_at',
                // ambil updated_at terbaru antar s, rp, rd
                DB::raw("
                   GREATEST(
                       s.updated_at,
                       COALESCE(ws.updated_at, s.updated_at),
                       COALESCE(ws.updated_at, s.updated_at)
                   ) AS updated_at
               "),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ])
            ->orderBy('ws.id');
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            "biodata_id" => $item->biodata_id,
            "id" => $item->id,
            "nis" => $item->nis,
            "nama" => $item->nama,
            "kamar" => $item->nama_kamar ?? '-',
            "blok" => $item->nama_blok ?? '-',
            "wilayah" => $item->nama_wilayah ?? '-',
            "kota_asal" => $item->kota_asal,
            "angkatan" => $item->angkatan,
            "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            "foto_profil" => url($item->foto_profil)
        ]);
    }

    public function index(string $bioId) :array {
         $list = DB::table('wali_asuh as w')
            ->join('santri as s', 'w.id_santri', '=', 's.id')
            ->where('s.biodata_id', $bioId)
            ->select([
                'w.id',
                's.nis',
                'w.tanggal_mulai',
                'w.tanggal_berakhir',
                'w.status'
            ])
            ->get();

        return [
            'status' => true,
            'data'   => $list->map(fn($item) => [
                'id'            => $item->id,
                'nis'           => $item->nis,
                'tanggal_mulai' => $item->tanggal_mulai,
                'tanggal_akhir' => $item->tanggal_berakhir,
                'status'        => $item->status,
            ]),
        ];
    }

    public function store(array $input, string $bioId): array
    {
        return DB::transaction(function () use ($input, $bioId) {
            // 1. Validasi biodata
            $biodata = Biodata::find($bioId);
            if (!$biodata) {
                return ['status' => false, 'message' => 'Biodata tidak ditemukan.'];
            }

            // 2. Cek apakah santri sudah ada
            $santri = Santri::where('biodata_id', $bioId)->first();
            if (!$santri) {
                return ['status' => false, 'message' => 'Data santri tidak ditemukan.'];
            }

            // 3. Cek apakah sudah menjadi wali asuh aktif
            $activeWaliExists = Wali_asuh::where('id_santri', $santri->id)
                ->where('status', true)
                ->exists();

            if ($activeWaliExists) {
                return ['status' => false, 'message' => 'Santri ini sudah terdaftar sebagai wali asuh aktif.'];
            }

            // 4. Buat data wali asuh
            $waliAsuh = Wali_asuh::create([
                'id_santri' => $santri->id,
                'id_grup_wali_asuh' => $input['id_grup_wali_asuh'] ?? null,
                'tanggal_mulai'  => Carbon::parse($input['tanggal_mulai']),
                'status' => true,
                'created_by' => Auth::id(),
            ]);

            // 5. Activity log
            activity('wali_asuh_create')
                ->performedOn($waliAsuh)
                ->withProperties([
                    'biodata_id' => $bioId,
                    'santri_id' => $santri->id,
                    'input' => $input
                ])
                ->log('Wali asuh baru ditambahkan');

            return [
                'status' => true,
                'data' => $waliAsuh,
                'message' => 'Wali asuh berhasil didaftarkan'
            ];
        });
    }

    public function show(int $id): array
    {
        $wa = Wali_asuh::with(['santri','grupWaliAsuh'])->find($id);

        if (!$wa) {
            return ['status' => false, 'message' => 'Data tidak ditemukan.'];
        }

        return ['status' => true, 'data' => [
            'id'    =>$wa->id,
            'nis'   =>$wa->santri->nis,
            'grup'  =>$wa->grupWaliAsuh->nama_grup,
            'tanggal_mulai' =>$wa->tanggal_mulai,
            'tanggal_akhir' =>$wa->tanggal_berakhir
        ]];
    }

    public function update(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $waliAsuh = Wali_Asuh::find($id);

            if (! $waliAsuh) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            // Cegah perubahan jika sudah punya tanggal berakhir
            if (! is_null($waliAsuh->tanggal_berakhir)) {
                return [
                    'status'  => false,
                    'message' => 'Data ini sudah memiliki tanggal berakhir dan tidak dapat diubah lagi demi menjaga histori.',
                ];
            }

            // Simpan data lama untuk log
            $before = $waliAsuh->getOriginal();

            // Nonaktifkan relasi lama jika grup berubah
            if (isset($input['id_grup_wali_asuh']) && $input['id_grup_wali_asuh'] != $waliAsuh->id_grup_wali_asuh) {
                Kewaliasuhan::where('id_wali_asuh', $waliAsuh->id)
                    ->where('status', true)
                    ->update([
                        'status'      => false,
                        'tanggal_berakhir' => Carbon::now(),
                        'updated_by'  => Auth::id(),
                        'updated_at'  => now(),
                    ]);

                activity('kewaliasuhan_update')
                    ->performedOn($waliAsuh)
                    ->withProperties([
                        'action'     => 'nonaktif_relasi_lama',
                        'grup_lama'  => $waliAsuh->id_grup_wali_asuh,
                        'grup_baru'  => $input['id_grup_wali_asuh'],
                    ])
                    ->log('Nonaktifkan relasi karena perubahan grup wali asuh');
            }

            // Update data
            $updateData = [
                'id_santri' => $input['id_santri'] ?? $waliAsuh->id_santri,
                'id_grup_wali_asuh' => $input['id_grup_wali_asuh'] ?? $waliAsuh->id_grup_wali_asuh,
                'tanggal_berakhir'  => isset($input['tanggal_berakhir']) ? Carbon::parse($input['tanggal_berakhir']) : $waliAsuh->tanggal_berakhir,
                'status'            => $input['status'] ?? $waliAsuh->status,
                'updated_by'        => Auth::id(),
                'updated_at'        => now(),
            ];

            $waliAsuh->update($updateData);

            // Log aktivitas
            activity('wali_asuh_update')
                ->performedOn($waliAsuh)
                ->withProperties([
                    'before' => $before,
                    'after'  => $waliAsuh->getChanges(),
                ])
                ->log('Data wali asuh diperbarui');

            return [
                'status'  => true,
                'message' => 'waliasuh berhasil diperbarui',
                'data'    => $waliAsuh,
            ];
        });
    }

    public function keluarWaliasuh(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $kh = Wali_asuh::find($id);
            if (!$kh) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            $tglKeluar = Carbon::parse($input['tanggal_berakhir'] ?? '');

            if ($tglKeluar->lt(Carbon::parse($kh->tanggal_mulai))) {
                return ['status' => false, 'message' => 'Tanggal akhir tidak boleh sebelum tanggal mulai.'];
            }

            $kh->update([
                'tanggal_berakhir'  => $tglKeluar,
                'status'         => false,
                'updated_by'     => Auth::id(),
            ]);

            return ['status' => true, 'data' => $kh];
        });
    }


    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            if (!Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengguna tidak terautentikasi'
                ], 401);
            }

            $waliAsuh = Wali_asuh::withTrashed()->find($id);

            if (!$waliAsuh) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data wali asuh tidak ditemukan'
                ], 404);
            }

            if ($waliAsuh->trashed()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data wali asuh sudah dihapus sebelumnya'
                ], 410);
            }

            // Cek relasi aktif sebelum hapus
            $hasActiveRelation = Kewaliasuhan::where('id_wali_asuh', $id)
                ->whereNull('tanggal_berakhir')
                ->exists();

            if ($hasActiveRelation) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak dapat menghapus wali asuh yang masih memiliki anak asuh aktif'
                ], 400);
            }

            // Soft delete
            $waliAsuh->delete();

            // // Update status santri
            // Santri::where('id', $waliAsuh->id_santri)->update(['status_wali_asuh' => false]);

            // Log activity
            activity('wali_asuh_delete')
                ->performedOn($waliAsuh)
                ->withProperties([
                    'deleted_at' => now(),
                    'deleted_by' => Auth::id()
                ])
                ->event('delete_wali_asuh')
                ->log('Wali asuh berhasil dihapus (soft delete)');

            return response()->json([
                'status' => true,
                'message' => 'Wali asuh berhasil dihapus',
                'data' => [
                    'deleted_at' => $waliAsuh->deleted_at
                ]
            ]);
        });
    }
}
  