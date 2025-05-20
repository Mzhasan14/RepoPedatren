<?php

namespace App\Services\Administrasi;

use App\Models\Santri;
use App\Models\Biodata;
use Illuminate\Http\Request;
use App\Models\PengunjungMahrom;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PengunjungMahromService
{

    public function getAllPengunjung(Request $request)
    {
        return DB::table('pengunjung_mahrom as pm')
            ->join('biodata as bp', 'pm.biodata_id', 'bp.id')
            ->join('santri as s', 'pm.santri_id', 's.id')
            ->join('biodata as bs', 's.biodata_id', 'bs.id')
            ->join('riwayat_domisili as rd', 'rd.santri_id', 's.id')
            ->leftjoin('riwayat_pendidikan as rp', 'rp.santri_id', 's.id')
            ->leftJoin('lembaga AS l', 'rp.lembaga_id', 'l.id')
            ->leftJoin('jurusan AS j', 'rp.jurusan_id', 'j.id')
            ->join('wilayah AS w', 'rd.wilayah_id', 'w.id')
            ->leftjoin('blok AS bl', 'rd.blok_id', 'bl.id')
            ->leftjoin('kamar AS km', 'rd.kamar_id', 'km.id')
            ->select(
                'pm.id',
                'w.nama_wilayah',
                'bp.nama as nama_pengunjung',
                'bs.nama as nama_santri',
                'bl.nama_blok',
                'km.nama_kamar',
                'l.nama_lembaga',
                'j.nama_jurusan',
                'pm.jumlah_rombongan',
                'pm.tanggal_kunjungan'
            )
            ->orderBy('pm.created_at');
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'id'                => $item->id,
            'wilayah'       => $item->nama_wilayah,
            'nama_pengunjung'     => $item->nama_pengunjung,
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
            // Ambil santri berdasarkan ID
            $santri = Santri::find($data['santri_id']);

            if (!$santri) {
                return ['status' => false, 'message' => 'Santri tidak ditemukan'];
            }

            // Cek apakah pengunjung sudah memiliki biodata
            $biodata = Biodata::where('nik', $data['nik'])->first();

            if (!$biodata) {
                // Jika belum ada, buat biodata baru
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
    //  public function show($id): array
    // {
    //     $kunjungan = PengunjungMahrom::with('biodata')->find($id);

    //     if (!$kunjungan) {
    //         return ['status' => false, 'message' => 'Data tidak ditemukan'];
    //     }

    //     return [
    //         'status' => true,
    //         'data' => [
    //             'id' => $kunjungan->id,
    //             'biodata_id' => $kunjungan->biodata_id,
    //             'nama_pengunjung' => $kunjungan->biodata->nama ?? null,
    //             'santri_id' => $kunjungan->santri_id,
    //             'hubungan_id' => $kunjungan->hubungan_id,
    //             'jumlah_rombongan' => $kunjungan->jumlah_rombongan,
    //             'tanggal_kunjungan' => $kunjungan->tanggal_kunjungan,
    //             'status' => $kunjungan->status,
    //         ],
    //     ];
    // }
    //  public function index(string $bioId): array
    // {
    //     $kunjungan = PengunjungMahrom::with(['biodata', 'santri.biodata'])
    //         ->whereHas('santri.biodata', fn($q) => $q->where('id', $bioId))
    //         ->latest()
    //         ->get()
    //         ->map(fn($item) => [
    //             'id' => $item->id,
    //             'nama_pengunjung' => $item->biodata->nama ?? null,
    //             'santri_id' => $item->santri_id,
    //             'hubungan_id' => $item->hubungan_id,
    //             'jumlah_rombongan' => $item->jumlah_rombongan,
    //             'tanggal_kunjungan' => $item->tanggal_kunjungan,
    //             'status' => $item->status,
    //         ]);

    //     return ['status' => true, 'data' => $kunjungan];
    // }
}
