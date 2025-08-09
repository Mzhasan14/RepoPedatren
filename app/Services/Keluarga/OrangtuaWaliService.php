<?php

namespace App\Services\Keluarga;

use App\Models\Biodata;
use App\Models\Keluarga;
use Illuminate\Support\Str;
use App\Models\OrangTuaWali;
use Illuminate\Http\Request;
use App\Models\Alamat\Negara;
use Illuminate\Support\Carbon;
use App\Models\Alamat\Provinsi;
use App\Models\Alamat\Kabupaten;
use App\Models\Alamat\Kecamatan;
use App\Models\HubunganKeluarga;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OrangtuaWaliService
{
    public function getAllOrangtua(Request $request)
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

        // 3) Query utama: data orang_tua all
        return DB::table('orang_tua_wali AS o')
            ->join('biodata AS b', 'o.id_biodata', '=', 'b.id')
            // join berkas pas foto terakhir
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->join('hubungan_keluarga AS hk', 'hk.id', '=', 'o.id_hubungan_keluarga')
            ->join('keluarga AS kel', 'b.id', '=', 'kel.id_biodata') // dari orangtua ke tabel keluarga
            ->join('keluarga as ka', 'kel.no_kk', '=', 'ka.no_kk') // dari keluarga ke keluarga lainnya
            ->join('biodata as ba', 'ka.id_biodata', '=', 'ba.id') // dari keluarga ke anak
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            // hanya yang berstatus aktif
            ->where(fn($q) => $q->where('o.status', true))
            ->select([
                'o.id_biodata AS biodata_id',
                'o.id',
                DB::raw('COALESCE(b.nik, b.no_passport) AS identitas'),
                'b.nama',
                'b.no_telepon AS telepon_1',
                'b.no_telepon_2 AS telepon_2',
                'kb.nama_kabupaten AS kota_asal',
                'o.created_at',
                // ambil updated_at terbaru antar s, rp, rd
                DB::raw('
                        GREATEST(
                            o.updated_at,
                            hk.updated_at,
                            kel.updated_at
                        ) AS updated_at
                    '),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ])
            ->groupBy([
                'o.id_biodata',
                'o.id',
                'b.nik',
                'b.no_passport',
                'b.nama',
                'b.no_telepon',
                'b.no_telepon_2',
                'kb.nama_kabupaten',
                'o.created_at',
                'o.updated_at',
                'hk.updated_at',
                'kel.updated_at',
                'br.file_path',
            ])
            ->latest('b.created_at');
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'biodata_id' => $item->biodata_id,
            'id' => $item->id,
            'nik' => $item->identitas,
            'nama' => $item->nama,
            'telepon_1' => $item->telepon_1,
            'telepon_2' => $item->telepon_2,
            'kota_asal' => $item->kota_asal,
            'tgl_update' => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            'tgl_input' => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            'foto_profil' => url($item->foto_profil),
        ]);
    }

    public function index(string $bioId): array
    {
        $list = OrangTuaWali::with('biodata', 'keluarga')
            ->where('id_biodata', $bioId)
            ->get();

        return [
            'status' => true,
            'data' => $list->map(fn($item) => [
                'id' => $item->id,
                'id_biodata' => $item->id_biodata,
                'no_passport' => $item->biodata->no_passport,
                'no_kk' => optional($item->keluarga)->first()->no_kk,
                'nik' => $item->biodata->nik,
                'nama' => $item->biodata->nama,
                'jenis_kelamin' => $item->biodata->jenis_kelamin ?? null,
                'tanggal_lahir' => $item->biodata->tanggal_lahir
                    ? Carbon::parse($item->biodata->tanggal_lahir)->format('Y-m-d')
                    : null,
                'tempat_lahir' => $item->biodata->tempat_lahir,
                'anak_keberapa' => $item->biodata->anak_keberapa,
                'dari_saudara' => $item->biodata->dari_saudara,
                'tinggal_bersama' => $item->biodata->tinggal_bersama,
                'jenjang_pendidikan_terakhir' => $item->biodata->jenjang_pendidikan_terakhir,
                'nama_pendidikan_terakhir' => $item->biodata->nama_pendidikan_terakhir,
                'no_telepon' => $item->biodata->no_telepon,
                'no_telepon_2' => $item->biodata->no_telepon_2,
                'email' => $item->biodata->email,
                'pekerjaan' => $item->pekerjaan,
                'penghasilan' => $item->penghasilan,
                'negara_id' => $item->biodata->negara_id,
                'provinsi_id' => $item->biodata->provinsi_id,
                'kabupaten_id' => $item->biodata->kabupaten_id,
                'kecamatan_id' => $item->biodata->kecamatan_id,
                'jalan' => $item->biodata->jalan,
                'kode_pos' => $item->biodata->kode_pos,
                'wafat' => (bool) $item->biodata->wafat,
            ]),
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data): array {
            try {
                // Definisi daftar hubungan berdasarkan gender
                $hubunganLakiLaki = ['ayah kandung', 'kakek kandung', 'paman', 'ayah sambung'];
                $hubunganPerempuan = ['ibu kandung', 'nenek kandung', 'bibi', 'ibu sambung'];

                // Validasi jenis kelamin vs hubungan keluarga
                $hubungan = HubunganKeluarga::find($data['id_hubungan_keluarga']);
                $namaHubungan = strtolower($hubungan->nama_status ?? '');

                if ($hubungan) {
                    if (
                        in_array($namaHubungan, $hubunganLakiLaki) &&
                        strtolower($data['jenis_kelamin']) !== 'l'
                    ) {
                        return ['status' => false, 'message' => 'Jenis kelamin tidak sesuai dengan hubungan keluarga'];
                    }

                    if (
                        in_array($namaHubungan, $hubunganPerempuan) &&
                        strtolower($data['jenis_kelamin']) !== 'p'
                    ) {
                        return ['status' => false, 'message' => 'Jenis kelamin tidak sesuai dengan hubungan keluarga'];
                    }

                    // Validasi hanya satu ayah/ibu kandung per no_kk
                    if (!empty($data['no_kk']) && in_array($namaHubungan, ['ayah kandung', 'ibu kandung'])) {
                        $sudahAda = OrangTuaWali::whereHas('biodata.keluarga', function ($query) use ($data) {
                            $query->where('no_kk', $data['no_kk']);
                        })->whereHas('hubunganKeluarga', function ($query) use ($namaHubungan) {
                            $query->whereRaw('LOWER(nama_status) = ?', [$namaHubungan]);
                        })->exists();

                        if ($sudahAda) {
                            return [
                                'status' => false,
                                'message' => ucfirst($namaHubungan) . ' sudah terdaftar dalam keluarga ini'
                            ];
                        }
                    }
                }

                // Validasi No KK jika ada
                if (!empty($data['no_kk'])) {
                    $kkExists = Keluarga::where('no_kk', $data['no_kk'])->exists();
                    if (!$kkExists) {
                        return ['status' => false, 'message' => 'Nomor KK tidak cocok dengan keluarga manapun'];
                    }
                }

                // Validasi minimal identitas
                if (empty($data['nik']) && empty($data['no_kk']) && empty($data['no_passport'])) {
                    return ['status' => false, 'message' => 'Minimal salah satu dari NIK, No KK, atau No Passport harus diisi'];
                }

                // Validasi unik NIK
                if (!empty($data['nik']) && Biodata::where('nik', $data['nik'])->exists()) {
                    return ['status' => false, 'message' => 'NIK sudah terdaftar'];
                }

                // Buat Biodata
                $biodata = Biodata::create([
                    'id' => Str::uuid(),
                    'negara_id' => $data['negara_id'],
                    'provinsi_id' => $data['provinsi_id'],
                    'kabupaten_id' => $data['kabupaten_id'],
                    'kecamatan_id' => $data['kecamatan_id'],
                    'jalan' => $data['jalan'],
                    'kode_pos' => $data['kode_pos'],
                    'nama' => $data['nama'],
                    'no_passport' => $data['no_passport'] ?? null,
                    'tanggal_lahir' => Carbon::parse($data['tanggal_lahir']),
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'tempat_lahir' => $data['tempat_lahir'],
                    'nik' => $data['nik'],
                    'no_telepon' => $data['no_telepon'],
                    'no_telepon_2' => $data['no_telepon_2'],
                    'email' => $data['email'],
                    'jenjang_pendidikan_terakhir' => $data['jenjang_pendidikan_terakhir'],
                    'nama_pendidikan_terakhir' => $data['nama_pendidikan_terakhir'],
                    'anak_keberapa' => $data['anak_keberapa'],
                    'dari_saudara' => $data['dari_saudara'],
                    'status' => true,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                ]);

                // Isi no_kk dari id_biodata jika kosong
                if (empty($data['no_kk']) && !empty($data['id_biodata'])) {
                    $noKK = Keluarga::where('id_biodata', $data['id_biodata'])->value('no_kk');
                    if ($noKK) {
                        $data['no_kk'] = $noKK;
                    }
                }

                // Tambah ke keluarga jika ada no_kk
                if (!empty($data['no_kk'])) {
                    Keluarga::create([
                        'id_biodata' => $biodata->id,
                        'no_kk' => $data['no_kk'],
                        'created_by' => Auth::id(),
                        'status' => true,
                        'created_at' => now(),
                    ]);
                }

                // Nonaktifkan wali sebelumnya jika perlu
                if (!empty($data['wali']) && $data['wali']) {
                    OrangTuaWali::whereHas('biodata.keluarga', function ($q) use ($data) {
                        $q->where('no_kk', $data['no_kk']);
                    })->where('wali', true)->update(['wali' => false]);
                }

                // Buat data Orang Tua / Wali
                $ortu = OrangTuaWali::create([
                    'id_biodata' => $biodata->id,
                    'id_hubungan_keluarga' => $data['id_hubungan_keluarga'] ?? null,
                    'wali' => $data['wali'] ?? false,
                    'pekerjaan' => $data['pekerjaan'] ?? null,
                    'penghasilan' => $data['penghasilan'] ?? null,
                    'status' => true,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                activity('ortu_create')
                    ->performedOn($ortu)
                    ->withProperties(['new' => $ortu->getAttributes()])
                    ->event('create_ortu')
                    ->log('Data orang tua baru disimpan');

                return ['status' => true, 'data' => $ortu];
            } catch (\Exception $e) {
                Log::error('Error creating orangtua: ' . $e->getMessage());
                throw $e;
            }
        });
    }


    public function show(int $id): array
    {
        $ortu = OrangtuaWali::with(['biodata', 'keluarga', 'hubunganKeluarga'])->find($id);

        if (! $ortu) {
            return ['status' => false, 'message' => 'Data tidak ditemukan.'];
        }

        return ['status' => true, 'data' => [
            'id' => $ortu->id,
            'biodata_id' => $ortu->biodata->id,
            'no_passport' => $ortu->biodata->no_passport,
            'no_kk' => optional($ortu->keluarga)->first()->no_kk,
            'nik' => $ortu->biodata->nik,
            'nama' => $ortu->biodata->nama,
            'jenis_kelamin' => $ortu->biodata->jenis_kelamin ?? null,
            'tanggal_lahir' => $ortu->biodata->tanggal_lahir
                ? Carbon::parse($ortu->biodata->tanggal_lahir)->format('Y-m-d')
                : null,
            'tempat_lahir' => $ortu->biodata->tempat_lahir,
            'anak_keberapa' => $ortu->biodata->anak_keberapa,
            'dari_saudara' => $ortu->biodata->dari_saudara,
            'tinggal_bersama' => $ortu->biodata->tinggal_bersama,
            'jenjang_pendidikan_terakhir' => $ortu->biodata->jenjang_pendidikan_terakhir,
            'nama_pendidikan_terakhir' => $ortu->biodata->nama_pendidikan_terakhir,
            'no_telepon' => $ortu->biodata->no_telepon,
            'no_telepon_2' => $ortu->biodata->no_telepon_2,
            'email' => $ortu->biodata->email,
            'pekerjaan' => $ortu->pekerjaan,
            'penghasilan' => $ortu->penghasilan,
            'negara_id' => $ortu->biodata->negara_id,
            'provinsi_id' => $ortu->biodata->provinsi_id,
            'kabupaten_id' => $ortu->biodata->kabupaten_id,
            'kecamatan_id' => $ortu->biodata->kecamatan_id,
            'jalan' => $ortu->biodata->jalan,
            'kode_pos' => $ortu->biodata->kode_pos,
            'wafat' => (bool) $ortu->biodata->wafat,
        ]];
    }

    public function edit(string $id)
    {
        $ortu = OrangTuaWali::find($id);

        if (! $ortu) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $ortu];
    }

    public function update(array $data, string $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $ortu = OrangTuaWali::find($id);
            if (!$ortu) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            $biodata = $ortu->biodata;

            // Ambil semua hubungan keluarga yang hanya boleh untuk laki-laki
            $hubunganLakiLaki = ['ayah kandung', 'kakek kandung', 'paman', 'ayah sambung'];
            $hubunganPerempuan = ['ibu kandung', 'nenek kandung', 'bibi', 'ibu sambung'];

            // Ambil hubungan keluarga
            $hubungan = HubunganKeluarga::find($data['id_hubungan_keluarga']);
            if ($hubungan) {
                $namaHubungan = strtolower($hubungan->nama_status);

                if (in_array($namaHubungan, $hubunganLakiLaki) && strtolower($data['jenis_kelamin']) !== 'l') {
                    return ['status' => false, 'message' => 'Jenis kelamin tidak sesuai dengan hubungan keluarga'];
                }

                if (in_array($namaHubungan, $hubunganPerempuan) && strtolower($data['jenis_kelamin']) !== 'p') {
                    return ['status' => false, 'message' => 'Jenis kelamin tidak sesuai dengan hubungan keluarga'];
                }

                // Cek duplikasi ayah kandung / ibu kandung di keluarga
                if (!empty($data['no_kk']) && in_array($namaHubungan, ['ayah kandung', 'ibu kandung'])) {
                    $sudahAda = OrangTuaWali::whereHas('biodata.keluarga', function ($query) use ($data) {
                        $query->where('no_kk', $data['no_kk']);
                    })->whereHas('hubungan', function ($query) use ($namaHubungan) {
                        $query->whereRaw('LOWER(nama_status) = ?', [$namaHubungan]);
                    })->where('id', '!=', $id)->exists();

                    if ($sudahAda) {
                        return ['status' => false, 'message' => ucfirst($namaHubungan) . ' sudah terdaftar dalam keluarga ini'];
                    }
                }
            }

            if (!empty($data['no_kk'])) {
                $kkExists = Keluarga::where('no_kk', $data['no_kk'])->exists();
                if (!$kkExists) {
                    return ['status' => false, 'message' => 'Nomor KK tidak cocok dengan keluarga manapun'];
                }
            }

            if (empty($data['nik']) && empty($data['no_kk']) && empty($data['no_passport'])) {
                return ['status' => false, 'message' => 'Minimal salah satu dari NIK, No KK, atau No Passport harus diisi'];
            }

            if (!empty($data['nik']) && $data['nik'] !== $biodata->nik) {
                $nikExists = Biodata::where('nik', $data['nik'])->exists();
                if ($nikExists) {
                    return ['status' => false, 'message' => 'NIK sudah terdaftar'];
                }
            }

            $before = $ortu->getOriginal();
            $beforeBiodata = $biodata->getOriginal();

            // Update Biodata
            $biodata->update([
                'negara_id' => $data['negara_id'],
                'provinsi_id' => $data['provinsi_id'] ?? null,
                'kabupaten_id' => $data['kabupaten_id'] ?? null,
                'kecamatan_id' => $data['kecamatan_id'] ?? null,
                'jalan' => $data['jalan'] ?? null,
                'kode_pos' => $data['kode_pos'] ?? null,
                'nama' => $data['nama'],
                'no_passport' => $data['no_passport'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'tempat_lahir' => $data['tempat_lahir'],
                'nik' => $data['nik'] ?? null,
                'no_telepon' => $data['no_telepon'],
                'no_telepon_2' => $data['no_telepon_2'] ?? null,
                'email' => $data['email'],
                'jenjang_pendidikan_terakhir' => $data['jenjang_pendidikan_terakhir'] ?? null,
                'nama_pendidikan_terakhir' => $data['nama_pendidikan_terakhir'] ?? null,
                'anak_keberapa' => $data['anak_keberapa'] ?? null,
                'dari_saudara' => $data['dari_saudara'] ?? null,
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            // Update KK
            if (!empty($data['no_kk'])) {
                Keluarga::updateOrCreate(
                    ['id_biodata' => $biodata->id],
                    [
                        'no_kk' => $data['no_kk'],
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]
                );
            }

            // Jika dia sekarang jadi wali, nonaktifkan wali lainnya di KK yang sama
            if (!empty($data['wali']) && $data['wali']) {
                OrangTuaWali::whereHas('biodata.keluarga', function ($q) use ($data) {
                    $q->where('no_kk', $data['no_kk']);
                })->where('wali', true)->where('id', '!=', $id)->update(['wali' => false]);
            }

            // Update OrangTuaWali
            $ortu->update([
                'id_hubungan_keluarga' => $data['id_hubungan_keluarga'],
                'wali' => $data['wali'] ?? false,
                'pekerjaan' => $data['pekerjaan'] ?? null,
                'penghasilan' => $data['penghasilan'] ?? null,
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            $batchUuid = Str::uuid();

            activity('ortu_update')
                ->performedOn($ortu)
                ->withProperties([
                    'before_ortu' => $before,
                    'before_biodata' => $beforeBiodata,
                    'after_ortu' => $ortu->getChanges(),
                    'after_biodata' => $biodata->getChanges()
                ])
                ->tap(fn($activity) => $activity->batch_uuid = $batchUuid)
                ->event('update_ortu')
                ->log('Data orang tua diperbarui');

            return ['status' => true, 'data' => $ortu];
        });
    }

    public function destroy(string $id)
    {
        return DB::transaction(function () use ($id) {
            $ortu = OrangTuaWali::find($id);

            if (! $ortu) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            $ortu->delete();

            $batchUuid = Str::uuid();

            activity('ortu_delete')
                ->performedOn($ortu)
                ->withProperties(['deleted' => $ortu])
                ->tap(fn($activity) => $activity->batch_uuid = $batchUuid)
                ->event('delete_ortu')
                ->log('Data orang tua dihapus');

            return ['status' => true, 'message' => 'Data berhasil dihapus'];
        });
    }
}
