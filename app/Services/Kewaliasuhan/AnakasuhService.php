<?php

namespace App\Services\Kewaliasuhan;

use Exception;
use App\Models\Santri;
use App\Models\Biodata;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Kewaliasuhan\Anak_asuh;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\Kewaliasuhan\Kewaliasuhan;

class AnakasuhService
{
    public function getAllAnakasuh(Request $request)
    {
        $user = Auth::user();

        // Ambil ID jenis berkas "Pas foto"
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        // Jika user punya role "waliasuh", ambil id_wali_asuh
        $waliAsuhId = null;
        if ($user->hasRole('waliasuh')) {
            $waliAsuhId = DB::table('wali_asuh as wa')
                ->join('santri as s','s.id','wa.id_santri')
                ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                ->join('user_biodata as ub','b.id','=','ub.biodata_id')
                ->join('users as u', 'u.id', '=', 'ub.user_id')
                ->where('u.id', $user->id)
                ->value('wa.id');
        }

        // Query utama
        $query = DB::table('anak_asuh AS aa')
            ->join('santri AS s', 'aa.id_santri', '=', 's.id')
            ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
            ->join('keluarga as k', 'k.id_biodata', '=', 'b.id')
            ->join('kewaliasuhan as ks', 'ks.id_anak_asuh', '=', 'aa.id')
            ->join('wali_asuh as wa', 'ks.id_wali_asuh', '=', 'wa.id')
            ->join('grup_wali_asuh as gw', 'wa.id_grup_wali_asuh', '=', 'gw.id')
            ->leftJoin('domisili_santri AS ds', fn($j) => $j->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'))
            ->leftjoin('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
            ->leftjoin('blok AS bl', 'ds.blok_id', '=', 'bl.id')
            ->leftjoin('kamar AS km', 'ds.kamar_id', '=', 'km.id')
            ->leftJoin('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
            ->leftJoinSub($fotoLast, 'fl', fn ($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn ($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->where('aa.status', true)
            ->where('ks.status', true)
            ->select([
                's.biodata_id',
                'aa.id',
                's.nis',
                'b.nama',
                DB::raw("CONCAT(km.nama_kamar,' - ',w.nama_wilayah) As kamar"),
                'gw.nama_grup',
                DB::raw('YEAR(s.tanggal_masuk) as angkatan'),
                'kb.nama_kabupaten AS kota_asal',
                's.created_at',
                DB::raw('
                    GREATEST(
                        s.updated_at,
                        COALESCE(aa.updated_at, s.updated_at),
                        COALESCE(gw.updated_at, s.updated_at)
                    ) AS updated_at
                '),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ])
            ->orderBy('aa.id');

        // Filter khusus jika wali_asuh
        if ($user->hasRole('waliasuh') && $waliAsuhId) {
            $query->where('wa.id', $waliAsuhId);
        } elseif ($user->hasRole('waliasuh') && !$waliAsuhId) {
            $query->whereRaw('1=0'); // user wali asuh tapi tidak punya relasi → kosong
        }

        return $query;
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
        $list = DB::table('anak_asuh as as')
            ->join('santri as s', 'as.id_santri', '=', 's.id')
            ->join('kewaliasuhan as k','k.id_anak_asuh','=','as.id')
            ->join('wali_asuh as w','w.id','=','k.id_wali_asuh')
            ->join('santri as sw','w.id_santri','=','sw.id')
            ->join('biodata as b','sw.biodata_id','=','b.id')
            ->where('s.biodata_id', $bioId)
            ->select([
                'as.id as id_anak_asuh',
                's.id as santriId',
                's.nis',
                'k.id_wali_asuh',
                'b.nama as nama_waliasuh',
                'k.id as kewid',
                'k.tanggal_mulai',
                'k.tanggal_berakhir',
                'as.status as status_anak_asuh',
                'k.status as status_kewaliasuhan',
            ])
            ->get();

        return [
            'status' => true,
            'data' => $list->map(fn($item) => [
                'id' => $item->kewid,
                'id_anak_asuh' => $item->id_anak_asuh,
                'id_santri' => $item->santriId,
                'nis' => $item->nis,
                'id_wali_asuh'  => $item->id_wali_asuh,
                'nama_wali_asuh' => $item->nama_waliasuh,
                'tanggal_mulai' => $item->tanggal_mulai,
                'tanggal_akhir' => $item->tanggal_berakhir,
                'status_anak_asuh' => $item->status_anak_asuh,
                'status_kewaliasuhan' => $item->status_kewaliasuhan,
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
            $waliAsuh = Wali_asuh::with('santri.biodata', 'grupWaliAsuh')->find($waliAsuhId);
            if (! $waliAsuh || ! $waliAsuh->grupWaliAsuh) {
                return [
                    'success' => false,
                    'message' => 'Wali asuh atau grup tidak ditemukan.',
                    'data_baru' => [],
                    'data_gagal' => $santriIds,
                ];
            }

            $jenisKelaminGrup = strtolower($waliAsuh->grupWaliAsuh->jenis_kelamin); // e.g. 'laki-laki'

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

            // Pengecekan baru: Jika tidak ada satupun santri yang berhasil ditambahkan
            if (empty($dataBaru) && !empty($dataGagal)) {
                return [
                    'success' => false, 
                    'message' => 'Tidak ada santri yang berhasil ditambahkan. ' . count($dataGagal) . ' santri gagal ditambahkan.',
                    'data_baru' => $dataBaru, 
                    'data_gagal' => $dataGagal,
                ];
            }
            // Jika ada yang berhasil (atau campuran berhasil dan gagal)
            return [
                'success' => true,
                'message' => 'Santri berhasil ditambahkan sebagai anak asuh dan dikaitkan dengan wali asuh. ' . count($dataBaru) . ' berhasil, ' . count($dataGagal) . ' gagal ditambahkan.',
                'data_baru' => $dataBaru,
                'data_gagal' => $dataGagal,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(), // Lebih spesifik untuk kesalahan sistem
                'data_baru' => [],
                'data_gagal' => $santriIds, // Atau lebih spesifik ke santri yang gagal karena error ini
            ];
        }
    }

    public function formStore(array $input, string $bioId): array
    {
        return DB::transaction(function () use ($input, $bioId) {
            // 1. Validasi biodata
            $biodata = Biodata::find($bioId);
            if (! $biodata) {
                return ['status' => false, 'message' => 'Biodata tidak ditemukan.'];
            }

            // 2. Cek apakah santri sudah ada
            $santri = Santri::where('biodata_id', $bioId)->first();
            if (! $santri) {
                return ['status' => false, 'message' => 'Data santri tidak ditemukan.'];
            }
            $idSantri = $santri->id;

            // 3. Cek apakah santri sudah menjadi anak asuh aktif
            $anakAsuhId = Anak_Asuh::where('id_santri', $idSantri)->value('id');

            $anakAsuhAktif = Kewaliasuhan::where('id_anak_asuh', $anakAsuhId)
                ->where('status', true)
                ->whereNull('tanggal_berakhir')
                ->exists();


            if ($anakAsuhAktif) {
                return ['status' => false, 'message' => 'Santri ini sudah menjadi anak asuh aktif.'];
            }

            // 4. Ambil wali asuh
            $waliAsuh = Wali_asuh::with('grupWaliAsuh')->find($input['id_wali_asuh'] ?? null);
            if (! $waliAsuh || ! $waliAsuh->grupWaliAsuh) {
                return ['status' => false, 'message' => 'Wali asuh atau grup tidak ditemukan.'];
            }

            // 5. Validasi jenis kelamin sesuai grup wali asuh
            $jenisKelaminSantri = strtolower($biodata->jenis_kelamin);
            $jenisKelaminGrup = strtolower($waliAsuh->grupWaliAsuh->jenis_kelamin);

            if ($jenisKelaminGrup !== 'campuran' && $jenisKelaminGrup !== $jenisKelaminSantri) {
                return ['status' => false, 'message' => 'Jenis kelamin santri tidak cocok dengan grup wali asuh.'];
            }

            // 6. Tambahkan ke tabel anak_asuh
            $anakAsuh = Anak_Asuh::create([
                'id_santri' => $santri->id,
                'status' => true,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 7. Tambahkan ke tabel kewaliasuhan
            $kewaliasuhan = Kewaliasuhan::create([
                'id_wali_asuh' => $waliAsuh->id,
                'id_anak_asuh' => $anakAsuh->id,
                'tanggal_mulai' => Carbon::parse($input['tanggal_mulai']),
                'status' => true,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 8. Logging
            activity('anak_asuh_create')
                ->performedOn($anakAsuh)
                ->withProperties([
                    'biodata_id' => $bioId,
                    'santri_id' => $santri->id,
                    'wali_asuh_id' => $waliAsuh->id,
                ])
                ->log('Anak asuh baru ditambahkan dan dihubungkan ke wali asuh');

            return [
                'status' => true,
                'message' => 'Anak asuh berhasil ditambahkan dan dikaitkan ke wali asuh.',
                'data' => [
                    'anak_asuh' => $anakAsuh,
                    'kewaliasuhan' => $kewaliasuhan,
                ],
            ];
        });
    }



    public function show(int $id): array
    {
            // 1. Langsung ambil data Kewaliasuhan berdasarkan ID yang diberikan
            $kewaliasuhan = Kewaliasuhan::with([
                'anakAsuh.santri.biodata', 
                'waliAsuh.santri.biodata'
            ])->findOrFail($id);

            // Jika kewaliasuhan ditemukan, ambil data terkait
            $anakAsuh = $kewaliasuhan->anakAsuh;
            $waliAsuh = $kewaliasuhan->waliAsuh;

            // Lakukan pengecekan null safety
            $nis = $anakAsuh->santri->nis ?? null;
            $namaWaliAsuh = $waliAsuh->santri->biodata->nama ?? null;

            return [
                'status' => true,
                'data' => [
                    'id' => $kewaliasuhan->id,
                    'id_anak_asuh' => $anakAsuh->id,
                    'nis' => $nis, 
                    'id_wali_asuh' => $waliAsuh->id, 
                    'nama_wali_asuh' => $namaWaliAsuh, 
                    'tanggal_mulai' => $kewaliasuhan->tanggal_mulai,
                    'tanggal_akhir' => $kewaliasuhan->tanggal_akhir, 
                    'status_kewaliasuhan' => $kewaliasuhan->status,
                    'status_anak_asuh' => $anakAsuh->status
                ]
            ];

        // $as = Anak_asuh::with(['santri', 'kewaliasuhan'])->where('kewaliasuhan.status', true)->find($id);

        // if (! $as) {
        //     return ['status' => false, 'message' => 'Data tidak ditemukan.'];
        // }

        // // Siapkan array untuk menampung semua data kewaliasuhan
        // $allKewaliasuhanData = [];

        // // Loop melalui setiap item di koleksi kewaliasuhan
        // foreach ($as->kewaliasuhan as $kewaliasuhan) {
        //     $allKewaliasuhanData[] = [
        //         'tanggal_mulai' => $kewaliasuhan->tanggal_mulai,
        //         'tanggal_akhir' => $kewaliasuhan->tanggal_berakhir,
        //         'status_kewaliasuhan' => $kewaliasuhan->status
        //     ];
        // }

        // return ['status' => true, 'data' => [
        //     'id' => $as->id,
        //     'nis' => $as->santri->nis,
        //     // 'kewaliasuhan' => $allKewaliasuhanData,
        //     'tanggal_mulai' => $as->kewaliasuhan->tanggal_mulai,
        //     'tanggal_berakhir'  => $as->kewaliasuhan->tanggal_berakhir,
        //     'status_anakasuh' => $as->status
        // ]];
    }

    public function update(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $kewaliasuhanSaatIni = Kewaliasuhan::find($id);

            if (!$kewaliasuhanSaatIni) {
                return ['status' => false, 'message' => 'Relasi kewaliasuhan tidak ditemukan.'];
            }

            $anakAsuh = Anak_Asuh::with('santri.biodata')->find($kewaliasuhanSaatIni->id_anak_asuh);

            if (! $anakAsuh) {
                return ['status' => false, 'message' => 'Data anak asuh tidak ditemukan.'];
            }

            $biodata = $anakAsuh->santri->biodata ?? null;
            if (! $biodata) {
                return ['status' => false, 'message' => 'Data biodata anak asuh tidak ditemukan.'];
            }

            $waliAsuhBaru = Wali_Asuh::with('grupWaliAsuh')->find($input['id_wali_asuh']);
            if (! $waliAsuhBaru || ! $waliAsuhBaru->grupWaliAsuh) {
                return ['status' => false, 'message' => 'Wali asuh atau grup wali asuh tidak ditemukan.'];
            }

            // Validasi jenis kelamin
            $jenisKelaminSantri = strtolower($biodata->jenis_kelamin);
            $jenisKelaminGrup = strtolower($waliAsuhBaru->grupWaliAsuh->jenis_kelamin);

            if ($jenisKelaminGrup !== 'campuran' && $jenisKelaminGrup !== $jenisKelaminSantri) {
                return ['status' => false, 'message' => 'Jenis kelamin santri tidak cocok dengan grup wali asuh.'];
            }

            // Ambil relasi lama (aktif)
            $kewaliasuhanLama = Kewaliasuhan::where('id_anak_asuh', $anakAsuh->id)
                ->where('status', true)
                ->first();

            // Jika wali asuh tidak berubah
            if ($kewaliasuhanLama && $input['id_wali_asuh'] == $kewaliasuhanLama->id_wali_asuh) {
                return [
                    'status' => false, 'message' => 'Wali asuh sama dengan sebelumnya, tidak ada perubahan.',
                ];
            }

            // Nonaktifkan relasi lama (jika ada)
            if ($kewaliasuhanLama) {
                $kewaliasuhanLama->update([
                    'status' => false,
                    'tanggal_berakhir' => now(),
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

                activity('kewaliasuhan_update')
                    ->performedOn($anakAsuh)
                    ->withProperties([
                        'action' => 'nonaktifkan_wali_lama',
                        'dari' => $kewaliasuhanLama->id_wali_asuh,
                        'ke' => $input['id_wali_asuh'],
                    ])
                    ->log('Relasi lama dinonaktifkan karena penggantian wali asuh');
            }

            // Tambahkan relasi baru
            $kewaliasuhanBaru = Kewaliasuhan::create([
                'id_wali_asuh' => $input['id_wali_asuh'],
                'id_anak_asuh' => $anakAsuh->id,
                'tanggal_mulai' => now(),
                'status' => true,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            activity('kewaliasuhan_create')
                ->performedOn($anakAsuh)
                ->withProperties([
                    'id_wali_asuh_baru' => $input['id_wali_asuh'],
                    'id_anak_asuh' => $anakAsuh->id,
                ])
                ->log('Relasi kewaliasuhan baru dibuat setelah update');

            return [
                'status' => true,
                'message' => 'Wali asuh berhasil diperbarui.',
                'data' => [
                    'anak_asuh' => $anakAsuh,
                    'kewaliasuhan_baru' => $kewaliasuhanBaru,
                ],
            ];
        });
    }

    public function pindahAnakasuh(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $kewaliasuhanSaatIni = Kewaliasuhan::find($id);

            if (!$kewaliasuhanSaatIni) {
                return ['status' => false, 'message' => 'Relasi kewaliasuhan tidak ditemukan.'];
            }

            $anakAsuh = Anak_Asuh::with('santri.biodata')->find($kewaliasuhanSaatIni->id_anak_asuh);

            if (! $anakAsuh) {
                return ['status' => false, 'message' => 'Data anak asuh tidak ditemukan.'];
            }

            // Ambil jenis kelamin santri dari relasi
            $jenisKelaminSantri = strtolower($anakAsuh->santri->biodata->jenis_kelamin ?? '');

            // Cek kewaliasuhan aktif
            $kewAliasuhLama = Kewaliasuhan::where('id_anak_asuh', $anakAsuh->id)
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

            $grupBaru = $waliBaru->grupWaliAsuh;
            $jenisKelaminGrup = strtolower($grupBaru->jenis_kelamin ?? '');

            // Validasi jenis kelamin
            if ($jenisKelaminGrup !== 'campuran' && $jenisKelaminGrup !== $jenisKelaminSantri) {
                return [
                    'status' => false,
                    'message' => 'Jenis kelamin santri tidak cocok dengan grup wali asuh baru.',
                ];
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
                'id_anak_asuh' => $anakAsuh->id,
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
            $kewaliasuhanSaatIni = Kewaliasuhan::find($id);

            if (!$kewaliasuhanSaatIni) {
                return ['status' => false, 'message' => 'Relasi kewaliasuhan tidak ditemukan.'];
            }

            $anakAsuh = Anak_Asuh::with('santri.biodata')->find($kewaliasuhanSaatIni->id_anak_asuh);

            if (! $anakAsuh) {
                return ['status' => false, 'message' => 'Data anak asuh tidak ditemukan.'];
            }

            // Ambil data kewaliasuhan yang aktif
            $kewaliasuhan = Kewaliasuhan::where('id_anak_asuh', $anakAsuh->id)
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
                'updated_at' => now(),
                'updated_by' => Auth::id(),
            ]);

            // Update status anak_asuh
            $anakAsuh->update([
                'status' => false,
                'updated_at' => now(),
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

    public function getExportAnakasuhQuery(array $fields, Request $request)
    {
        $query = $this->getAllAnakasuh($request);

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
        if (in_array('wali_asuh',$fields)) {
            $query->leftjoin('kewaliasuhan as kw','kw.id_anak_asuh','=','as.id')
            ->leftjoin('wali_asuh as wa','wa.id','=','kw.id_wali_asuh')
            ->leftjoin('santri as sw','wa.id_santri','=','sw.id')
            ->leftjoin('biodata as bw','sw.biodata_id','=','bw.id');
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
                case 'grup':
                    $select[] = 'gs.nama_grup';
                    break;
                case 'wali_asuh':
                    $select[] = 'bw.nama as nama_wali_asuh';
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


    public function formatDataExportAnakasuh($results, array $fields, bool $addNumber = false)
    {
        return collect($results)->values()->map(function ($item, $idx) use ($fields, $addNumber) {
            $data = [];
            if ($addNumber) {
                $data['No'] = $idx + 1;
            }
            $itemArr = (array) $item; // convert to array to support index based access for multi-fields

            $i = 0; // index pointer untuk multi-field
            foreach ($fields as $field) {
                switch ($field) {
                    case 'nama':
                        $data['Nama'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'tempat_tanggal_lahir':
                        $data['Tempat Lahir'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $tgl = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Tanggal Lahir'] = $tgl ? \Carbon\Carbon::parse($tgl)->translatedFormat('d F Y') : '';
                        break;
                    case 'jenis_kelamin':
                        $jk = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        if (strtolower($jk) === 'l') {
                            $data['Jenis Kelamin'] = 'Laki-laki';
                        } elseif (strtolower($jk) === 'p') {
                            $data['Jenis Kelamin'] = 'Perempuan';
                        } else {
                            $data['Jenis Kelamin'] = '';
                        }
                        break;
                    case 'nis':
                        $data['NIS'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'no_kk':
                        $data['No. KK'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'nik':
                        $data['NIK'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'niup':
                        $data['NIUP'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'anak_ke':
                        $data['Anak ke'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'jumlah_saudara':
                        $data['Jumlah Saudara'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'alamat':
                        $data['Jalan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kecamatan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kabupaten'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Provinsi'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Negara'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'domisili_santri':
                        $data['Wilayah'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Blok'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kamar'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'angkatan_santri':
                        $data['Angkatan Santri'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'angkatan_pelajar':
                        $data['Angkatan Pelajar'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'pendidikan':
                        $data['No. Induk'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        $data['Lembaga'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Jurusan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kelas'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Rombel'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'status':
                        $data['Status'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'ibu_kandung':
                        $data['Ibu Kandung'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'grup':
                        $data['Grup Wali Asuh'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'wali_asuh':
                        $data['Nama Wali Asuh'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    default:
                        // translate untuk created_at / updated_at
                        if (in_array($field, ['created_at', 'updated_at'])) {
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


    public function getFieldExportAnakasuhHeadings(array $fields, bool $addNumber = false)
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
            'grup' => 'Grup Wali Asuh',
            'wali_asuh' => 'Nama Wali Asuh',
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
}