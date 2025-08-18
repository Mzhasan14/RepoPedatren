<?php

namespace App\Services\Pegawai\Filters\Formulir;

use App\Models\Catatan_kognitif;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\Santri;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatatanKognitifService
{
    public function index(string $bioId): array
    {
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $kognitif = Catatan_kognitif::with([
            'santri.biodata',
            'waliAsuh.santri.biodata.berkas' => fn($q) => $q
                ->where('jenis_berkas_id', $pasFotoId)
                ->latest('id')
                ->limit(1),
            'creator.biodata.berkas' => fn($q) => $q
                ->where('jenis_berkas_id', $pasFotoId)
                ->latest('id')
                ->limit(1),
            'creator.roles'
        ])
            ->whereHas('santri.biodata', fn($q) => $q->where('id', $bioId))
            ->orderByDesc('tanggal_buat')
            ->get()
            ->map(function ($item) {
                $isSuperAdmin = $item->creator?->hasRole('superadmin');

                if ($isSuperAdmin) {
                    // Pencatat adalah superadmin
                    $pencatatBiodata = $item->creator?->biodata?->first();
                    $fotoPath = $pencatatBiodata?->berkas?->first()?->file_path ?? 'default.jpg';
                    $namaPencatat = $pencatatBiodata?->nama ?? $item->creator?->name ?? 'Super Admin';
                    $status = 'Superadmin';
                } elseif ($item->waliAsuh) {
                    // Pencatat adalah wali asuh → santri → biodata
                    $pencatatBiodata = $item->waliAsuh?->santri?->biodata;
                    $fotoPath = $pencatatBiodata?->berkas?->first()?->file_path ?? 'default.jpg';
                    $namaPencatat = $pencatatBiodata?->nama ?? '-';
                    $status = 'Wali Asuh';
                } else {
                    // Tidak diketahui
                    $fotoPath = 'default.jpg';
                    $namaPencatat = '-';
                    $status = 'Tidak diketahui';
                }

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
                    'foto_pencatat' => url($fotoPath),
                    'nama_pencatat' => $namaPencatat,
                    'status' => $status,
                    'status_aktif' => (bool) $item->status,
                ];
            });

        return ['status' => true, 'data' => $kognitif];
    }

    public function edit($id): array
    {
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $item = Catatan_kognitif::with([
            'santri.biodata',
            'waliAsuh.santri.biodata.berkas' => fn($q) => $q
                ->where('jenis_berkas_id', $pasFotoId)
                ->latest('id')
                ->limit(1),
            'creator.biodata.berkas' => fn($q) => $q
                ->where('jenis_berkas_id', $pasFotoId)
                ->latest('id')
                ->limit(1),
            'creator.roles'
        ])->find($id);

        if (! $item) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        $isSuperAdmin = $item->creator?->hasRole('superadmin');

        if ($isSuperAdmin) {
            // Pencatat = superadmin → ambil dari biodata user
            $pencatatBiodata = $item->creator?->biodata?->first();
            $fotoPath = $pencatatBiodata?->berkas?->first()?->file_path ?? 'default.jpg';
            $namaPencatat = $pencatatBiodata?->nama ?? $item->creator?->name ?? 'Super Admin';
            $status = 'Superadmin';
        } elseif ($item->waliAsuh) {
            // Pencatat = wali asuh → santri → biodata
            $pencatatBiodata = $item->waliAsuh?->santri?->biodata;
            $fotoPath = $pencatatBiodata?->berkas?->first()?->file_path ?? 'default.jpg';
            $namaPencatat = $pencatatBiodata?->nama ?? '-';
            $status = 'Wali Asuh';
        } else {
            // fallback
            $fotoPath = 'default.jpg';
            $namaPencatat = '-';
            $status = 'Tidak diketahui';
        }

        $data = [
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
            'foto_pencatat' => url($fotoPath),
            'nama_pencatat' => $namaPencatat,
            'status' => $status,
            'status_aktif' => (bool) $item->status,
        ];

        return ['status' => true, 'data' => $data];
    }
    public function Listedit($id): array
    {
        $kognitif = Catatan_kognitif::select(
            'id',
            'id_santri',
            'id_wali_asuh',
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

        if (! $kognitif) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $kognitif];
    }

    public function updateSuperadmin(array $input, string $id, Request $request): array
    {
        $user = $request->user();

        // Pastikan hanya superadmin yang bisa update
        if (! $user->hasRole('superadmin')) {
            return [
                'status' => false,
                'message' => 'Hanya superadmin yang dapat mengubah catatan kognitif ini.',
            ];
        }

        return DB::transaction(function () use ($input, $id, $user) {
            // 1. Pencarian data
            $kognitif = Catatan_kognitif::find($id);
            if (! $kognitif) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }


            // 3. Update data (tidak termasuk id_wali_asuh)
            $kognitif->update([
                'kebahasaan_nilai' => $input['kebahasaan_nilai'] ?? $kognitif->kebahasaan_nilai,
                'kebahasaan_tindak_lanjut' => $input['kebahasaan_tindak_lanjut'] ?? $kognitif->kebahasaan_tindak_lanjut,
                'baca_kitab_kuning_nilai' => $input['baca_kitab_kuning_nilai'] ?? $kognitif->baca_kitab_kuning_nilai,
                'baca_kitab_kuning_tindak_lanjut' => $input['baca_kitab_kuning_tindak_lanjut'] ?? $kognitif->baca_kitab_kuning_tindak_lanjut,
                'hafalan_tahfidz_nilai' => $input['hafalan_tahfidz_nilai'] ?? $kognitif->hafalan_tahfidz_nilai,
                'hafalan_tahfidz_tindak_lanjut' => $input['hafalan_tahfidz_tindak_lanjut'] ?? $kognitif->hafalan_tahfidz_tindak_lanjut,
                'furudul_ainiyah_nilai' => $input['furudul_ainiyah_nilai'] ?? $kognitif->furudul_ainiyah_nilai,
                'furudul_ainiyah_tindak_lanjut' => $input['furudul_ainiyah_tindak_lanjut'] ?? $kognitif->furudul_ainiyah_tindak_lanjut,
                'tulis_alquran_nilai' => $input['tulis_alquran_nilai'] ?? $kognitif->tulis_alquran_nilai,
                'tulis_alquran_tindak_lanjut' => $input['tulis_alquran_tindak_lanjut'] ?? $kognitif->tulis_alquran_tindak_lanjut,
                'baca_alquran_nilai' => $input['baca_alquran_nilai'] ?? $kognitif->baca_alquran_nilai,
                'baca_alquran_tindak_lanjut' => $input['baca_alquran_tindak_lanjut'] ?? $kognitif->baca_alquran_tindak_lanjut,
                'updated_by' => $user->id,
            ]);

            // 4. Return hasil
            return [
                'status' => true,
                'data' => $kognitif->fresh(),
            ];
        });
    }


    // public function keluarKognitif(array $input, int $id): array
    // {
    //     return DB::transaction(function () use ($input, $id) {
    //         $kognitif = Catatan_kognitif::find($id);
    //         if (! $kognitif) {
    //             return ['status' => false, 'message' => 'Data tidak ditemukan.'];
    //         }

    //         if ($kognitif->tanggal_selesai) {
    //             return [
    //                 'status' => false,
    //                 'message' => 'Data kognitif sudah ditandai selesai/nonaktif.',
    //             ];
    //         }

    //         $tglSelesai = Carbon::parse($input['tanggal_selesai'] ?? '');

    //         if ($tglSelesai->lt(Carbon::parse($kognitif->tanggal_buat))) {
    //             return [
    //                 'status' => false,
    //                 'message' => 'Tanggal selesai tidak boleh sebelum tanggal buat.',
    //             ];
    //         }

    //         $kognitif->update([
    //             'status' => 0,
    //             'tanggal_selesai' => $tglSelesai,
    //             'updated_by' => Auth::id(),
    //         ]);

    //         return [
    //             'status' => true,
    //             'data' => $kognitif,
    //         ];
    //     });
    // }

    public function storeSuperadminKognitif(array $data, string $bioId, Request $request): array
    {
        $user = $request->user();

        // Pastikan hanya superadmin yang bisa menjalankan ini
        if (! $user->hasRole('superadmin')) {
            return [
                'status' => false,
                'message' => 'Hanya superadmin yang dapat membuat catatan kognitif untuk biodata ini.',
            ];
        }

        return DB::transaction(function () use ($data, $bioId, $user) {

            // 1. Ambil santri berdasarkan biodata_id
            $santri = Santri::where('biodata_id', $bioId)->latest()->first();
            if (! $santri) {
                return [
                    'status' => false,
                    'message' => 'Santri tidak ditemukan.',
                ];
            }

            if ($santri->status !== 'aktif') {
                return [
                    'status' => false,
                    'message' => 'Santri sudah tidak aktif.',
                ];
            }

            // 2. Cek apakah santri memiliki anak_asuh
            $anakAsuh = DB::table('anak_asuh')
                ->where('id_santri', $santri->id)
                ->first();
            if (! $anakAsuh) {
                return [
                    'status' => false,
                    'message' => 'Santri ini tidak memiliki anak asuh. Catatan kognitif tidak bisa dibuat.',
                ];
            }

            // 3. Cek apakah santri adalah wali_asuh → jika iya, tidak boleh
            $isWaliAsuh = DB::table('wali_asuh')
                ->where('id_santri', $santri->id)
                ->exists();
            if ($isWaliAsuh) {
                return [
                    'status' => false,
                    'message' => 'Santri ini merupakan wali asuh. Catatan kognitif tidak bisa dibuat.',
                ];
            }


            // 5. Ambil id_wali_asuh dari tabel kewaliasuhan terkait anak_asuh
            $waliAsuhId = DB::table('kewaliasuhan')
                ->where('id_anak_asuh', $anakAsuh->id)
                ->value('id_wali_asuh');
            if (! $waliAsuhId) {
                return [
                    'status' => false,
                    'message' => 'Belum ada wali asuh untuk anak asuh ini.',
                ];
            }

            // 6. Buat Catatan Kognitif baru
            $kognitif = Catatan_kognitif::create([
                'id_santri' => $santri->id,
                'id_wali_asuh' => $waliAsuhId,
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
                'tanggal_buat' => now(),
                'status' => 1,
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'status' => true,
                'data' => $kognitif->fresh(),
            ];
        });
    }
}
