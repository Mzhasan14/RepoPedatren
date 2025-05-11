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
use Illuminate\Http\JsonResponse;
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
            ->join('keluarga AS kel', 'b.id', '=', 'kel.id_biodata') //dari orangtua ke tabel keluarga
            ->join('keluarga as ka', 'kel.no_kk', '=', 'ka.no_kk') //dari keluarga ke keluarga lainnya
            ->join('biodata as ba', 'ka.id_biodata', '=', 'ba.id') //dari keluarga ke anak
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            // hanya yang berstatus aktif
            ->where(fn($q) => $q->where('o.status', true))
            ->select([
                'o.id',
                DB::raw("COALESCE(b.nik, b.no_passport) AS identitas"),
                'b.nama',
                'b.no_telepon AS telepon_1',
                'b.no_telepon_2 AS telepon_2',
                'kb.nama_kabupaten AS kota_asal',
                'o.created_at',
                // ambil updated_at terbaru antar s, rp, rd
                DB::raw("
                        GREATEST(
                            o.updated_at,
                            hk.updated_at,
                            kel.updated_at
                        ) AS updated_at
                    "),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ])
            ->groupBy([
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
                'br.file_path'
            ])
            ->orderBy('o.id');
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            "id" => $item->id,
            "nik" => $item->identitas,
            "nama" => $item->nama,
            "telepon_1" => $item->telepon_1,
            "telepon_2" => $item->telepon_2,
            "kota_asal" => $item->kota_asal,
            "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            "foto_profil" => url($item->foto_profil)
        ]);
    }
    
    public function index(?string $idBiodata = null)
    {
        $query = OrangTuaWali::with(['hubunganKeluarga', 'biodata']);

        if ($idBiodata) {
            $query->where('id_biodata', $idBiodata);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        return [
            'status' => true,
            'data' => $data
        ];
    }

    // public function store(array $data)
    // {
    //     return DB::transaction(function () use ($data) {
    //         if (!isset($data['id_biodata'], $data['id_hubungan_keluarga']) || !Auth::id()) {
    //             return [
    //                 'status' => false,
    //                 'message' => 'Data tidak lengkap atau pengguna tidak terautentikasi',
    //                 'data' => null
    //             ];
    //         }

    //         $ortu = OrangTuaWali::create([
    //             'id_biodata' => $data['id_biodata'],
    //             'id_hubungan_keluarga' => $data['id_hubungan_keluarga'],
    //             'wali' => $data['wali'] ?? false,
    //             'pekerjaan' => $data['pekerjaan'] ?? null,
    //             'penghasilan' => $data['penghasilan'] ?? null,
    //             'wafat' => $data['wafat'] ?? false,
    //             'status' => true,
    //             'created_by' => Auth::id(),
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);

    //         activity('ortu_create')
    //             ->performedOn($ortu)
    //             ->withProperties(['new' => $ortu->getAttributes()])
    //             ->event('create_ortu')
    //             ->log('Data orang tua baru disimpan');

    //         return ['status' => true, 'data' => $ortu];
    //     });
    // }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $negara = Negara::create([
                'nama_negara' => $data['negara'],
                'created_by' => Auth::id(), // Ganti dengan ID pengguna yang sesuai login
                'status' => true,
                'created_at' => Carbon::now(),
            ]);
            $provinsi = Provinsi::create([
                'negara_id' => $negara->id,
                'nama_provinsi' => $data['provinsi'],
                'created_by' => Auth::id(), // Ganti dengan ID pengguna yang sesuai login
                'status' => true,
                'created_at' => Carbon::now(),
            ]);
            $kabupaten = Kabupaten::create([
                'provinsi_id' => $provinsi->id,
                'nama_kabupaten' => $data['kabupaten'],
                'created_by' => Auth::id(), // Ganti dengan ID pengguna yang sesuai login
                'status' => true,
                'created_at' => Carbon::now(),
            ]);
            $kecamatan = Kecamatan::create([
                'kabupaten_id' => $kabupaten->id,
                'nama_kecamatan' => $data['kecamatan'],
                'created_by' => Auth::id(), // Ganti dengan ID pengguna yang sesuai login
                'status' => true,
                'created_at' => Carbon::now(),
            ]);
            //  Biodata
            $biodata = Biodata::create([
                'id' => Str::uuid(),
                'negara_id' => $negara->id,
                'provinsi_id' => $provinsi->id,
                'kabupaten_id' => $kabupaten->id,
                'kecamatan_id' => $kecamatan->id,
                'jalan' => $data['jalan'],
                'kode_pos' => $data['kode_pos'],
                'nama' => $data['nama'],
                'no_passport' => $data['no_passport'], // diperbaiki
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
                'wafat' => $data['wafat'],
                'created_by' => Auth::id(), // Ganti dengan ID pengguna yang sesuai login
                'created_at' => Carbon::now(),
            ]);

            if (!empty($data['no_kk'])) {
                Keluarga::create([
                    'id_biodata' => $biodata->id,
                    'no_kk' => $data['no_kk'],
                    'created_by' => Auth::id(), // Ganti dengan ID pengguna yang sesuai login
                    'status' => true,
                    'created_at' => now(),
                ]);
            }
            $ortu = OrangTuaWali::create([
                'id_biodata' => $biodata->id,
                'id_hubungan_keluarga' => $data['id_hubungan_keluarga'],
                'wali' => $data['wali'] ?? false,
                'pekerjaan' => $data['pekerjaan'] ?? null,
                'penghasilan' => $data['penghasilan'] ?? null,
                'wafat' => $data['wafat'] ?? false,
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

            DB::commit();

            return ['status' => true, 'data' => $ortu];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating orangtua: ' . $e->getMessage());
            throw $e;  // Melemparkan exception agar ditangani di controller
        }
    }

    public function edit(string $id)
    {
        $ortu = OrangTuaWali::find($id);

        if (!$ortu) {
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

            $updateData = [
                'negara_id'                   => $data['negara_id'],
                'provinsi_id'                 => $data['provinsi_id'] ?? null,
                'kabupaten_id'                => $data['kabupaten_id'] ?? null,
                'kecamatan_id'                => $data['kecamatan_id'] ?? null,
                'jalan'                       => $data['jalan'] ?? null,
                'kode_pos'                    => $data['kode_pos'] ?? null,
                'nama'                        => $data['nama'],
                'no_passport'                 => $data['no_passport'] ?? null,
                'jenis_kelamin'               => $data['jenis_kelamin'],
                'tanggal_lahir'               => $data['tanggal_lahir'],
                'tempat_lahir'                => $data['tempat_lahir'],
                'nik'                         => $data['nik'] ?? null,
                'no_telepon'                  => $data['no_telepon'],
                'no_telepon_2'                => $data['no_telepon_2'] ?? null,
                'email'                       => $data['email'],
                'jenjang_pendidikan_terakhir' => $data['jenjang_pendidikan_terakhir'] ?? null,
                'nama_pendidikan_terakhir'    => $data['nama_pendidikan_terakhir'] ?? null,
                'anak_keberapa'               => $data['anak_keberapa'] ?? null,
                'dari_saudara'                => $data['dari_saudara'] ?? null,
                'no_kk' => $data['no_kk'],
                'id_hubungan_keluarga' => $data['id_hubungan_keluarga'],
                'wali' => $data['wali'] ?? false,
                'pekerjaan' => $data['pekerjaan'] ?? null,
                'penghasilan' => $data['penghasilan'] ?? null,
                'wafat' => $data['wafat'] ?? false,
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ];

            $before = $ortu->getOriginal();

            $ortu->fill($updateData);

            if (!$ortu->isDirty()) {
                return ['status' => false, 'message' => 'Tidak ada perubahan'];
            }

            $ortu->save();

            $batchUuid = Str::uuid();

            activity('ortu_update')
                ->performedOn($ortu)
                ->withProperties(['before' => $before, 'after' => $ortu->getChanges()])
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

            if (!$ortu) {
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
