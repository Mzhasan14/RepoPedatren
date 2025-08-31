<?php

namespace App\Services\Kewaliasuhan;

use App\Models\Kewaliasuhan\Grup_WaliAsuh;
use App\Models\Kewaliasuhan\Wali_asuh;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GrupWaliasuhService
{
    public function getAllGrupWaliasuh(Request $request)
    {
        return DB::table('grup_wali_asuh AS gs')
            ->leftJoin('wali_asuh AS wa', function ($join) {
                $join->on('gs.wali_asuh_id', '=', 'wa.id')
                    ->where('wa.status', true);
            })
            ->leftJoin('santri AS s', 'wa.id_santri', '=', 's.id')
            ->leftJoin('biodata AS b', 's.biodata_id', '=', 'b.id')
            ->leftJoin('wilayah AS w', 'gs.id_wilayah', '=', 'w.id')
            ->leftJoin('anak_asuh AS aa', function ($join) {
                $join->on('gs.id', '=', 'aa.grup_wali_asuh_id')
                    ->where('aa.status', true);
            })
            ->select([
                'gs.id',
                'gs.nama_grup as group',
                's.nis',
                'b.nama as nama_wali_asuh',
                'w.nama_wilayah',
                DB::raw('COUNT(aa.id) as jumlah_anak_asuh'),
                'gs.updated_at',
                'gs.created_at',
                'gs.status'
            ])
            ->groupBy(
                'gs.id',
                'gs.nama_grup',
                's.nis',
                'b.nama',
                'w.nama_wilayah',
                'gs.updated_at',
                'gs.created_at',
                'gs.status'
            )
            ->orderBy('gs.id');
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'id' => $item->id,
            'group' => $item->group,
            'nis_wali_asuh' => $item->nis,
            'nama_wali_asuh' => $item->nama_wali_asuh,
            'wilayah' => $item->nama_wilayah,
            'jumlah_anak_asuh' => $item->jumlah_anak_asuh,
            'tgl_update' => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            'tgl_input' => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s') ?? '-',
            'status' => $item->status
        ]);
    }

    public function detail($id): array
    {
        try {
            $pasFotoId = DB::table('jenis_berkas')
                ->where('nama_jenis_berkas', 'Pas foto')
                ->value('id');

            // 🔹 Data Grup + Wali Asuh aktif
            $group = DB::table('grup_wali_asuh as g')
                ->select(
                    'g.id',
                    'g.nama_grup as nama_group',
                    'w.id as wali_asuh_id',
                    'bw.nama as nama_wali_asuh',
                    'wil.nama_wilayah as wilayah',
                    DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto")
                )
                ->leftJoin('wali_asuh as w', function ($join) {
                    $join->on('g.wali_asuh_id', '=', 'w.id')
                        ->where('w.status', true);
                })
                ->leftJoin('santri as sw', 'sw.id', '=', 'w.id_santri')
                ->leftJoin('biodata as bw', 'bw.id', '=', 'sw.biodata_id')
                ->leftJoin(
                    'domisili_santri AS ds',
                    fn($join) =>
                    $join->on('sw.id', '=', 'ds.santri_id')->where('ds.status', 'aktif')
                )
                ->leftJoin('wilayah AS wil', 'ds.wilayah_id', '=', 'wil.id')
                ->leftJoinSub(
                    DB::table('berkas')
                        ->select('biodata_id', DB::raw('MAX(id) as last_id'))
                        ->where('jenis_berkas_id', $pasFotoId)
                        ->groupBy('biodata_id'),
                    'fl',
                    fn($j) => $j->on('bw.id', '=', 'fl.biodata_id')
                )
                ->leftJoin('berkas as br', 'br.id', '=', 'fl.last_id')
                ->where('g.id', $id)
                ->where('g.status', true) // grup aktif
                ->first();

            if (! $group) {
                return [
                    'status'  => false,
                    'message' => 'Data tidak ditemukan',
                    'data'    => null
                ];
            }

            // 🔹 Daftar Anak Asuh aktif (langsung dari tabel anak_asuh)
            $anakAsuh = DB::table('anak_asuh as aa')
                ->select(
                    'aa.id as anak_asuh_id',
                    'pd.no_induk',
                    'ab.nama'
                )
                ->leftJoin('santri as ai', 'ai.id', '=', 'aa.id_santri')
                ->leftJoin('biodata as ab', 'ab.id', '=', 'ai.biodata_id')
                ->leftJoin('pendidikan AS pd', function ($j) {
                    $j->on('ab.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif');
                })
                ->where('aa.status', true) // anak asuh aktif
                ->where('aa.grup_wali_asuh_id', $group->id)
                ->orderBy('pd.no_induk')
                ->get();

            return [
                'status'  => true,
                'message' => 'Proses berhasil',
                'data'    => [
                    'group' => [
                        'grup_wali_id'     => $group->id,
                        'wali_asuh_id'     => $group->wali_asuh_id ?? '-',
                        'nama_group'       => $group->nama_group,
                        'nama_wali_asuh'   => $group->nama_wali_asuh ?? '-',
                        'wilayah'          => $group->wilayah ?? '-',
                        'foto'             => url($group->foto)
                    ],
                    'total'     => $anakAsuh->count(),
                    'anak_asuh' => $anakAsuh
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null
            ];
        }
    }

    public function nonaktifkanAnakAsuh(int $anakAsuhId): array
    {
        try {
            DB::transaction(function () use ($anakAsuhId) {
                $anakAsuh = DB::table('anak_asuh')
                    ->where('id', $anakAsuhId)
                    ->where('status', true)
                    ->first();

                if (!$anakAsuh) {
                    throw new \Exception('Anak asuh tidak ditemukan atau sudah nonaktif');
                }

                // Nonaktifkan anak asuh
                DB::table('anak_asuh')
                    ->where('id', $anakAsuhId)
                    ->update([
                        'status' => false,
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);
            });

            return [
                'status'  => true,
                'message' => 'Anak asuh berhasil dinonaktifkan',
            ];
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => 'Gagal menonaktifkan anak asuh: ' . $e->getMessage(),
            ];
        }
    }

    // public function index(): array
    // {
    //     $data = Grup_WaliAsuh::with(['wilayah'])->orderBy('id', 'asc')->get();

    //     return [
    //         'status' => true,
    //         'data' => $data->map(fn($item) => [
    //             'id' => $item->id,
    //             'nama_grup' => $item->nama_status,
    //             'wilayah' => $item->wilayah->nama_wilayah,
    //             'jenis_kelamin' => $item->jenis_kelamin,
    //             'status' => $item->status,
    //             'created_by' => $item->created_by,
    //             'created_at' => $item->created_at,
    //             'updated_by' => $item->updated_by,
    //             'updated_at' => $item->updated_at,
    //             'deleted_by' => $item->deleted_by,
    //             'deleted_at' => $item->deleted_at,
    //         ]),
    //     ];
    // }

    public function show(int $id)
    {
        $hubungan = DB::table('grup_wali_asuh AS gs')
            ->leftJoin('wali_asuh AS wa', 'gs.wali_asuh_id', '=', 'wa.id')
            ->select(
                'gs.id',
                'gs.nama_grup',
                'gs.wali_asuh_id',
                'gs.jenis_kelamin',
                'gs.id_wilayah',
                'gs.status',
            )
            ->where('gs.id', $id)
            ->first();

        if (! $hubungan) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        return [
            'status' => true,
            'data' => $hubungan,
        ];
    }
    public function showGrup(string $id)
    {
        $grup = Grup_WaliAsuh::find($id);

        if (!$grup) {
            return response()->json([
                'status' => false,
                'message' => 'Grup wali asuh tidak ditemukan',
                'data' => null
            ], 404);
        }
        return [
            'status' => true,
            'data' => $grup,
        ];
    }

    public function store(array $data): array
    {
        return DB::transaction(function () use ($data) {

            if (!Auth::id()) {
                return [
                    'status' => false,
                    'message' => 'Pengguna tidak terautentikasi',
                    'data' => null,
                ];
            }

            $waliAsuhId = null;

            // 🔹 Validasi jenis kelamin grup sesuai wilayah
            $wilayah = DB::table('wilayah')
                ->where('id', $data['id_wilayah'])
                ->where('status', true)
                ->first();

            if (!$wilayah) {
                return [
                    'status' => false,
                    'message' => 'Wilayah tidak ditemukan atau tidak aktif',
                    'data' => null,
                ];
            }

            // mapping enum jenis_kelamin grup -> kategori wilayah
            $mapJenis = [
                'l' => 'putra',
                'p' => 'putri',
            ];

            if (isset($mapJenis[$data['jenis_kelamin']]) && $wilayah->kategori !== $mapJenis[$data['jenis_kelamin']]) {
                return [
                    'status' => false,
                    'message' => 'Jenis kelamin grup tidak sesuai dengan kategori wilayah',
                    'data' => null,
                ];
            }

            // Jika wali_asuh_id dikirim dan bukan null, cek validitasnya
            if (!empty($data['wali_asuh_id'])) {
                $waliAsuh = DB::table('wali_asuh as ws')
                    ->join('santri as s', 'ws.id_santri', '=', 's.id')
                    ->join('biodata as b', 'b.id', 's.biodata_id')
                    ->leftJoin('domisili_santri as ds', function ($join) {
                        $join->on('s.id', '=', 'ds.santri_id')
                            ->where('ds.status', 'aktif');
                    })
                    ->leftJoin('wilayah as w', 'ds.wilayah_id', '=', 'w.id')
                    ->select('ws.id', 's.id as id_santri', 'ds.wilayah_id', 'b.jenis_kelamin')
                    ->where('ws.id', $data['wali_asuh_id'])
                    ->where('ws.status', true)
                    ->first();

                if (!$waliAsuh) {
                    return [
                        'status' => false,
                        'message' => 'Wali asuh tidak ditemukan atau tidak aktif',
                        'data' => null,
                    ];
                }

                // Validasi jenis kelamin wali asuh
                if ($waliAsuh->jenis_kelamin !== $data['jenis_kelamin']) {
                    return [
                        'status' => false,
                        'message' => 'Jenis kelamin wali asuh tidak sesuai dengan jenis kelamin grup',
                        'data' => null,
                    ];
                }

                // Validasi wilayah wali asuh
                if (is_null($waliAsuh->wilayah_id)) {
                    return [
                        'status' => false,
                        'message' => 'Wali asuh belum memiliki wilayah, tidak bisa ditambahkan ke grup',
                        'data' => null,
                    ];
                }

                if ($waliAsuh->wilayah_id != $data['id_wilayah']) {
                    return [
                        'status' => false,
                        'message' => 'Wilayah wali asuh tidak sesuai dengan wilayah grup',
                        'data' => null,
                    ];
                }

                // Validasi wali asuh belum punya grup aktif
                $grupAktif = DB::table('grup_wali_asuh')
                    ->where('wali_asuh_id', $waliAsuh->id)
                    ->where('status', true)
                    ->exists();

                if ($grupAktif) {
                    return [
                        'status' => false,
                        'message' => 'Wali asuh ini sudah terhubung dengan grup wali asuh aktif',
                        'data' => null,
                    ];
                }

                $waliAsuhId = $waliAsuh->id;
            }

            // Buat grup baru
            $grup = Grup_WaliAsuh::create([
                'id_wilayah'    => $data['id_wilayah'],
                'wali_asuh_id'  => $waliAsuhId, // bisa null
                'nama_grup'     => $data['nama_grup'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'status'        => true,
                'created_by'    => Auth::id(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // Log activity
            activity('grup_wali_asuh_create')
                ->performedOn($grup)
                ->withProperties([
                    'new_attributes' => $grup->getAttributes(),
                    'wali_asuh_id'   => $waliAsuhId,
                    'ip'             => request()->ip(),
                    'user_agent'     => request()->userAgent(),
                ])
                ->event('create_grup_wali_asuh')
                ->log('Grup wali asuh baru berhasil dibuat');

            return [
                'status'  => true,
                'message' => 'Grup wali asuh baru berhasil dibuat',
                'data'    => [
                    'grup'      => $grup,
                    'wali_asuh' => $waliAsuhId,
                ],
            ];
        });
    }


    public function update(array $data, string $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $grup = Grup_WaliAsuh::find($id);

            if (!$grup) {
                return ['status' => false, 'message' => 'Data grup tidak ditemukan'];
            }

            // 🔹 Cek apakah grup aktif
            if (!$grup->status) {
                return ['status' => false, 'message' => 'Grup ini sudah tidak aktif, tidak bisa diupdate'];
            }

            $jenisKelaminBaru = $data['jenis_kelamin'] ?? $grup->jenis_kelamin;
            $wilayahBaru      = $data['id_wilayah'] ?? $grup->id_wilayah;

            // 🔹 Validasi anak asuh: jenis kelamin
            $anakTidakSesuaiJK = DB::table('anak_asuh as aa')
                ->join('santri as s', 'aa.id_santri', '=', 's.id')
                ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                ->where('aa.grup_wali_asuh_id', $id)
                ->where('aa.status', true)
                ->where('b.jenis_kelamin', '!=', $jenisKelaminBaru)
                ->exists();

            if ($anakTidakSesuaiJK) {
                return [
                    'status' => false,
                    'message' => 'Jenis kelamin grup tidak sesuai dengan salah satu anak asuh.'
                ];
            }

            // 🔹 Validasi anak asuh: wilayah
            $anakTidakSesuaiWilayah = DB::table('anak_asuh as aa')
                ->join('santri as s', 'aa.id_santri', '=', 's.id')
                ->leftJoin('domisili_santri as ds', function ($join) {
                    $join->on('ds.santri_id', '=', 's.id')->where('ds.status', 'aktif');
                })
                ->where('aa.grup_wali_asuh_id', $id)
                ->where('aa.status', true)
                ->where(function ($q) use ($wilayahBaru) {
                    $q->where('ds.wilayah_id', '!=', $wilayahBaru)
                        ->orWhereNull('ds.wilayah_id');
                })
                ->exists();

            if ($anakTidakSesuaiWilayah) {
                return [
                    'status' => false,
                    'message' => 'Wilayah grup tidak sesuai dengan salah satu anak asuh.'
                ];
            }

            // Simpan data lama sebelum update
            $before = $grup->getOriginal();

            // Update atribut dasar grup
            $grup->fill([
                'id_wilayah'    => $wilayahBaru,
                'nama_grup'     => $data['nama_grup'] ?? $grup->nama_grup,
                'jenis_kelamin' => $jenisKelaminBaru,
                'updated_by'    => Auth::id(),
                'updated_at'    => now(),
            ]);

            // 🔹 Jika ada wali_asuh pengganti
            if (isset($data['wali_asuh_id'])) {
                $waliAsuhPengganti = DB::table('wali_asuh as w')
                    ->join('santri as s', 'w.id_santri', '=', 's.id')
                    ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                    ->leftJoin('domisili_santri as ds', function ($join) {
                        $join->on('ds.santri_id', '=', 's.id')->where('ds.status', 'aktif');
                    })
                    ->where('w.id', $data['wali_asuh_id'])
                    ->where('w.status', true) // hanya wali aktif
                    ->select('w.id as wali_id', 's.id as santri_id', 'b.jenis_kelamin', 'ds.wilayah_id')
                    ->first();

                if (!$waliAsuhPengganti) {
                    return ['status' => false, 'message' => 'Wali asuh pengganti tidak valid atau tidak aktif'];
                }

                // 🚫 Pastikan wali asuh pengganti belum dipakai di grup lain
                $sudahDipakai = DB::table('grup_wali_asuh')
                    ->where('wali_asuh_id', $waliAsuhPengganti->wali_id)
                    ->where('status', true)
                    ->where('id', '!=', $id)
                    ->exists();

                if ($sudahDipakai) {
                    return [
                        'status' => false,
                        'message' => 'Wali asuh pengganti sudah memiliki grup wali aktif lain.'
                    ];
                }

                // 🔹 Validasi jenis kelamin wali asuh pengganti
                if ($waliAsuhPengganti->jenis_kelamin !== $jenisKelaminBaru) {
                    return [
                        'status' => false,
                        'message' => 'Jenis kelamin wali asuh pengganti tidak sesuai dengan grup.'
                    ];
                }

                // 🔹 Validasi wilayah wali asuh pengganti
                if (!$waliAsuhPengganti->wilayah_id || $waliAsuhPengganti->wilayah_id != $wilayahBaru) {
                    return [
                        'status' => false,
                        'message' => 'Wilayah wali asuh pengganti tidak sesuai dengan grup atau belum terdaftar.'
                    ];
                }

                // ✅ Update grup dengan wali asuh pengganti
                $grup->wali_asuh_id = $waliAsuhPengganti->wali_id;
            }

            // 🔹 Cek perubahan
            if (!$grup->isDirty() && !isset($data['wali_asuh_id'])) {
                return ['status' => false, 'message' => 'Tidak ada perubahan'];
            }

            $grup->save();

            $batchUuid = Str::uuid();

            activity('grup_update')
                ->performedOn($grup)
                ->withProperties(['before' => $before, 'after' => $grup->getChanges()])
                ->tap(fn($activity) => $activity->batch_uuid = $batchUuid)
                ->event('update_grup')
                ->log('Data Grup wali asuh diperbarui');

            return ['status' => true, 'message' => 'Grup berhasil diperbarui', 'data' => $grup];
        });
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

            $grup = Grup_WaliAsuh::withTrashed()->find($id);

            if (!$grup) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data grup wali asuh tidak ditemukan',
                ], 404);
            }

            if ($grup->trashed()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data grup sudah dihapus sebelumnya',
                ], 410);
            }

            // Cek apakah grup masih memiliki anggota aktif
            $hasActiveMembers = Wali_asuh::where('id_grup_wali_asuh', $id)
                ->where('status', true)
                ->exists();

            if ($hasActiveMembers) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak dapat menghapus grup yang masih memiliki anggota aktif',
                ], 400);
            }

            // Ubah status menjadi non aktif, isi kolom deleted_by dan deleted_at
            $grup->status = false;
            $grup->deleted_by = Auth::id();
            $grup->deleted_at = now();
            $grup->save();

            // Log activity
            activity('grup_wali_asuh_nonaktifkan')
                ->performedOn($grup)
                ->withProperties([
                    'deleted_at' => $grup->deleted_at,
                    'deleted_by' => $grup->deleted_by,
                ])
                ->event('nonaktif_grup_wali_asuh')
                ->log('Grup wali asuh dinonaktifkan tanpa dihapus (soft update)');

            return response()->json([
                'status' => true,
                'message' => 'Grup wali asuh berhasil dinonaktifkan',
                'data' => [
                    'deleted_at' => $grup->deleted_at,
                ],
            ]);
        });
    }

    public function activate($id)
    {
        return DB::transaction(function () use ($id) {
            if (!Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengguna tidak terautentikasi',
                ], 401);
            }

            $grup = Grup_WaliAsuh::withTrashed()->find($id);

            if (!$grup) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data grup wali asuh tidak ditemukan',
                ], 404);
            }

            // Jika status sudah aktif
            if ($grup->status) {
                return response()->json([
                    'status' => false,
                    'message' => 'Grup wali asuh sudah dalam keadaan aktif',
                ], 400);
            }

            // Aktifkan kembali
            $grup->status = true;
            $grup->deleted_by = null;
            $grup->deleted_at = null;
            $grup->updated_by = Auth::id();
            $grup->updated_at = now();
            $grup->save();

            // Log activity
            activity('grup_wali_asuh_restore')
                ->performedOn($grup)
                ->event('restore_grup_wali_asuh')
                ->log('Grup wali asuh berhasil diaktifkan kembali');

            return response()->json([
                'status' => true,
                'message' => 'Grup wali asuh berhasil diaktifkan kembali',
            ]);
        });
    }

    public function getExportGrupWaliasuhQuery(array $fields, Request $request)
    {
        $query = $this->getAllGrupWaliasuh($request);

        // Dynamic joins
        if (in_array('no_kk', $fields)) {
            $query->leftJoin('keluarga as k', 'k.id_biodata', '=', 'b.id');
        }

        if (in_array('niup', $fields)) {
            $query->leftJoin('warga_pesantren as wp', 'wp.biodata_id', '=', 'b.id');
        }

        if (in_array('angkatan_santri', $fields)) {
            $query->leftJoin('angkatan as as', 's.angkatan_id', '=', 'as.id');
        }

        // Select fields
        $select = [];

        foreach ($fields as $field) {
            switch ($field) {
                case 'id':
                    $select[] = 'gs.id';
                    break;
                case 'nama_grup':
                    $select[] = 'gs.nama_grup';
                    break;
                case 'nama_wilayah':
                    $select[] = 'w.nama_wilayah';
                    break;
                case 'nis':
                    $select[] = 's.nis';
                    break;
                case 'nama_wali_asuh':
                    $select[] = 'b.nama as nama_wali_asuh';
                    break;
                case 'no_kk':
                    $select[] = 'k.no_kk as no_kk';
                    break;
                case 'nik':
                    $select[] = DB::raw('COALESCE(b.nik, b.no_passport) as nik');
                    break;
                case 'niup':
                    $select[] = 'wp.niup as niup';
                    break;
                case 'jenis_kelamin_wali_asuh':
                    $select[] = 'b.jenis_kelamin as jenis_kelamin_wali_asuh';
                    break;
                case 'angkatan_santri':
                    $select[] = 'as.angkatan as angkatan_santri';
                    break;
                case 'jumlah_anak_asuh':
                    $select[] = DB::raw("COUNT(CASE WHEN ks.status = true THEN aa.id ELSE NULL END) as jumlah_anak_asuh");
                    break;
                case 'created_at':
                    $select[] = 'gs.created_at';
                    break;
                case 'updated_at':
                    $select[] = 'gs.updated_at';
                    break;
                case 'status':
                    $select[] = 'gs.status';
                    break;
            }
        }

        $groupBy = [
            'gs.id',
            'gs.nama_grup',
            'w.nama_wilayah',
            's.nis',
            'b.nama',
            'b.jenis_kelamin',
            'gs.created_at',
            'gs.updated_at',
            'gs.status'
        ];

        if (in_array('no_kk', $fields)) {
            $groupBy[] = 'k.no_kk';
        }

        if (in_array('nik', $fields)) {
            $groupBy[] = DB::raw('COALESCE(b.nik, b.no_passport)');
        }

        if (in_array('niup', $fields)) {
            $groupBy[] = 'wp.niup';
        }

        if (in_array('angkatan_santri', $fields)) {
            $groupBy[] = 'as.angkatan';
        }

        $query->groupBy(...$groupBy);

        $query->select($select);

        return $query;
    }


    public function formatDataExportGrupWaliasuh($results, array $fields, $addNumber = false)
    {
        return collect($results)->map(function ($item, $idx) use ($fields, $addNumber) {
            $row = [];

            if ($addNumber) {
                $row['No'] = $idx + 1;
            }

            foreach ($fields as $field) {
                switch ($field) {
                    case 'nama_grup':
                        $row['Nama Grup'] = $item->nama_grup ?? '-';
                        break;
                    case 'nis':
                        $row['NIS Wali Asuh'] = $item->nis ?? '-';
                        break;
                    case 'nama_wali_asuh':
                        $row['Nama Wali Asuh'] = $item->nama_wali_asuh ?? '-';
                        break;
                    case 'nama_wilayah':
                        $row['Wilayah'] = $item->nama_wilayah ?? '-';
                        break;
                    case 'jumlah_anak_asuh':
                        $row['Jumlah Anak Asuh'] = $item->jumlah_anak_asuh ?? 0;
                        break;
                    case 'jenis_kelamin_wali_asuh':
                        $jk = strtolower($item->jenis_kelamin_wali_asuh ?? '');
                        $row['Jenis Kelamin Wali Asuh'] = $jk === 'l' ? 'Laki-laki' : ($jk === 'p' ? 'Perempuan' : '');
                        break;;
                    case 'no_kk':
                        $row['No KK Wali Asuh'] = ' ' . $item->no_kk ?? '';
                        break;
                    case 'nik':
                        $row['NIK Wali Asuh'] = ' ' . ($item->nik ?? $item->no_passport ?? '');
                        break;
                    case 'niup':
                        $row['NIUP Wali Asuh'] = ' ' . $item->niup ?? '';
                        break;
                    case 'angkatan_santri':
                        $row['Angkatan Santri'] = ' ' . $item->angkatan_santri ?? '';
                        break;
                    case 'created_at':
                        $row['Tanggal Input'] = $item->created_at
                            ? Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s')
                            : '-';
                        break;
                    case 'updated_at':
                        $row['Tanggal Update'] = $item->updated_at
                            ? Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s')
                            : '-';
                        break;
                    case 'status':
                        $row['Status'] = $item->status ? 'Aktif' : 'Nonaktif';
                        break;
                }
            }

            return $row;
        })->values();
    }

    public function getFieldExportGrupWaliasuhHeadings(array $fields, bool $addNumber = false): array
    {
        $map = [
            'nama_grup' => 'Nama Grup',
            'nama_wilayah' => 'Wilayah',
            'jumlah_anak_asuh' => 'Jumlah Anak Asuh',
            'no_kk' => 'No KK Wali Asuh',
            'nik' => 'NIK Wali Asuh',
            'niup' => 'NIUP Wali Asuh',
            'nis' => 'NIS Wali Asuh',
            'nama_wali_asuh' => 'Nama Wali Asuh',
            'jenis_kelamin_wali_asuh' => 'Jenis Kelamin Wali Asuh',
            'angkatan_santri' => 'Angkatan Santri',
            'created_at' => 'Tanggal Input',
            'updated_at' => 'Tanggal Update',
            'status' => 'Status',
        ];
        $headings = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $map)) {
                $mapped = $map[$field];
                if (is_array($mapped)) {
                    foreach ($mapped as $h) {
                        $headings[] = $h;
                    }
                } else {
                    $headings[] = $mapped;
                }
            } else {
                $headings[] = $field; // fallback kalau field tidak ada di map
            }
        }
        if ($addNumber) {
            array_unshift($headings, 'No');
        }

        return $headings;
    }
}
