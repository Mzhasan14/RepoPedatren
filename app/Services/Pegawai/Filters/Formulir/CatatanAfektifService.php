<?php

namespace App\Services\Pegawai\Filters\Formulir;

use App\Models\Catatan_afektif;
use App\Models\Santri;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CatatanAfektifService
{
        public function index(string $bioId): array
        {
            // Ambil ID jenis berkas "Pas foto"
            $pasFotoId = DB::table('jenis_berkas')
                ->where('nama_jenis_berkas', 'Pas foto')
                ->value('id');

            // Ambil data afektif dengan relasi lengkap
            $afektif = Catatan_afektif::whereHas('santri.biodata', function ($query) use ($bioId) {
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
                    'kepedulian_nilai' => $item->kepedulian_nilai,
                    'kepedulian_tindak_lanjut' => $item->kepedulian_tindak_lanjut,
                    'kebersihan_nilai' => $item->kebersihan_nilai,
                    'kebersihan_tindak_lanjut' => $item->kebersihan_tindak_lanjut,
                    'akhlak_nilai' => $item->akhlak_nilai,
                    'akhlak_tindak_lanjut' => $item->akhlak_tindak_lanjut,
                    'tanggal_buat' => $item->tanggal_buat,
                    'tanggal_selesai' => $item->tanggal_selesai,
                    'foto_pencatat' => $fotoPath,
                    'nama_pencatat' => $namaPencatat,
                    'status' => 'Wali Asuh', 
                ];
            });

            return ['status' => true, 'data' => $afektif];
        }

        public function edit($id):array
        {
            $afektif = Catatan_afektif::select(
                    'id',
                    'kepedulian_nilai',
                    'kepedulian_tindak_lanjut',
                    'kebersihan_nilai',
                    'kebersihan_tindak_lanjut',
                    'akhlak_nilai',
                    'akhlak_tindak_lanjut',
                    'tanggal_buat',
                    'tanggal_selesai',
            )->find($id);
            if (!$afektif){
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            return ['status' => true, 'data' => $afektif];
        }

        public function update(array $data, string $id)
        {
            return DB::transaction(function () use($data, $id){
                $afektif = Catatan_afektif::find($id);
                
                if (!$afektif){
                    return ['status' =>false, 'message' => 'Data tidak ditemukan'];
                }

                if (!is_null($afektif->tanggal_selesai)){
                    return ['status' => false, 'data' => 'Data riwayat tidak boleh diubah!!'];
                }

                // cek perubahan
                $iskepedulianChanged = isset($data['kepedulian_nilai']) && $afektif->kepedulian_nilai != $data['kepedulian_nilai'];
                $iskepedulianLanjutChanged = isset($data['kepedulian_tindak_lanjut']) && $afektif->kepedulian_nilai != $data['kepedulian_tindak_lanjut'];
                $iskebersihanChanged = isset($data['kebersihan_nilai']) && $afektif->kebersihan_nilai != $data['kebersihan_nilai'];
                $iskebersihanLanjutChanged = isset($data['kebersihan_tindak_lanjut']) && $afektif->kebersihan_tindak_lanjut != $data['kebersihan_tindak_lanjut'];
                $isAkhlaknChanged = isset($data['akhlak_nilai']) && $afektif->akhlak_nilai != $data['akhlak_nilai'];
                $isAkhlakLanjutChanged = isset($data['akhlak_tindak_lanjut']) && $afektif->akhlak_tindak_lanjut != $data['akhlak_tindak_lanjut'];

                if ($iskepedulianChanged || $iskepedulianLanjutChanged || $iskebersihanChanged || $iskebersihanLanjutChanged || $isAkhlaknChanged || $isAkhlakLanjutChanged) {
                    return ['status' => false, 'message' => 'Perubahan Kepedulian, Kebersihan, atau Akhlak tidak diperbolehkan'];
                }

                if (!empty($data['tanggal_selesai'])) {
                    if (strtotime($data['tanggal_selesai']) < strtotime($afektif->tanggal_buat)) {
                        return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk.'];
                    }

                    $afektif->update([
                        'tanggal_selesai' => $data['tanggal_selesai'],
                        'status' => 0,
                        'updated_by' => Auth::id(),
                        'updated_at' => now()
                    ]);

                    return ['status' => true, 'data' => $afektif];
                }
            return ['status' => false, 'message' => 'Tidak ada perubahan yang diizinkan selain tanggal keluar'];     
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
                Catatan_afektif::where('id_santri', $santri->id)
                    ->whereNull('tanggal_selesai')
                    ->where('status', 1)
                    ->update([
                        'status' => 0,
                        'tanggal_selesai' => now(),
                        'updated_by' => Auth::id(),
                    ]);

                // 4. Buat Catatan Afektif baru
                $afektif = Catatan_afektif::create([
                    'id_santri'                  => $santri->id,
                    'id_wali_asuh'               => $data['id_wali_asuh'],
                    'kepedulian_nilai'           => $data['kepedulian_nilai'],
                    'kepedulian_tindak_lanjut'   => $data['kepedulian_tindak_lanjut'],
                    'kebersihan_nilai'           => $data['kebersihan_nilai'],
                    'kebersihan_tindak_lanjut'   => $data['kebersihan_tindak_lanjut'],
                    'akhlak_nilai'               => $data['akhlak_nilai'],
                    'akhlak_tindak_lanjut'       => $data['akhlak_tindak_lanjut'],
                    'tanggal_buat'               => $data['tanggal_buat'] ?? now(),
                    'status'                     => 1, // Status aktif
                    'created_by'                 => Auth::id(),
                    'created_at'                 => now(),
                    'updated_at'                 => now(),
                ]);

                return ['status' => true, 'data' => $afektif];
            });
        }

}