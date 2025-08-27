<?php

namespace App\Services\Kewaliasuhan;

use App\Models\Biodata;
use App\Models\Kewaliasuhan\Kewaliasuhan;
use App\Models\Kewaliasuhan\Grup_WaliAsuh;
use App\Models\Kewaliasuhan\Anak_asuh;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WaliasuhService
{
    public function getAllWaliasuh(Request $request)
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

        return DB::table('wali_asuh AS ws')
            ->join('santri AS s', 'ws.id_santri', '=', 's.id')
            ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
            ->join('keluarga as k', 'k.id_biodata', '=', 'b.id')
            ->leftJoin('domisili_santri AS ds', fn($j) => $j->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'))
            ->leftjoin('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
            ->leftjoin('blok AS bl', 'ds.blok_id', '=', 'bl.id')
            ->leftjoin('kamar AS km', 'ds.kamar_id', '=', 'km.id')
            ->leftjoin('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->leftJoin('grup_wali_asuh as g', 'g.wali_asuh_id', '=', 'ws.id')
            ->where('ws.status', true)
            ->where(fn($q) => $q->whereNull('b.deleted_at')->whereNull('ws.deleted_at'))
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
                // ambil updated_at terbaru antar s, pd, rd
                DB::raw('
                   GREATEST(
                       s.updated_at,
                       COALESCE(ws.updated_at, s.updated_at),
                       COALESCE(ws.updated_at, s.updated_at)
                   ) AS updated_at
               '),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ])
            ->orderBy('ws.id');
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'biodata_id' => $item->biodata_id,
            'id' => $item->id,
            'nis' => $item->nis,
            'nama' => $item->nama,
            'kamar' => $item->nama_kamar ?? '-',
            'blok' => $item->nama_blok ?? '-',
            'wilayah' => $item->nama_wilayah ?? '-',
            'kota_asal' => $item->kota_asal,
            'angkatan' => $item->angkatan,
            'tgl_update' => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            'tgl_input' => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            'foto_profil' => url($item->foto_profil),
        ]);
    }

    public function index(string $bioId): array
    {
        $list = DB::table('wali_asuh as w')
            ->join('santri as s', 'w.id_santri', '=', 's.id')
            ->where('s.biodata_id', $bioId)
            ->select([
                'w.id',
                's.nis',
                'w.tanggal_mulai',
                'w.tanggal_berakhir',
                'w.status',
            ])
            ->get();

        return [
            'status' => true,
            'data' => $list->map(fn($item) => [
                'id' => $item->id,
                'nis' => $item->nis,
                'tanggal_mulai' => $item->tanggal_mulai,
                'tanggal_akhir' => $item->tanggal_berakhir,
                'status' => $item->status,
            ]),
        ];
    }

    public function store(array $input, string $bioId): array
    {
        return DB::transaction(function () use ($input, $bioId) {
            // 1. Cek biodata
            $biodata = Biodata::find($bioId);
            if (!$biodata) {
                return ['status' => false, 'message' => 'Biodata tidak ditemukan.'];
            }

            // 2. Cek santri aktif
            $santri = Santri::where('biodata_id', $bioId)
                ->where('status', 'aktif')
                ->first();

            if (!$santri) {
                return ['status' => false, 'message' => 'Data santri tidak ditemukan atau tidak aktif.'];
            }

            // 3. Cek wali asuh aktif
            $activeWaliExists = Wali_asuh::where('id_santri', $santri->id)
                ->where('status', true)
                ->exists();
            if ($activeWaliExists) {
                return ['status' => false, 'message' => 'Santri ini sudah terdaftar sebagai wali asuh aktif.'];
            }

            // 4️⃣ Cek apakah sudah menjadi Anak Asuh aktif
            $activeAnakAsuh = Anak_asuh::where('id_santri', $santri->id)
                ->where('status', true)
                ->whereNull('deleted_at')
                ->exists();

            if ($activeAnakAsuh) {
                return [
                    'status'  => false,
                    'message' => 'Santri ini sudah terdaftar sebagai anak asuh aktif.',
                    'data'    => null,
                ];
            }

            // 5. Validasi grup jika diinput
            $grupId = $input['grup_wali_asuh_id'] ?? null;
            if ($grupId) {
                $grup = DB::table('grup_wali_asuh as g')
                    ->select('g.id', 'g.nama_grup', 'g.jenis_kelamin', 'g.id_wilayah', 'g.status', 'g.wali_asuh_id')
                    ->where('g.id', $grupId)
                    ->where('g.status', true)
                    ->first();

                if (!$grup) {
                    return ['status' => false, 'message' => 'Grup wali asuh tidak ditemukan atau tidak aktif.'];
                }

                if ($grup->wali_asuh_id) {
                    return ['status' => false, 'message' => "Grup '{$grup->nama_grup}' sudah memiliki wali asuh."];
                }

                if ($biodata->jenis_kelamin !== $grup->jenis_kelamin) {
                    return ['status' => false, 'message' => 'Jenis kelamin santri tidak sesuai dengan grup.'];
                }

                $domisili = DB::table('domisili_santri as ds')
                    ->where('ds.santri_id', $santri->id)
                    ->where('ds.status', 'aktif')
                    ->value('ds.wilayah_id');

                if (!$domisili) {
                    return ['status' => false, 'message' => 'Santri belum memiliki wilayah aktif.'];
                }

                if ($domisili != $grup->id_wilayah) {
                    return ['status' => false, 'message' => 'Wilayah santri tidak sesuai dengan grup.'];
                }

                // Cek apakah sudah jadi wali asuh di grup lain
                $grupAktif = DB::table('grup_wali_asuh as g')
                    ->join('wali_asuh as ws', 'g.wali_asuh_id', '=', 'ws.id')
                    ->where('ws.id_santri', $santri->id)
                    ->where('ws.status', true)
                    ->where('g.status', true)
                    ->select('g.nama_grup')
                    ->first();

                if ($grupAktif) {
                    return ['status' => false, 'message' => "Santri ini sudah menjadi wali asuh di grup aktif: '{$grupAktif->nama_grup}'"];
                }
            }

            // 6. Buat wali_asuh
            $waliAsuh = Wali_asuh::create([
                'id_santri'     => $santri->id,
                'tanggal_mulai' => $input['tanggal_mulai'] ?? now(),
                'status'        => true,
                'created_by'    => Auth::id(),
            ]);

            // 7. Assign ke grup kalau ada
            if ($grupId) {
                DB::table('grup_wali_asuh')
                    ->where('id', $grupId)
                    ->update([
                        'wali_asuh_id' => $waliAsuh->id,
                        'updated_by'   => Auth::id(),
                        'updated_at'   => now(),
                    ]);
            }

            // 8. Log activity
            activity('wali_asuh_create')
                ->performedOn($waliAsuh)
                ->causedBy(Auth::user())
                ->withProperties([
                    'biodata_id' => $bioId,
                    'santri_id'  => $santri->id,
                    'grup_id'    => $grupId,
                    'input'      => $input,
                    'ip'         => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log('Wali asuh baru ditambahkan');

            return [
                'status'  => true,
                'message' => 'Wali asuh berhasil didaftarkan',
                'data'    => [
                    'wali_asuh' => $waliAsuh,
                    'grup_id'   => $grupId,
                    'santri_id' => $santri->id,
                ],
            ];
        });
    }



    public function show(int $id): array
    {
        // Ambil semua history wali asuh untuk santri tertentu
        $histories = DB::table('wali_asuh as wa')
            ->leftJoin('santri as s', 'wa.id_santri', '=', 's.id')
            ->leftJoin('grup_wali_asuh as gw', 'wa.id', '=', 'gw.wali_asuh_id')
            ->where('wa.id', $id)
            ->select([
                'wa.id',
                's.nis',
                'gw.id as grup_id',
                'wa.tanggal_mulai',
                'wa.tanggal_berakhir',
                'wa.status',
            ])
            ->orderBy('wa.tanggal_mulai', 'asc')
            ->get();

        if ($histories->isEmpty()) {
            return ['status' => false, 'message' => 'Data tidak ditemukan.'];
        }

        $data = $histories->map(function ($wa) {
            return [
                'id' => $wa->id,
                'nis' => $wa->nis,
                'id_grup_wali_asuh' => $wa->grup_id ?? '-',
                'tanggal_mulai' => $wa->tanggal_mulai,
                'tanggal_akhir' => $wa->tanggal_berakhir ?? '-',
                'tanggal_akhir' => $wa->status ?? '-',
            ];
        })->toArray();

        return [
            'status' => true,
            'data' => $data
        ];
    }

    public function update(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $waliAsuh = Wali_Asuh::find($id);

            if (!$waliAsuh) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            // Cegah perubahan jika sudah punya tanggal berakhir
            if (!is_null($waliAsuh->tanggal_berakhir)) {
                return [
                    'status' => false,
                    'message' => 'Data ini sudah memiliki tanggal berakhir dan tidak dapat diubah lagi demi menjaga histori.',
                ];
            }

            // Simpan data lama untuk log
            $before = $waliAsuh->getOriginal();

            // ✅ Hanya boleh update tanggal_mulai
            if (isset($input['tanggal_mulai'])) {
                $waliAsuh->update([
                    'tanggal_mulai' => Carbon::parse($input['tanggal_mulai']),
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);
            }

            // Log aktivitas
            activity('wali_asuh_update')
                ->performedOn($waliAsuh)
                ->withProperties([
                    'before' => $before,
                    'after' => $waliAsuh->getChanges(),
                ])
                ->log('Tanggal mulai wali asuh diperbarui');

            return [
                'status' => true,
                'message' => 'Tanggal mulai wali asuh berhasil diperbarui',
                'data' => $waliAsuh,
            ];
        });
    }


    public function keluarWaliasuh(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $kh = Wali_asuh::find($id);

            if (!$kh) {
                return [
                    'status'  => false,
                    'message' => 'Data tidak ditemukan.'
                ];
            }

            // Ambil tanggal berakhir dari input
            $tglKeluar = Carbon::parse($input['tanggal_berakhir'] ?? '');

            // Validasi: tanggal keluar tidak boleh sebelum tanggal mulai
            if ($tglKeluar->lt(Carbon::parse($kh->tanggal_mulai))) {
                return [
                    'status'  => false,
                    'message' => 'Tanggal akhir tidak boleh sebelum tanggal mulai.'
                ];
            }

            // Update data wali asuh
            $kh->update([
                'tanggal_berakhir' => $tglKeluar,
                'status'           => false,
                'updated_by'       => Auth::id(),
            ]);

            // 🔹 Update grup: set wali_asuh_id = null
            DB::table('grup_wali_asuh')
                ->where('wali_asuh_id', $kh->id)
                ->update([
                    'wali_asuh_id' => null,
                    'updated_by'   => Auth::id(),
                    'updated_at'   => now(),
                ]);

            return [
                'status' => true,
                'data'   => $kh,
                'message' => 'Wali asuh berhasil keluar dan grup diperbarui.'
            ];
        });
    }
    public function nonaktifkanWaliAsuh(int $waliAsuhId): array
    {
        try {
            DB::transaction(function () use ($waliAsuhId) {
                $waliAsuh = DB::table('wali_asuh')
                    ->where('id', $waliAsuhId)
                    ->where('status', true)
                    ->first();

                if (!$waliAsuh) {
                    throw new \Exception('Wali asuh tidak ditemukan atau sudah nonaktif');
                }

                // Nonaktifkan wali asuh
                DB::table('wali_asuh')
                    ->where('id', $waliAsuhId)
                    ->update([
                        'status' => false,
                        'tanggal_berakhir' => now(),
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);

                // Cek apakah wali asuh memiliki grup
                $grup = DB::table('grup_wali_asuh')
                    ->where('wali_asuh_id', $waliAsuhId)
                    ->get();

                if ($grup->count() > 0) {
                    // Set wali_asuh_id di grup menjadi null
                    DB::table('grup_wali_asuh')
                        ->where('wali_asuh_id', $waliAsuhId)
                        ->update([
                            'wali_asuh_id' => null,
                            'updated_by' => Auth::id(),
                            'updated_at' => now(),
                        ]);
                }
            });

            return [
                'status'  => true,
                'message' => 'Wali asuh berhasil dinonaktifkan',
            ];
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => 'Gagal menonaktifkan wali asuh: ' . $e->getMessage(),
            ];
        }
    }
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            if (!Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengguna tidak terautentikasi',
                ], 401);
            }

            $waliAsuh = Wali_asuh::withTrashed()->find($id);

            if (!$waliAsuh) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data wali asuh tidak ditemukan',
                ], 404);
            }

            if ($waliAsuh->trashed()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data wali asuh sudah dihapus sebelumnya',
                ], 410);
            }

            // Cek relasi aktif sebelum hapus
            $hasActiveRelation = Kewaliasuhan::where('id_wali_asuh', $id)
                ->whereNull('tanggal_berakhir')
                ->exists();

            if ($hasActiveRelation) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak dapat menghapus wali asuh yang masih memiliki anak asuh aktif',
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
                    'deleted_by' => Auth::id(),
                ])
                ->event('delete_wali_asuh')
                ->log('Wali asuh berhasil dihapus (soft delete)');

            return response()->json([
                'status' => true,
                'message' => 'Wali asuh berhasil dihapus',
                'data' => [
                    'deleted_at' => $waliAsuh->deleted_at,
                ],
            ]);
        });
    }

    public function getExportWaliasuhQuery(array $fields, Request $request)
    {
        $query = $this->getAllWaliasuh($request);

        // JOIN dinamis dengan alias unik"
        if (in_array('alamat', $fields)) {
            $query->leftJoin('kecamatan as kc2', 'b.kecamatan_id', '=', 'kc2.id');
            $query->leftJoin('kabupaten as kb2', 'b.kabupaten_id', '=', 'kb2.id');
            $query->leftJoin('provinsi as pv2', 'b.provinsi_id', '=', 'pv2.id');
            $query->leftJoin('negara as ng2', 'b.negara_id', '=', 'ng2.id');
        }
        if (in_array('domisili_santri', $fields)) {
            $query->leftJoin('domisili_santri AS ds2', fn($join) => $join->on('s.id', '=', 'ds2.santri_id')->where('ds2.status', 'aktif'));
            $query->leftJoin('wilayah as w2', 'ds2.wilayah_id', '=', 'w2.id');
            $query->leftJoin('blok as bl2', 'ds2.blok_id', '=', 'bl2.id');
            $query->leftJoin('kamar as km2', 'ds2.kamar_id', '=', 'km2.id');
        }
        if (in_array('angkatan_santri', $fields)) {
            $query->leftJoin('angkatan as as2', 's.angkatan_id', '=', 'as2.id');
        }
        if (in_array('angkatan_pelajar', $fields)) {
            $query->leftJoin('angkatan as ap2', 'pd.angkatan_id', '=', 'ap2.id');
        }
        if (in_array('pendidikan', $fields)) {
            $query->leftJoin('lembaga AS l2', 'pd.lembaga_id', '=', 'l2.id');
            $query->leftJoin('jurusan AS j2', 'pd.jurusan_id', '=', 'j2.id');
            $query->leftJoin('kelas AS kls2', 'pd.kelas_id', '=', 'kls2.id');
            $query->leftJoin('rombel AS r2', 'pd.rombel_id', '=', 'r2.id');
        }
        if (in_array('ibu_kandung', $fields)) {
            $subIbu = DB::table('keluarga as k1')
                ->select('k1.no_kk', 'otw2.id_biodata as id_biodata_ibu')
                ->join('orang_tua_wali as otw2', 'otw2.id_biodata', '=', 'k1.id_biodata')
                ->join('hubungan_keluarga as hk2', function ($join) {
                    $join->on('otw2.id_hubungan_keluarga', '=', 'hk2.id')
                        ->where('hk2.nama_status', '=', 'ibu kandung');
                });
            $query->leftJoinSub($subIbu, 'ibu2', function ($join) {
                $join->on('k.no_kk', '=', 'ibu2.no_kk');
            });
            $query->leftJoin('biodata as b_ibu2', 'ibu2.id_biodata_ibu', '=', 'b_ibu2.id');
        }
        // Tambahan untuk melihat grup dan jenis kelamin wali asuh
        if (in_array('grup_wali_asuh', $fields)) {
            $query->leftJoin('grup_wali_asuh as gwa', 'ws.id_grup_wali_asuh', '=', 'gwa.id');
        }
        $select = [];

        foreach ($fields as $field) {
            switch ($field) {
                case 'nama':
                    $select[] = 'b.nama';
                    break;
                case 'tempat_tanggal_lahir':
                    $select[] = 'b.tempat_lahir';
                    $select[] = 'b.tanggal_lahir';
                    break;
                case 'jenis_kelamin':
                    $select[] = 'b.jenis_kelamin';
                    break;
                case 'nis':
                    $select[] = 's.nis';
                    break;
                case 'no_kk':
                    $select[] = 'k.no_kk';
                    break;
                case 'nik':
                    $select[] = DB::raw('COALESCE(b.nik, b.no_passport) as nik');
                    break;
                case 'niup':
                    $select[] = 'wp.niup';
                    break;
                case 'anak_ke':
                    $select[] = 'b.anak_keberapa';
                    break;
                case 'jumlah_saudara':
                    $select[] = 'b.dari_saudara';
                    break;
                case 'alamat':
                    $select[] = 'b.jalan';
                    $select[] = 'kc2.nama_kecamatan';
                    $select[] = 'kb2.nama_kabupaten';
                    $select[] = 'pv2.nama_provinsi';
                    $select[] = 'ng2.nama_negara';
                    break;
                case 'domisili_santri':
                    $select[] = 'w2.nama_wilayah as dom_wilayah';
                    $select[] = 'bl2.nama_blok as dom_blok';
                    $select[] = 'km2.nama_kamar as dom_kamar';
                    break;
                case 'angkatan_santri':
                    $select[] = 'as2.angkatan as angkatan_santri';
                    break;
                case 'angkatan_pelajar':
                    $select[] = 'ap2.angkatan as angkatan_pelajar';
                    break;
                case 'pendidikan':
                    $select[] = 'pd.no_induk';
                    $select[] = 'l2.nama_lembaga as lembaga';
                    $select[] = 'j2.nama_jurusan as jurusan';
                    $select[] = 'kls2.nama_kelas as kelas';
                    $select[] = 'r2.nama_rombel as rombel';
                    break;
                case 'status':
                    $select[] = DB::raw(
                        "CASE 
                            WHEN s.status = 'aktif' AND pd.status = 'aktif' THEN 'santri-pelajar'
                            WHEN s.status = 'aktif' THEN 'santri'
                            WHEN pd.status = 'aktif' THEN 'pelajar'
                            ELSE ''
                        END as status"
                    );
                    break;
                case 'ibu_kandung':
                    $select[] = 'b_ibu2.nama as nama_ibu';
                    break;
                case 'grup_wali_asuh':
                    $select[] = 'gwa.nama_grup as grup_wali_asuh';
                    break;
                case 'created_at':
                    $select[] = 'ws.created_at';
                    break;
                case 'updated_at':
                    $select[] = DB::raw('GREATEST(
                    ws.updated_at,
                    COALESCE(s.updated_at, ws.updated_at),
                    COALESCE(b.updated_at, ws.updated_at)
                ) as updated_at');
                    break;
            }
        }

        $query->select($select);

        return $query;
    }

    public function getFieldExportWaliasuhHeadings($fields, $addNumber = false)
    {
        $map = [
            'nama' => 'Nama',
            'tempat_tanggal_lahir' => ['Tempat Lahir', 'Tanggal Lahir'],
            'jenis_kelamin' => 'Jenis Kelamin',
            'nis' => 'NIS',
            'no_kk' => 'No. KK',
            'nik' => 'NIK',
            'niup' => 'NIUP',
            'anak_ke' => 'Anak ke',
            'jumlah_saudara' => 'Jumlah Saudara',
            'alamat' => ['Jalan', 'Kecamatan', 'Kabupaten', 'Provinsi', 'Negara'],
            'domisili_santri' => ['Wilayah', 'Blok', 'Kamar'],
            'angkatan_santri' => 'Angkatan Santri',
            'angkatan_pelajar' => 'Angkatan Pelajar',
            'pendidikan' => ['No. Induk', 'Lembaga', 'Jurusan', 'Kelas', 'Rombel'],
            'grup_wali_asuh' => 'Grup Wali Asuh',
            'status' => 'Status',
            'ibu_kandung' => 'Ibu Kandung',
        ];
        $headings = [];
        foreach ($fields as $field) {
            if (isset($map[$field])) {
                if (is_array($map[$field])) {
                    foreach ($map[$field] as $h) {
                        $headings[] = $h;
                    }
                } else {
                    $headings[] = $map[$field];
                }
            } else {
                $headings[] = $field;
            }
        }
        if ($addNumber) {
            array_unshift($headings, 'No');
        }

        return $headings;
    }

    public function formatDataExportWaliasuh($results, array $fields, bool $translate = true, bool $addNumber = true)
    {
        return collect($results)->values()->map(function ($item, $idx) use ($fields, $translate, $addNumber) {
            $data = [];

            if ($addNumber) {
                $data['No'] = $idx + 1;
            }

            // Gunakan objek sebagai array
            $itemArr = (array) $item;

            foreach ($fields as $field) {
                switch ($field) {
                    case 'nama':
                        $data['Nama'] = $itemArr['nama'] ?? '';
                        break;
                    case 'tempat_tanggal_lahir':
                        $data['Tempat Lahir'] = $itemArr['tempat_lahir'] ?? '';
                        $tgl = $itemArr['tanggal_lahir'] ?? '';
                        $data['Tanggal Lahir'] = $tgl ? \Carbon\Carbon::parse($tgl)->translatedFormat('d F Y') : '';
                        break;
                    case 'jenis_kelamin':
                        $jk = strtolower($itemArr['jenis_kelamin'] ?? '');
                        $data['Jenis Kelamin'] = $jk === 'l' ? 'Laki-laki' : ($jk === 'p' ? 'Perempuan' : '');
                        break;
                    case 'nis':
                        $data['NIS'] = ' ' . ($itemArr['nis'] ?? '');
                        break;
                    case 'no_kk':
                        $data['No. KK'] = ' ' . ($itemArr['no_kk'] ?? '');
                        break;
                    case 'nik':
                        $data['NIK'] = ' ' . ($itemArr['nik'] ?? '');
                        break;
                    case 'niup':
                        $data['NIUP'] = ' ' . ($itemArr['niup'] ?? '');
                        break;
                    case 'anak_ke':
                        $data['Anak ke'] = $itemArr['anak_keberapa'] ?? '';
                        break;
                    case 'jumlah_saudara':
                        $data['Jumlah Saudara'] = $itemArr['dari_saudara'] ?? '';
                        break;
                    case 'alamat':
                        $data['Jalan'] = $itemArr['jalan'] ?? '';
                        $data['Kecamatan'] = $itemArr['nama_kecamatan'] ?? '';
                        $data['Kabupaten'] = $itemArr['nama_kabupaten'] ?? '';
                        $data['Provinsi'] = $itemArr['nama_provinsi'] ?? '';
                        $data['Negara'] = $itemArr['nama_negara'] ?? '';
                        break;
                    case 'domisili_santri':
                        $data['Wilayah'] = $itemArr['dom_wilayah'] ?? '';
                        $data['Blok'] = $itemArr['dom_blok'] ?? '';
                        $data['Kamar'] = $itemArr['dom_kamar'] ?? '';
                        break;
                    case 'angkatan_santri':
                        $data['Angkatan Santri'] = $itemArr['angkatan_santri'] ?? '';
                        break;
                    case 'angkatan_pelajar':
                        $data['Angkatan Pelajar'] = $itemArr['angkatan_pelajar'] ?? '';
                        break;
                    case 'pendidikan':
                        $data['No. Induk'] = ' ' . ($itemArr['no_induk'] ?? '');
                        $data['Lembaga'] = $itemArr['lembaga'] ?? '';
                        $data['Jurusan'] = $itemArr['jurusan'] ?? '';
                        $data['Kelas'] = $itemArr['kelas'] ?? '';
                        $data['Rombel'] = $itemArr['rombel'] ?? '';
                        break;
                    case 'grup_wali_asuh':
                        $data['Grup Wali Asuh'] = $itemArr['grup_wali_asuh'] ?? '';
                        break;
                    case 'status':
                        $data['Status'] = $itemArr['status'] ?? '';
                        break;
                    case 'ibu_kandung':
                        $data['Ibu Kandung'] = $itemArr['nama_ibu'] ?? '';
                        break;
                    default:
                        // translate untuk created_at / updated_at
                        if (in_array($field, ['created_at', 'updated_at']) && $translate) {
                            $data[$field] = !empty($itemArr[$field])
                                ? \Carbon\Carbon::parse($itemArr[$field])->translatedFormat('d F Y H:i:s')
                                : '';
                        } else {
                            $data[$field] = $itemArr[$field] ?? '';
                        }
                        break;
                }
            }

            return $data;
        })->values();
    }

    public function storeWaliAsuh(array $data): array
    {
        return DB::transaction(function () use ($data) {

            if (!Auth::id()) {
                return [
                    'status' => false,
                    'message' => 'Pengguna tidak terautentikasi',
                    'data' => null,
                ];
            }

            // 🔹 Ambil santri yang statusnya aktif (backend handle)
            $santri = DB::table('santri as s')
                ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                ->select('s.id', 'b.jenis_kelamin', 's.status')
                ->where('s.id', $data['id_santri'])
                ->where('s.status', 'aktif') // otomatis filter aktif
                ->first();

            if (!$santri) {
                return [
                    'status' => false,
                    'message' => 'Santri tidak ditemukan atau tidak aktif',
                    'data' => null,
                ];
            }
            // 🔹 Cek apakah santri sudah menjadi wali asuh aktif (di tabel wali_asuh saja)
            $alreadyWaliAsuh = DB::table('wali_asuh')
                ->where('id_santri', $santri->id)
                ->where('status', true)
                ->exists();

            if ($alreadyWaliAsuh) {
                return [
                    'status' => false,
                    'message' => 'Santri ini sudah menjadi wali asuh aktif.',
                    'data' => null,
                ];
            }

            $grupId = $data['grup_wali_asuh_id'] ?? null;

            if ($grupId) {
                $grup = DB::table('grup_wali_asuh as g')
                    ->select('g.id', 'g.nama_grup', 'g.jenis_kelamin', 'g.id_wilayah', 'g.status', 'g.wali_asuh_id')
                    ->where('g.id', $grupId)
                    ->where('g.status', true)
                    ->first();

                if (!$grup) {
                    return [
                        'status' => false,
                        'message' => 'Grup wali asuh tidak ditemukan atau tidak aktif',
                        'data' => null,
                    ];
                }

                if ($grup->wali_asuh_id) {
                    return [
                        'status' => false,
                        'message' => "Grup '{$grup->nama_grup}' sudah memiliki wali asuh",
                        'data' => null,
                    ];
                }

                if ($santri->jenis_kelamin !== $grup->jenis_kelamin) {
                    return [
                        'status' => false,
                        'message' => 'Jenis kelamin santri tidak sesuai dengan grup',
                        'data' => null,
                    ];
                }

                $domisili = DB::table('domisili_santri as ds')
                    ->where('ds.santri_id', $santri->id)
                    ->where('ds.status', 'aktif')
                    ->value('ds.wilayah_id');

                if (!$domisili) {
                    return [
                        'status' => false,
                        'message' => 'Santri belum memiliki wilayah aktif',
                        'data' => null,
                    ];
                }

                if ($domisili != $grup->id_wilayah) {
                    return [
                        'status' => false,
                        'message' => 'Wilayah santri tidak sesuai dengan grup',
                        'data' => null,
                    ];
                }

                // Cek apakah santri sudah jadi wali asuh di grup lain
                $grupAktif = DB::table('grup_wali_asuh as g')
                    ->join('wali_asuh as ws', 'g.wali_asuh_id', '=', 'ws.id')
                    ->where('ws.id_santri', $santri->id)
                    ->where('ws.status', true)
                    ->where('g.status', true)
                    ->select('g.nama_grup')
                    ->first();

                if ($grupAktif) {
                    return [
                        'status' => false,
                        'message' => "Santri ini sudah menjadi wali asuh di grup aktif: '{$grupAktif->nama_grup}'",
                        'data' => null,
                    ];
                }
            }

            // 🔹 Tambahkan wali_asuh baru
            $waliAsuhId = DB::table('wali_asuh')->insertGetId([
                'id_santri'     => $santri->id,
                'tanggal_mulai' => now(),
                'status'        => true,
                'created_by'    => Auth::id(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // 🔹 Update grup jika diinput
            if ($grupId) {
                DB::table('grup_wali_asuh')
                    ->where('id', $grupId)
                    ->update([
                        'wali_asuh_id' => $waliAsuhId,
                        'updated_by'   => Auth::id(),
                        'updated_at'   => now(),
                    ]);
            }

            // 🔹 Log activity
            $waliAsuh = Wali_asuh::find($waliAsuhId);

            activity()
                ->performedOn($waliAsuh)
                ->causedBy(Auth::user())
                ->withProperties([
                    'grup_id'   => $grupId,
                    'santri_id' => $santri->id,
                    'ip'        => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->event('create_wali_asuh')
                ->log('Wali asuh baru berhasil dibuat');

            return [
                'status'  => true,
                'message' => 'Data wali asuh berhasil ditambahkan',
                'data'    => [
                    'wali_asuh_id' => $waliAsuhId,
                    'grup_id'      => $grupId,
                    'santri_id'    => $santri->id,
                ],
            ];
        });
    }
}
