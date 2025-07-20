<?php

namespace App\Services\PesertaDidik;

use App\Models\Khadam;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KhadamService
{
    public function baseKhadamQuery(Request $request)
    {
        // 1) Ambil ID jenis berkas 'Pas foto'
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        // Subqueries: ID terakhir berkas pas foto
        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        // Subqueries: ID terakhir warga pesantren yang aktif
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        $keluargaLast = DB::table('keluarga')
            ->select('id_biodata', DB::raw('MAX(id) AS last_id'))
            ->groupBy('id_biodata');

        $query = DB::table('khadam as kh')
            ->join('biodata as b', 'kh.biodata_id', '=', 'b.id')
            ->leftJoin('santri AS s', fn($j) => $j->on('b.id', '=', 's.biodata_id')->where('s.status', 'aktif'))
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoinSub($keluargaLast, 'kl', fn($j) => $j->on('b.id', '=', 'kl.id_biodata'))
            ->leftJoin('keluarga as k', 'k.id', '=', 'kl.last_id')
            ->where('kh.status', true)
            ->where(fn($q) => $q->whereNull('b.deleted_at')
                ->whereNull('s.deleted_at')
                ->whereNull('kh.deleted_at'));

        return $query;
    }

    // Query untuk LIST (select default)
    public function getAllKhadam(Request $request, $fields = null)
    {
        $query = $this->baseKhadamQuery($request);

        // SELECT default jika tidak dikasih field custom
        $fields = $fields ?? [
            'b.id as biodata_id',
            'kh.id',
            'wp.niup',
            DB::raw('COALESCE(b.nik, b.no_passport) as identitas'),
            'b.nama',
            'kh.keterangan',
            'kh.created_at',
            'kh.updated_at',
            DB::raw("COALESCE(br.file_path, 'default.jpg') as foto_profil"),
        ];

        return $query->select($fields);
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'biodata_id' => $item->biodata_id,
            'id_khadam' => $item->id,
            'niup' => $item->niup ?? '-',
            'nik' => $item->identitas ?? '-',
            'nama' => $item->nama,
            'keterangan' => $item->keterangan,
            'tgl_update' => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            'tgl_input' => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            'foto_profil' => url($item->foto_profil),
        ]);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();
            $now = now();

            // --- Validasi jika paspor diisi, maka negara bukan Indonesia ---
            if (! empty($data['passport'])) {
                // Pastikan negara_id ada dulu
                if (empty($data['negara_id'])) {
                    throw ValidationException::withMessages([
                        'negara_id' => ['Negara wajib dipilih jika mengisi paspor.'],
                    ]);
                }

                // Ambil data negara berdasarkan ID
                $negara = DB::table('negara')->where('id', $data['negara_id'])->first();

                // Cek jika negara tidak ditemukan (mungkin karena data di DB kosong)
                if (! $negara) {
                    throw ValidationException::withMessages([
                        'negara_id' => ['Negara tidak ditemukan di database.'],
                    ]);
                }

                // Jika negara asal adalah Indonesia, tolak pengisian paspor
                if (strtolower(trim($negara->nama_negara)) === 'indonesia') {
                    throw ValidationException::withMessages([
                        'passport' => ['Jika mengisi nomor paspor, negara asal tidak boleh Indonesia.'],
                    ]);
                }
            }


            // Biodata
            $nik = $data['nik'] ?? null;
            $existingBiodata = $nik ? DB::table('biodata')->where('nik', $nik)->first() : null;

            if ($existingBiodata) {
                // Cek apakah sudah ada khadam aktif
                $khadamAktif = DB::table('khadam')
                    ->where('biodata_id', $existingBiodata->id)
                    ->where('status', true)
                    ->whereNull('deleted_at')
                    ->first();

                if ($khadamAktif) {
                    throw ValidationException::withMessages([
                        'khadam' => ['Biodata ini sudah memiliki status khadam yang aktif.'],
                    ]);
                }
            }

            // Data biodata
            $biodataData = [
                'nama' => $data['nama'],
                'negara_id' => $data['negara_id'],
                'provinsi_id' => $data['provinsi_id'] ?? null,
                'kabupaten_id' => $data['kabupaten_id'] ?? null,
                'kecamatan_id' => $data['kecamatan_id'] ?? null,
                'jalan' => $data['jalan'] ?? null,
                'kode_pos' => $data['kode_pos'] ?? null,
                'no_passport' => $data['passport'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'tempat_lahir' => $data['tempat_lahir'],
                'nik' => $nik,
                'no_telepon' => $data['no_telepon'],
                'no_telepon_2' => $data['no_telepon_2'] ?? null,
                'email' => $data['email'],
                'jenjang_pendidikan_terakhir' => $data['jenjang_pendidikan_terakhir'] ?? null,
                'nama_pendidikan_terakhir' => $data['nama_pendidikan_terakhir'] ?? null,
                'anak_keberapa' => $data['anak_keberapa'] ?? null,
                'dari_saudara' => $data['dari_saudara'] ?? null,
                'tinggal_bersama' => $data['tinggal_bersama'] ?? null,
                'smartcard' => $data['smartcard'] ?? null,
                'updated_by' => $userId,
                'updated_at' => $now,
            ];

            if ($existingBiodata) {
                DB::table('biodata')->where('id', $existingBiodata->id)->update($biodataData);
                $biodataId = $existingBiodata->id;
            } else {
                do {
                    $smartcard = 'SC-' . strtoupper(Str::random(10));
                } while (DB::table('biodata')->where('smartcard', $smartcard)->exists());

                do {
                    $biodataId = Str::uuid()->toString();
                } while (DB::table('biodata')->where('id', $biodataId)->exists());

                DB::table('biodata')->insert(array_merge($biodataData, [
                    'id' => $biodataId,
                    'smartcard' => $smartcard,
                    'status' => true,
                    'created_by' => $userId,
                    'created_at' => $now,
                ]));
            }

            // Insert ke tabel khadam
            $khadamId = DB::table('khadam')->insertGetId([
                'biodata_id' => $biodataId,
                'keterangan' => $data['keterangan'],
                'tanggal_mulai' => $data['tanggal_mulai'],
                'status' => true,
                'created_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Upload Berkas (jika ada)
            if (! empty($data['berkas']) && is_array($data['berkas'])) {
                foreach ($data['berkas'] as $item) {
                    if (! ($item['file_path'] instanceof UploadedFile)) {
                        throw new \Exception('Berkas tidak valid');
                    }

                    $url = Storage::url($item['file_path']->store('Khadam', 'public'));
                    DB::table('berkas')->insert([
                        'biodata_id' => $biodataId,
                        'jenis_berkas_id' => (int) $item['jenis_berkas_id'],
                        'file_path' => $url,
                        'status' => true,
                        'created_by' => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            activity('khadam_registration')
                ->causedBy(Auth::user())
                ->performedOn(Khadam::find($khadamId))
                ->withProperties([
                    'khadam_id' => $khadamId,
                    'biodata_id' => $biodataId,
                    'berkas' => collect($data['berkas'] ?? [])->pluck('jenis_berkas_id'),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->event('create_khadam')
                ->log('Pendaftaran khadam baru berhasil disimpan.');

            return [
                'khadam_id' => $khadamId,
                'biodata_id' => $biodataId,
            ];
        });
    }

    // Query untuk EXPORT, join dan select dinamis sesuai field
    public function getExportKhadamQuery($fields, $request)
    {
        $query = $this->baseKhadamQuery($request);

        // Join dinamis
        if (in_array('alamat', $fields)) {
            $query->leftJoin('kecamatan as kc2', 'b.kecamatan_id', '=', 'kc2.id');
            $query->leftJoin('kabupaten as kb2', 'b.kabupaten_id', '=', 'kb2.id');
            $query->leftJoin('provinsi as pv2', 'b.provinsi_id', '=', 'pv2.id');
            $query->leftJoin('negara as ng2', 'b.negara_id', '=', 'ng2.id');
        }
        if (in_array('domisili_santri', $fields)) {
            // Tambahkan join domisili_santri, wilayah, blok, kamar,
            $query->leftJoin('domisili_santri AS ds', fn($join) => $join->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'));
            $query->leftJoin('wilayah as w', 'ds.wilayah_id', '=', 'w.id');
            $query->leftJoin('blok as bl2', 'ds.blok_id', '=', 'bl2.id');
            $query->leftJoin('kamar as km2', 'ds.kamar_id', '=', 'km2.id');
        }
        if (
            in_array('no_induk', $fields) || in_array('lembaga', $fields) || in_array('angkatan_pelajar', $fields) ||
            in_array('jurusan', $fields) || in_array('kelas', $fields) || in_array('rombel', $fields)
        ) {
            // JOIN pendidikan dan relasi pendukungnya
            $query->leftJoin('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'));
        }
        if (in_array('lembaga', $fields)) {
            $query->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id');
        }
        if (in_array('angkatan_pelajar', $fields)) {
            $query->leftJoin('angkatan as ap2', 'pd.angkatan_id', '=', 'ap2.id');
        }
        if (in_array('jurusan', $fields)) {
            $query->leftJoin('jurusan AS j2', 'pd.jurusan_id', '=', 'j2.id');
        }
        if (in_array('kelas', $fields)) {
            $query->leftJoin('kelas AS kls2', 'pd.kelas_id', '=', 'kls2.id');
        }
        if (in_array('rombel', $fields)) {
            $query->leftJoin('rombel AS r2', 'pd.rombel_id', '=', 'r2.id');
        }
        if (in_array('angkatan_santri', $fields)) {
            $query->leftJoin('angkatan as as2', 's.angkatan_id', '=', 'as2.id');
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

        // Mapping select sesuai $fields, sesuaikan alias yang baru!
        $select = [];
        foreach ($fields as $field) {
            switch ($field) {
                case 'nama':
                    $select[] = 'b.nama';
                    break;
                case 'tempat_lahir':
                    $select[] = 'b.tempat_lahir';
                    break;
                case 'tanggal_lahir':
                    $select[] = 'b.tanggal_lahir';
                    break;
                case 'jenis_kelamin':
                    $select[] = 'b.jenis_kelamin';
                    break;
                case 'keterangan':
                    $select[] = 'kh.keterangan';
                    break;
                case 'tanggal_mulai':
                    $select[] = 'kh.tanggal_mulai';
                    break;
                case 'nis':
                    $select[] = 's.nis';
                    break;
                case 'no_induk':
                    $select[] = 'pd.no_induk';
                    break;
                case 'lembaga':
                    $select[] = 'l.nama_lembaga as lembaga';
                    break;
                case 'jurusan':
                    $select[] = 'j2.nama_jurusan as jurusan';
                    break;
                case 'kelas':
                    $select[] = 'kls2.nama_kelas as kelas';
                    break;
                case 'rombel':
                    $select[] = 'r2.nama_rombel as rombel';
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
                    $select[] = 'w.nama_wilayah as dom_wilayah';
                    $select[] = 'bl2.nama_blok as dom_blok';
                    $select[] = 'km2.nama_kamar as dom_kamar';
                    break;
                case 'angkatan_santri':
                    $select[] = 'as2.angkatan as angkatan_santri';
                    break;
                case 'angkatan_pelajar':
                    $select[] = 'ap2.angkatan as angkatan_pelajar';
                    break;
                case 'ibu_kandung':
                    $select[] = 'b_ibu2.nama as nama_ibu';
                    break;
            }
        }

        return $query->select($select);
    }

    public function formatDataExport($results, $fields, $addNumber = false)
    {
        return collect($results)->values()->map(function ($item, $idx) use ($fields, $addNumber) {
            $data = [];
            if ($addNumber) {
                $data['No'] = $idx + 1;
            }
            $itemArr = (array) $item;
            $i = 0; // pointer index hasil select (array order)

            foreach ($fields as $field) {
                switch ($field) {
                    case 'nama':
                        $data['Nama'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'tempat_lahir':
                        $data['Tempat Lahir'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'tanggal_lahir':
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
                    case 'keterangan':
                        $data['Keterangan'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'tanggal_mulai':
                        $data['Tanggal Mulai'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'nis':
                        $data['NIS'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'no_induk':
                        $data['No. Induk'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'lembaga':
                        $data['Lembaga'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'jurusan':
                        $data['Jurusan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'kelas':
                        $data['Kelas'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'rombel':
                        $data['Rombel'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
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
                        $data['Wilayah Domisili'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Blok Domisili'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kamar Domisili'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'angkatan_santri':
                        $data['Angkatan Santri'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'angkatan_pelajar':
                        $data['Angkatan Pelajar'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'ibu_kandung':
                        $data['Ibu Kandung'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    default:
                        $data[$field] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                }
            }

            return $data;
        })->values();
    }

    public function getFieldExportHeadings($fields, $addNumber = false)
    {
        $map = [
            'nama' => 'Nama',
            'tempat_lahir' => 'Tempat Lahir',
            'tanggal_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
            'keterangan' => 'Keterangan',
            'tanggal_mulai' => 'Tanggal Mulai',
            'nis' => 'NIS',
            'no_induk' => 'No. Induk',
            'lembaga' => 'Lembaga',
            'jurusan' => 'Jurusan',
            'kelas' => 'Kelas',
            'rombel' => 'Rombel',
            'no_kk' => 'No. KK',
            'nik' => 'NIK',
            'niup' => 'NIUP',
            'anak_ke' => 'Anak ke',
            'jumlah_saudara' => 'Jumlah Saudara',
            'alamat' => ['Jalan', 'Kecamatan', 'Kabupaten', 'Provinsi', 'Negara'],
            'domisili_santri' => ['Wilayah Domisili', 'Blok Domisili', 'Kamar Domisili'],
            'angkatan_santri' => 'Angkatan Santri',
            'angkatan_pelajar' => 'Angkatan Pelajar',
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
