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
            ->leftJoin('domisili_santri AS ds', fn($j) => $j->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'))
            ->leftjoin('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
            ->leftjoin('blok AS bl', 'ds.blok_id', '=', 'bl.id')
            ->leftjoin('kamar AS km', 'ds.kamar_id', '=', 'km.id')
            ->leftjoin('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
            ->leftJoinSub($fotoLast, 'fl', fn ($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn ($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->where('as.status', true)
            ->where('ks.status', true)
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
                // ambil updated_at terbaru antar s, pd, ds
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

        DB::beginTransaction();
        try {
            // Ambil wali asuh dan jenis kelamin grup-nya
            $waliAsuh = Wali_asuh::with('santri.biodata', 'grup')->find($waliAsuhId);
            if (! $waliAsuh || ! $waliAsuh->grup) {
                return [
                    'success' => false,
                    'message' => 'Wali asuh atau grup tidak ditemukan.',
                    'data_baru' => [],
                    'data_gagal' => $santriIds,
                ];
            }

            $jenisKelaminGrup = strtolower($waliAsuh->grup->jenis_kelamin); // e.g. 'laki-laki'

            foreach ($santriIds as $idSantri) {
                if (in_array($idSantri, $anakAsuhAktif)) {
                    $dataGagal[] = [
                        'santri_id' => $idSantri,
                        'message' => 'Santri sudah menjadi anak asuh aktif.',
                    ];
                    continue;
                }

                // Ambil jenis kelamin santri anak asuh
                $santri = Santri::with('biodata')->find($idSantri);
                if (! $santri || ! $santri->biodata) {
                    $dataGagal[] = [
                        'santri_id' => $idSantri,
                        'message' => 'Santri tidak ditemukan.',
                    ];
                    continue;
                }

                $jenisKelaminSantri = strtolower($santri->biodata->jenis_kelamin);

                if ($jenisKelaminGrup !== 'campuran' && $jenisKelaminGrup !== $jenisKelaminSantri) {
                    $dataGagal[] = [
                        'santri_id' => $idSantri,
                        'message' => 'Jenis kelamin anak asuh tidak cocok dengan grup wali asuh.',
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
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
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

    public function pindahAnakasuh(array $input, int $idAnakAsuh): array
    {
        return DB::transaction(function () use ($input, $idAnakAsuh) {
            $anakAsuh = Anak_asuh::find($idAnakAsuh);
            if (! $anakAsuh) {
                return ['status' => false, 'message' => 'Data anak asuh tidak ditemukan.'];
            }

            // Cek kewaliasuhan aktif
            $kewAliasuhLama = Kewaliasuhan::where('id_anak_asuh', $idAnakAsuh)
                ->where('status', true)
                ->first();

            if (! $kewAliasuhLama) {
                return ['status' => false, 'message' => 'Data kewaliasuhan aktif tidak ditemukan.'];
            }

            // Validasi wali asuh baru
            $waliBaruId = $input['id_wali_asuh'] ?? null;
            $waliBaru = Wali_asuh::find($waliBaruId);
            if (! $waliBaru || ! $waliBaru->status) {
                return ['status' => false, 'message' => 'Wali asuh baru tidak valid atau tidak aktif.'];
            }

            $tanggalPindah = Carbon::parse($input['tanggal_mulai'] ?? now());

            if ($tanggalPindah->lt(Carbon::parse($kewAliasuhLama->tanggal_mulai))) {
                return ['status' => false, 'message' => 'Tanggal pindah tidak boleh sebelum tanggal mulai wali asuh sebelumnya.'];
            }

            // Tutup hubungan kewaliasuhan lama
            $kewAliasuhLama->update([
                'tanggal_berakhir' => $tanggalPindah->copy()->subDay(), // sehari sebelum pindah
                'status' => false,
                'updated_by' => Auth::id(),
            ]);

            // Buat hubungan baru
            $kewAliasuhBaru = Kewaliasuhan::create([
                'id_wali_asuh' => $waliBaruId,
                'id_anak_asuh' => $idAnakAsuh,
                'tanggal_mulai' => $tanggalPindah,
                'status' => true,
                'created_by' => Auth::id(),
            ]);

            return [
                'status' => true,
                'message' => 'Anak asuh berhasil dipindah ke wali asuh baru.',
                'data' => [
                    'kewaliasuhan_lama' => $kewAliasuhLama,
                    'kewaliasuhan_baru' => $kewAliasuhBaru,
                ],
            ];
        });
    }


    public function keluarAnakasuh(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $anakAsuh = Anak_asuh::find($id);
            if (! $anakAsuh) {
                return ['status' => false, 'message' => 'Data anak asuh tidak ditemukan.'];
            }

            // Ambil data kewaliasuhan yang aktif
            $kewaliasuhan = Kewaliasuhan::where('id_anak_asuh', $id)
                ->where('status', true)
                ->first();

            if (! $kewaliasuhan) {
                return ['status' => false, 'message' => 'Data kewaliasuhan aktif tidak ditemukan untuk anak ini.'];
            }

            $tanggalMulai = Carbon::parse($kewaliasuhan->tanggal_mulai);
            $tanggalKeluar = Carbon::parse($input['tanggal_berakhir'] ?? null);

            if (! $tanggalKeluar) {
                return ['status' => false, 'message' => 'Tanggal keluar tidak valid.'];
            }

            if ($tanggalKeluar->lt($tanggalMulai)) {
                return ['status' => false, 'message' => 'Tanggal keluar tidak boleh sebelum tanggal mulai.'];
            }

            // Update tabel kewaliasuhan
            $kewaliasuhan->update([
                'tanggal_berakhir' => $tanggalKeluar,
                'status' => false,
                'updated_by' => Auth::id(),
            ]);

            // Update status anak_asuh
            $anakAsuh->update([
                'status' => false,
                'updated_by' => Auth::id(),
            ]);

            return [
                'status' => true,
                'message' => 'Anak asuh berhasil dikeluarkan dari kewaliasuhan.',
                'data' => [
                    'anak_asuh' => $anakAsuh,
                    'kewaliasuhan' => $kewaliasuhan,
                ],
            ];
        });
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
