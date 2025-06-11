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
                    'foto_pencatat' => url($fotoPath),
                    'nama_pencatat' =>$namaPencatat,
                    'status' => 'Wali Asuh', 
                ];
            });

            return ['status' => true, 'data' => $afektif];
        }

        public function show($id):array
        {
            $afektif = Catatan_afektif::select(
                    'id',
                    'id_wali_asuh',
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
                // 1. Pencarian data
                $afektif = Catatan_afektif::find($id);
                if (! $afektif) {
                    return ['status' => false, 'message' => 'Data tidak ditemukan.'];
                }

                // 2. Larangan update jika sudah memiliki tanggal_selesai
                if (! is_null($afektif->tanggal_selesai)) {
                    return [
                        'status'  => false,
                        'message' => 'Catatan afektif ini telah memiliki tanggal selesai dan tidak dapat diubah lagi demi menjaga keakuratan histori.',
                    ];
                }

                // 3. Update data
                $afektif->update([
                    'id_wali_asuh' => $input['id_wali_asuh'] ?? null,
                    'kepedulian_nilai'         => $input['kepedulian_nilai'],
                    'kepedulian_tindak_lanjut' => $input['kepedulian_tindak_lanjut'],
                    'kebersihan_nilai'         => $input['kebersihan_nilai'],
                    'kebersihan_tindak_lanjut' => $input['kebersihan_tindak_lanjut'],
                    'akhlak_nilai'             => $input['akhlak_nilai'],
                    'akhlak_tindak_lanjut'     => $input['akhlak_tindak_lanjut'],
                    'tanggal_buat'             => Carbon::parse($input['tanggal_buat']),
                    'updated_by'               => Auth::id(),
                ]);

                // 4. Return hasil
                return [
                    'status' => true,
                    'data'   => $afektif,
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

                // 2. Cari santri berdasarkan biodata_id (tanpa cek status dulu)
                $santri = Santri::where('biodata_id', $bioId)
                    ->latest()
                    ->first();

                if (!$santri) {
                    return [
                        'status' => false,
                        'message' => 'Santri tidak ditemukan.'
                    ];
                }

                if ($santri->status !== 'aktif') {
                    return [
                        'status' => false,
                        'message' => 'Santri tersebut sudah tidak aktif lagi.'
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

        public function keluarAfektif(array $input, int $id): array
        {
            return DB::transaction(function () use ($input, $id) {
                $afektif = Catatan_afektif::find($id);
                if (! $afektif) {
                    return ['status' => false, 'message' => 'Data tidak ditemukan.'];
                }

                if ($afektif->tanggal_selesai) {
                    return [
                        'status' => false,
                        'message' => 'Data afektif sudah ditandai selesai/nonaktif.',
                    ];
                }

                $tglSelesai = Carbon::parse($input['tanggal_selesai'] ?? '');

                if ($tglSelesai->lt(Carbon::parse($afektif->tanggal_buat))) {
                    return [
                        'status' => false,
                        'message' => 'Tanggal selesai tidak boleh sebelum tanggal buat.',
                    ];
                }

                $afektif->update([
                    'status'          => 0,
                    'tanggal_selesai' => $tglSelesai,
                    'updated_by'      => Auth::id(),
                ]);

                return [
                    'status' => true,
                    'data'   => $afektif,
                ];
            });
        }


}