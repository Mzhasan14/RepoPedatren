<?php

namespace App\Services\Pegawai\Filters\Formulir;

use App\Models\Catatan_afektif;
use App\Models\Santri;
use Carbon\Carbon;
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

        public function show($id):array
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


        public function update(array $input, string $id): array
        {
            return DB::transaction(function () use ($input, $id) {
                $afektif = Catatan_afektif::find($id);
                if (!$afektif) {
                    return ['status' => false, 'message' => 'Data tidak ditemukan.'];
                }

                // Validasi tanggal selesai jika ada
                if (!empty($input['tanggal_selesai'])) {
                    $tanggalSelesai = Carbon::parse($input['tanggal_selesai']);
                    $tanggalBuat = Carbon::parse($input['tanggal_buat'] ?? $afektif->tanggal_buat);

                    if ($tanggalSelesai->lt($tanggalBuat)) {
                        return [
                            'status' => false,
                            'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk.',
                        ];
                    }
                }

                // Siapkan data update
                $updateData = [
                    'kepedulian_nilai' => $input['kepedulian_nilai'] ?? $afektif->kepedulian_nilai,
                    'kepedulian_tindak_lanjut' => $input['kepedulian_tindak_lanjut'] ?? $afektif->kepedulian_tindak_lanjut,
                    'kebersihan_nilai' => $input['kebersihan_nilai'] ?? $afektif->kebersihan_nilai,
                    'kebersihan_tindak_lanjut' => $input['kebersihan_tindak_lanjut'] ?? $afektif->kebersihan_tindak_lanjut,
                    'akhlak_nilai' => $input['akhlak_nilai'] ?? $afektif->akhlak_nilai,
                    'akhlak_tindak_lanjut' => $input['akhlak_tindak_lanjut'] ?? $afektif->akhlak_tindak_lanjut,
                    'tanggal_buat' => Carbon::parse($input['tanggal_buat'] ?? $afektif->tanggal_buat),
                    'updated_by' => Auth::id(),
                ];

                if (!empty($input['tanggal_selesai'])) {
                    $updateData['tanggal_selesai'] = Carbon::parse($input['tanggal_selesai']);
                    $updateData['status'] = 0;
                } else {
                    $updateData['tanggal_selesai'] = null;
                    $updateData['status'] = 1;
                }

                $afektif->update($updateData);

                return [
                    'status' => true,
                    'data' => $afektif,
                ];
            });
        }
        
        public function store(array $data, string $bioId): array
        {
            return DB::transaction(function () use ($data, $bioId) {
                // 1. Periksa apakah santri sudah memiliki catatan afektif aktif
                $existing = Catatan_afektif::whereHas('santri', fn($q) =>
                        $q->where('biodata_id', $bioId)
                    )
                    ->whereNull('tanggal_selesai')
                    ->where('status', 1)
                    ->first();

                if ($existing) {
                    return [
                        'status' => false,
                        'message' => 'Santri masih memiliki Catatan Afektif aktif'
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

                // 3. Buat Catatan Afektif Baru
                $afektif = Catatan_afektif::create([
                    'id_santri' => $santri->id,
                    'id_wali_asuh' => $data['id_wali_asuh'],
                    'kepedulian_nilai' => $data['kepedulian_nilai'],
                    'kepedulian_tindak_lanjut' => $data['kepedulian_tindak_lanjut'],
                    'kebersihan_nilai' => $data['kebersihan_nilai'],
                    'kebersihan_tindak_lanjut' => $data['kebersihan_tindak_lanjut'],
                    'akhlak_nilai' => $data['akhlak_nilai'],
                    'akhlak_tindak_lanjut' => $data['akhlak_tindak_lanjut'],
                    'tanggal_buat' => $data['tanggal_buat'] ?? now(),
                    'status' => 1, // aktif
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return [
                    'status' => true,
                    'data' => $afektif->fresh()
                ];
            });
        }


}