<?php 

namespace App\Services\Pegawai\Filters\Formulir;

use App\Models\Catatan_kognitif;
use App\Models\Santri;
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
        )->find($id);
        
        if (!$kognitif) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $kognitif];
    }

    public function update(array $data, string $id)
    {
        return DB::transaction(function () use($data, $id) {
            $kognitif = Catatan_kognitif::find($id);
            
            if (!$kognitif) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            if (!is_null($kognitif->tanggal_selesai)) {
                return ['status' => false, 'data' => 'Data riwayat tidak boleh diubah!!'];
            }

            // Cek perubahan nilai
            $isChanged = false;
            $fields = [
                'kebahasaan_nilai', 'kebahasaan_tindak_lanjut',
                'baca_kitab_kuning_nilai', 'baca_kitab_kuning_tindak_lanjut',
                'hafalan_tahfidz_nilai', 'hafalan_tahfidz_tindak_lanjut',
                'furudul_ainiyah_nilai', 'furudul_ainiyah_tindak_lanjut',
                'tulis_alquran_nilai', 'tulis_alquran_tindak_lanjut',
                'baca_alquran_nilai', 'baca_alquran_tindak_lanjut'
            ];

            foreach ($fields as $field) {
                if (isset($data[$field]) && $kognitif->$field != $data[$field]) {
                    $isChanged = true;
                    break;
                }
            }

            if ($isChanged) {
                return ['status' => false, 'message' => 'Perubahan nilai tidak diperbolehkan'];
            }

            if (!empty($data['tanggal_selesai'])) {
                if (strtotime($data['tanggal_selesai']) < strtotime($kognitif->tanggal_buat)) {
                    return ['status' => false, 'message' => 'Tanggal selesai tidak boleh lebih awal dari tanggal buat.'];
                }

                $kognitif->update([
                    'tanggal_selesai' => $data['tanggal_selesai'],
                    'status' => 0,
                    'updated_by' => Auth::id(),
                    'updated_at' => now()
                ]);

                return ['status' => true, 'data' => $kognitif];
            }

            return ['status' => false, 'message' => 'Tidak ada perubahan yang diizinkan selain tanggal selesai'];     
        });
    }

    public function store(array $data, string $bioId)
    {
        return DB::transaction(function () use ($data, $bioId) {
            // 1. Ambil santri dari biodata_id
            $santri = Santri::where('biodata_id', $bioId)->latest()->first();

            if (!$santri) {
                return ['status' => false, 'message' => 'Santri tidak ditemukan untuk biodata ini'];
            }

            // 2. Nonaktifkan catatan aktif yang ada
            Catatan_kognitif::where('id_santri', $santri->id)
                ->whereNull('tanggal_selesai')
                ->where('status', 1)
                ->update([
                    'status' => 0,
                    'tanggal_selesai' => now(),
                    'updated_by' => Auth::id(),
                ]);

            // 3. Buat Catatan Kognitif baru
            $kognitif = Catatan_kognitif::create([
                'id_santri' => $santri->id,
                'id_wali_asuh' => $data['id_wali_asuh'],
                'kebahasaan_nilai' => $data['kebahasaan_nilai'],
                'kebahasaan_tindak_lanjut' => $data['kebahasaan_tindak_lanjut'],
                'baca_kitab_kuning_nilai' => $data['baca_kitab_kuning_nilai'],
                'baca_kitab_kuning_tindak_lanjut' => $data['baca_kitab_kuning_tindak_lanjut'],
                'hafalan_tahfidz_nilai' => $data['hafalan_tahfidz_nilai'],
                'hafalan_tahfidz_tindak_lanjut' => $data['hafalan_tahfidz_tindak_lanjut'],
                'furudul_ainiyah_nilai' => $data['furudul_ainiyah_nilai'],
                'furudul_ainiyah_tindak_lanjut' => $data['furudul_ainiyah_tindak_lanjut'],
                'tulis_alquran_nilai' => $data['tulis_alquran_nilai'],
                'tulis_alquran_tindak_lanjut' => $data['tulis_alquran_tindak_lanjut'],
                'baca_alquran_nilai' => $data['baca_alquran_nilai'],
                'baca_alquran_tindak_lanjut' => $data['baca_alquran_tindak_lanjut'],
                'tanggal_buat' => $data['tanggal_buat'] ?? now(),
                'status' => 1, // Status aktif
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ['status' => true, 'data' => $kognitif];
        });
    }
}