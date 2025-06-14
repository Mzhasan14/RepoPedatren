<?php

namespace App\Services\Administrasi;

use App\Models\Biodata;
use App\Models\HubunganKeluarga;
use App\Models\Keluarga;
use App\Models\OrangTuaWali;
use App\Models\PengunjungMahrom;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengunjungMahromService
{
    public function getAllPengunjung(Request $request)
    {
        return DB::table('pengunjung_mahrom as pm')
            ->join('hubungan_keluarga as hk', 'pm.hubungan_id', 'hk.id')
            ->join('biodata as bp', 'pm.biodata_id', 'bp.id')
            ->join('santri as s', 'pm.santri_id', 's.id')
            ->join('biodata as bs', 's.biodata_id', 'bs.id')
            ->join('domisili_santri as ds', 'ds.santri_id', 's.id')
            ->join('wilayah AS w', 'ds.wilayah_id', 'w.id')
            ->leftjoin('blok AS bl', 'ds.blok_id', 'bl.id')
            ->leftjoin('kamar AS km', 'ds.kamar_id', 'km.id')
            ->select(
                'pm.id',
                'w.nama_wilayah',
                'bp.nama as nama_pengunjung',
                'hk.nama_status',
                'bs.nama as nama_santri',
                'bl.nama_blok',
                'km.nama_kamar',
                'pm.jumlah_rombongan',
                'pm.tanggal_kunjungan'
            );
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn ($item) => [
            'id' => $item->id,
            'wilayah' => $item->nama_wilayah,
            'nama_pengunjung' => $item->nama_pengunjung,
            'status' => $item->nama_status,
            'santri_dikunjungi' => $item->nama_santri,
            'blok' => $item->nama_blok ?? '-',
            'kamar' => $item->nama_kamar ?? '-',
            'lembaga' => $item->nama_lembaga ?? '-',
            'jurusan' => $item->nama_jurusan ?? '-',
            'jumlah_rombongan' => $item->jumlah_rombongan ?? '-',
            'tanggal_kunjungan' => $item->tanggal_kunjungan ?? '-',
        ]);
    }

    public function store(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $santri = Santri::find($data['santri_id']);

            if (! $santri) {
                return ['status' => false, 'message' => 'Santri tidak ditemukan'];
            }

            // Ambil biodata pengunjung
            $biodata = Biodata::where('nik', $data['nik'])->first();

            if ($biodata) {
                // Ambil no_kk pengunjung
                $kkPengunjung = Keluarga::where('id_biodata', $biodata->id)->value('no_kk');

                if ($kkPengunjung) {
                    // Cek apakah santri berada dalam KK yang sama
                    $kkSantri = Keluarga::where('id_biodata', $santri->biodata_id)
                        ->where('no_kk', $kkPengunjung)
                        ->exists();

                    if ($kkSantri) {
                        // Ambil hubungan_id dan nama hubungan dari orang_tua_wali
                        $orangTuaWali = OrangTuaWali::where('id_biodata', $biodata->id)->first();

                        if ($orangTuaWali && $orangTuaWali->hubungan_id != $data['hubungan_id']) {
                            // Ambil nama hubungan yang tercatat
                            $hubunganTercatat = HubunganKeluarga::find($orangTuaWali->id_hubungan_keluarga)?->nama ?? 'tidak diketahui';

                            return [
                                'status' => false,
                                'message' => 'Hubungan tidak sesuai. Di sistem tercatat sebagai: '.$hubunganTercatat,
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

    public function show(string $id): array
    {
        $kunjungan = PengunjungMahrom::with([
            'biodata',
            'santri.biodata',
            'hubungan', // pastikan relasi ini ada di model
        ])->find($id);

        if (! $kunjungan) {
            return [
                'status' => false,
                'message' => 'Data kunjungan tidak ditemukan',
            ];
        }

        return [
            'status' => true,
            'data' => [
                'id' => $kunjungan->id,
                'santri_id' => $kunjungan->santri_id,
                'santri_nama' => $kunjungan->santri->biodata->nama ?? null,
                'nik' => $kunjungan->biodata->nik,
                'nama' => $kunjungan->biodata->nama,
                'tempat_lahir' => $kunjungan->biodata->tempat_lahir,
                'tanggal_lahir' => $kunjungan->biodata->tanggal_lahir,
                'jenis_kelamin' => $kunjungan->biodata->jenis_kelamin,
                'hubungan_id' => $kunjungan->hubungan_id ?? null,
                'jumlah_rombongan' => $kunjungan->jumlah_rombongan,
                'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
                'status' => $kunjungan->status,
            ],
        ];
    }

    public function update(array $data, string $id): array
    {
        return DB::transaction(function () use ($data, $id) {
            $kunjungan = PengunjungMahrom::find($id);

            if (! $kunjungan) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            if (in_array($kunjungan->status, ['selesai', 'ditolak'])) {
                return [
                    'status' => false,
                    'message' => 'Maaf, data dengan status "'.$kunjungan->status.'" tidak dapat diubah.',
                ];
            }

            // Cek atau buat biodata baru jika NIK berbeda
            $biodata = Biodata::where('nik', $data['nik'])->first();

            if (! $biodata) {
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
