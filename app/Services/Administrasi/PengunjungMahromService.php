<?php

namespace App\Services\Administrasi;

use App\Models\Santri;
use App\Models\Biodata;
use App\Models\Keluarga;
use App\Models\OrangTuaWali;
use Illuminate\Http\Request;
use App\Models\HubunganKeluarga;
use App\Models\PengunjungMahrom;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PengunjungMahromService
{

    public function getAllPengunjung(Request $request)
    {
        return DB::table('pengunjung_mahrom as pm')
            ->join('hubungan_keluarga as hk', 'pm.hubungan_id', 'hk.id')
            ->join('biodata as bp', 'pm.biodata_id', 'bp.id')
            ->join('santri as s', 'pm.santri_id', 's.id')
            ->join('biodata as bs', 's.biodata_id', 'bs.id')
            ->join('riwayat_domisili as rd', 'rd.santri_id', 's.id')
            ->leftjoin('riwayat_pendidikan as rp', 'rp.biodata_id', 'bs.id')
            ->leftJoin('lembaga AS l', 'rp.lembaga_id', 'l.id')
            ->leftJoin('jurusan AS j', 'rp.jurusan_id', 'j.id')
            ->join('wilayah AS w', 'rd.wilayah_id', 'w.id')
            ->leftjoin('blok AS bl', 'rd.blok_id', 'bl.id')
            ->leftjoin('kamar AS km', 'rd.kamar_id', 'km.id')
            ->select(
                'pm.id',
                'w.nama_wilayah',
                'bp.nama as nama_pengunjung',
                'hk.nama_status',
                'bs.nama as nama_santri',
                'bl.nama_blok',
                'km.nama_kamar',
                'l.nama_lembaga',
                'j.nama_jurusan',
                'pm.jumlah_rombongan',
                'pm.tanggal_kunjungan'
            )
            ->orderBy('pm.created_at', 'desc');
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'id'                => $item->id,
            'wilayah'       => $item->nama_wilayah,
            'nama_pengunjung'     => $item->nama_pengunjung,
            'status'     => $item->nama_status,
            'santri_dikunjungi'           => $item->nama_santri,
            'blok'         => $item->nama_blok ?? '-',
            'kamar'        => $item->nama_kamar ?? '-',
            'lembaga'      => $item->nama_lembaga ?? '-',
            'jurusan'      => $item->nama_jurusan ?? '-',
            'jumlah_rombongan'        => $item->jumlah_rombongan ?? '-',
            'tanggal_kunjungan'       => $item->tanggal_kunjungan ?? '-'
        ]);
    }


    public function store(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $santri = Santri::find($data['santri_id']);

            if (!$santri) {
                return ['status' => false, 'message' => 'Santri tidak ditemukan'];
            }

            // Ambil biodata pengunjung
            $biodata = Biodata::where('nik', $data['nik'])->first();

            if ($biodata) {
                // Ambil no_kk pengunjung
                $kkPengunjung = Keluarga::where('biodata_id', $biodata->id)->value('no_kk');

                if ($kkPengunjung) {
                    // Cek apakah santri berada dalam KK yang sama
                    $kkSantri = Keluarga::where('biodata_id', $santri->biodata_id)
                        ->where('no_kk', $kkPengunjung)
                        ->exists();

                    if ($kkSantri) {
                        // Ambil hubungan_id dan nama hubungan dari orang_tua_wali
                        $orangTuaWali = OrangTuaWali::where('biodata_id', $biodata->id)->first();

                        if ($orangTuaWali && $orangTuaWali->hubungan_id != $data['hubungan_id']) {
                            // Ambil nama hubungan yang tercatat
                            $hubunganTercatat = HubunganKeluarga::find($orangTuaWali->id_hubungan_keluarga)?->nama ?? 'tidak diketahui';

                            return [
                                'status' => false,
                                'message' => 'Hubungan tidak sesuai. Di sistem tercatat sebagai: ' . $hubunganTercatat
                            ];
                        }
                    }
                }
            } else {
                // Jika belum ada biodata, buat
                $biodata = Biodata::create([
                    'nik' => $data['nik'],
                    'nama' => $data['nama'],
                    'tempat_lahir' => $data['tempat_lahir'],
                    'tanggal_lahir' => $data['tanggal_lahir'],
                    'jenis_kelamin' => $data['jenis_kelamin'],
                ]);
            }

            // Simpan data kunjungan
            $kunjungan = PengunjungMahrom::create([
                'biodata_id' => $biodata->id,
                'santri_id' => $santri->id,
                'hubungan_id' => $data['hubungan_id'],
                'jumlah_rombongan' => $data['jumlah_rombongan'],
                'tanggal_kunjungan' => $data['tanggal_kunjungan'],
                'status' => $data['status'],
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ['status' => true, 'data' => $kunjungan];
        });
    }


    public function update(array $data, string $id): array
    {
        return DB::transaction(function () use ($data, $id) {
            $kunjungan = PengunjungMahrom::find($id);

            if (!$kunjungan) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            if (in_array($kunjungan->status, ['selesai', 'ditolak'])) {
                return [
                    'status' => false,
                    'message' => 'Maaf, data dengan status "' . $kunjungan->status . '" tidak dapat diubah.'
                ];
            }

            // Cek atau buat biodata baru jika NIK berbeda
            $biodata = Biodata::where('nik', $data['nik'])->first();

            if (!$biodata) {
                $biodata = Biodata::create([
                    'nik' => $data['nik'],
                    'nama' => $data['nama'],
                    'tempat_lahir' => $data['tempat_lahir'],
                    'tanggal_lahir' => $data['tanggal_lahir'],
                    'jenis_kelamin' => $data['jenis_kelamin'],
                ]);
            }

            // Update data kunjungan
            $kunjungan->update([
                'biodata_id' => $biodata->id,
                'hubungan_id' => $data['hubungan_id'],
                'jumlah_rombongan' => $data['jumlah_rombongan'],
                'tanggal_kunjungan' => $data['tanggal_kunjungan'],
                'status' => $data['status'],
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            return ['status' => true, 'data' => $kunjungan];
        });
    }
}
