<?php 

namespace App\Services\Pegawai\Filters\Formulir;

use App\Models\Catatan_kognitif;
use App\Models\Santri;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CatatanKognitifService
{
     public function index(string $bioId): array
    {
        // Ambil ID jenis berkas "Pas foto"
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        // Ambil data kognitif dengan relasi lengkap
        $kognitif = Catatan_kognitif::whereHas('santri.biodata', function ($query) use ($bioId) {
                $query->where('id', $bioId);
            })
            ->with([
                'santri.biodata',
                'waliAsuh.santri.biodata.berkas' => function ($query) use ($pasFotoId) {
                    $query->where('jenis_berkas_id', $pasFotoId)
                        ->latest('id')
                        ->limit(1);
                }
            ])
            ->get()
            ->map(function ($item) {
                $pencatatBiodata = optional($item->waliAsuh?->santri?->biodata);
                $fotoPath = $pencatatBiodata?->berkas?->first()?->file_path ?? 'default.jpg';
                $namaPencatat = $pencatatBiodata?->nama ?? '-';

                return [
                    'id' => $item->id,
                    'kebahasaan_nilai' => $item->kebahasaan_nilai,
                    'kebahasaan_tindak_lanjut' => $item->kebahasaan_tindak_lanjut,
                    'baca_kitab_kuning_nilai' => $item->baca_kitab_kuning_nilai,
                    'baca_kitab_kuning_tindak_lanjut' => $item->baca_kitab_kuning_tindak_lanjut,
                    'hafalan_tahfidz_nilai' => $item->hafalan_tahfidz_nilai,
                    'hafalan_tahfidz_tindak_lanjut' => $item->hafalan_tahfidz_tindak_lanjut,
                    'furudul_ainiyah_nilai' => $item->furudul_ainiyah_nilai,
                    'furudul_ainiyah_tindak_lanjut' => $item->furudul_ainiyah_tindak_lanjut,
                    'tulis_alquran_nilai' => $item->tulis_alquran_nilai,
                    'tulis_alquran_tindak_lanjut' => $item->tulis_alquran_tindak_lanjut,
                    'baca_alquran_nilai' => $item->baca_alquran_nilai,
                    'baca_alquran_tindak_lanjut' => $item->baca_alquran_tindak_lanjut,
                    'tanggal_buat' => $item->tanggal_buat,
                    'tanggal_selesai' => $item->tanggal_selesai,
                    'foto_pencatat' => $fotoPath,
                    'nama_pencatat' => $namaPencatat,
                    'status' => 'Wali Asuh', 
                ];
            });

        return ['status' => true, 'data' => $kognitif];
    }

    public function edit($id): array
    {
        $kognitif = Catatan_kognitif::select(
                'id',
                'kebahasaan_nilai',
                'kebahasaan_tindak_lanjut',
                'baca_kitab_kuning_nilai',
                'baca_kitab_kuning_tindak_lanjut',
                'hafalan_tahfidz_nilai',
                'hafalan_tahfidz_tindak_lanjut',
                'furudul_ainiyah_nilai',
                'furudul_ainiyah_tindak_lanjut',
                'tulis_alquran_nilai',
                'tulis_alquran_tindak_lanjut',
                'baca_alquran_nilai',
                'baca_alquran_tindak_lanjut',
                'tanggal_buat',
                'tanggal_selesai',
                DB::raw("CASE WHEN status = 1 THEN 'aktif' ELSE 'tidak aktif' END AS status_aktif")
        )->find($id);
        
        if (!$kognitif) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $kognitif];
    }

    public function update(array $input, string $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            // 1. Pencarian data
            $kognitif = Catatan_kognitif::find($id);
            if (! $kognitif) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            // 2. Larangan update jika sudah memiliki tanggal_selesai
            if (! is_null($kognitif->tanggal_selesai)) {
                return [
                    'status'  => false,
                    'message' => 'Catatan kognitif ini telah memiliki tanggal selesai dan tidak dapat diubah lagi demi menjaga keakuratan histori.',
                ];
            }

            // 3. Update data
            $kognitif->update([
                'kebahasaan_nilai'                => $input['kebahasaan_nilai'],
                'kebahasaan_tindak_lanjut'       => $input['kebahasaan_tindak_lanjut'],
                'baca_kitab_kuning_nilai'        => $input['baca_kitab_kuning_nilai'],
                'baca_kitab_kuning_tindak_lanjut'=> $input['baca_kitab_kuning_tindak_lanjut'],
                'hafalan_tahfidz_nilai'          => $input['hafalan_tahfidz_nilai'],
                'hafalan_tahfidz_tindak_lanjut'  => $input['hafalan_tahfidz_tindak_lanjut'],
                'furudul_ainiyah_nilai'          => $input['furudul_ainiyah_nilai'],
                'furudul_ainiyah_tindak_lanjut'  => $input['furudul_ainiyah_tindak_lanjut'],
                'tulis_alquran_nilai'             => $input['tulis_alquran_nilai'],
                'tulis_alquran_tindak_lanjut'     => $input['tulis_alquran_tindak_lanjut'],
                'baca_alquran_nilai'              => $input['baca_alquran_nilai'],
                'baca_alquran_tindak_lanjut'      => $input['baca_alquran_tindak_lanjut'],
                'tanggal_buat'                    => Carbon::parse($input['tanggal_buat']),
                'updated_by'                     => Auth::id(),
            ]);

            // 4. Return hasil
            return [
                'status' => true,
                'data'   => $kognitif,
            ];
        });
    }


    public function keluarKognitif(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $kognitif = Catatan_kognitif::find($id);
            if (! $kognitif) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if ($kognitif->tanggal_selesai) {
                return [
                    'status'  => false,
                    'message' => 'Data kognitif sudah ditandai selesai/nonaktif.',
                ];
            }

            $tglSelesai = Carbon::parse($input['tanggal_selesai'] ?? '');

            if ($tglSelesai->lt(Carbon::parse($kognitif->tanggal_buat))) {
                return [
                    'status'  => false,
                    'message' => 'Tanggal selesai tidak boleh sebelum tanggal buat.',
                ];
            }

            $kognitif->update([
                'status'          => 0,
                'tanggal_selesai' => $tglSelesai,
                'updated_by'      => Auth::id(),
            ]);

            return [
                'status' => true,
                'data'   => $kognitif,
            ];
        });
    }


    public function store(array $data, string $bioId): array
    {
        return DB::transaction(function () use ($data, $bioId) {
            // 1. Cek apakah santri sudah memiliki catatan kognitif aktif
            $existing = Catatan_kognitif::whereHas('santri', fn($q) =>
                    $q->where('biodata_id', $bioId)
                )
                ->whereNull('tanggal_selesai')
                ->where('status', 1)
                ->first();

            if ($existing) {
                return [
                    'status' => false,
                    'message' => 'Santri masih memiliki Catatan Kognitif aktif'
                ];
            }

            // 2. Cari Santri berdasarkan biodata_id
            $santri = Santri::where('biodata_id', $bioId)
                ->latest()
                ->first();

            if (!$santri) {
                return [
                    'status' => false,
                    'message' => 'Santri tidak ditemukan untuk biodata ini'
                ];
            }

            // 3. Buat Catatan Kognitif baru
            $kognitif = Catatan_kognitif::create([
                'id_santri' => $santri->id,
                'id_wali_asuh' => $data['id_wali_asuh'] ?? null,
                'kebahasaan_nilai' => $data['kebahasaan_nilai'] ?? null,
                'kebahasaan_tindak_lanjut' => $data['kebahasaan_tindak_lanjut'] ?? null,
                'baca_kitab_kuning_nilai' => $data['baca_kitab_kuning_nilai'] ?? null,
                'baca_kitab_kuning_tindak_lanjut' => $data['baca_kitab_kuning_tindak_lanjut'] ?? null,
                'hafalan_tahfidz_nilai' => $data['hafalan_tahfidz_nilai'] ?? null,
                'hafalan_tahfidz_tindak_lanjut' => $data['hafalan_tahfidz_tindak_lanjut'] ?? null,
                'furudul_ainiyah_nilai' => $data['furudul_ainiyah_nilai'] ?? null,
                'furudul_ainiyah_tindak_lanjut' => $data['furudul_ainiyah_tindak_lanjut'] ?? null,
                'tulis_alquran_nilai' => $data['tulis_alquran_nilai'] ?? null,
                'tulis_alquran_tindak_lanjut' => $data['tulis_alquran_tindak_lanjut'] ?? null,
                'baca_alquran_nilai' => $data['baca_alquran_nilai'] ?? null,
                'baca_alquran_tindak_lanjut' => $data['baca_alquran_tindak_lanjut'] ?? null,
                'tanggal_buat' => $data['tanggal_buat'] ?? now(),
                'status' => 1, // aktif
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'status' => true,
                'data' => $kognitif->fresh()
            ];
        });
    }

}