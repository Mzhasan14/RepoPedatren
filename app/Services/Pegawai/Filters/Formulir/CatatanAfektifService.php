<?php

namespace App\Services\Pegawai\Filters\Formulir;

use App\Models\Catatan_afektif;
use App\Models\Santri;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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
                },
            ])
            ->orderByDesc('tanggal_buat')
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
                    'nama_pencatat' => $namaPencatat,
                    'status' => 'Wali Asuh',
                    'status_aktif' => (bool) $item->status,
                ];
            });

        return ['status' => true, 'data' => $afektif];
    }

    public function show($id): array
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
        if (! $afektif) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $afektif];
    }
    public function Listshow($id): array
    {
        $afektif = Catatan_afektif::select(
            'id',
            'id_santri',
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
        if (! $afektif) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $afektif];
    }

    public function updateSuperadmin(array $input, string $id, Request $request): array
    {
        $user = $request->user();

        // Pastikan hanya superadmin yang bisa update
        if (! $user->hasRole('superadmin')) {
            return [
                'status' => false,
                'message' => 'Hanya superadmin yang dapat mengubah catatan afektif ini.',
            ];
        }

        return DB::transaction(function () use ($input, $id) {
            // 1. Pencarian data
            $afektif = Catatan_afektif::find($id);
            if (! $afektif) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            $afektif->update([
                'kepedulian_nilai' => $input['kepedulian_nilai'] ?? $afektif->kepedulian_nilai,
                'kepedulian_tindak_lanjut' => $input['kepedulian_tindak_lanjut'] ?? $afektif->kepedulian_tindak_lanjut,
                'kebersihan_nilai' => $input['kebersihan_nilai'] ?? $afektif->kebersihan_nilai,
                'kebersihan_tindak_lanjut' => $input['kebersihan_tindak_lanjut'] ?? $afektif->kebersihan_tindak_lanjut,
                'akhlak_nilai' => $input['akhlak_nilai'] ?? $afektif->akhlak_nilai,
                'akhlak_tindak_lanjut' => $input['akhlak_tindak_lanjut'] ?? $afektif->akhlak_tindak_lanjut,
                'updated_by' => Auth::id(),
            ]);

            // 4. Return hasil
            return [
                'status' => true,
                'data' => $afektif->fresh(),
            ];
        });
    }


    public function storeSuperadmin(array $data, string $bioId, Request $request): array
    {
        $user = $request->user();


        // Pastikan hanya superadmin yang bisa menjalankan ini
        if (! $user->hasRole('superadmin')) {
            return [
                'status' => false,
                'message' => 'Hanya superadmin yang dapat membuat catatan afektif untuk biodata ini.',
            ];
        }
        return DB::transaction(function () use ($data, $bioId) {
            // 1. Ambil santri berdasarkan biodata_id
            $santri = Santri::where('biodata_id', $bioId)
                ->latest()
                ->first();

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
                    'message' => 'Santri ini tidak memiliki anak asuh. Catatan tidak bisa dibuat.',
                ];
            }

            // 3. Cek apakah santri adalah wali_asuh → jika iya, tidak boleh
            $isWaliAsuh = DB::table('wali_asuh')
                ->where('id_santri', $santri->id)
                ->exists();

            if ($isWaliAsuh) {
                return [
                    'status' => false,
                    'message' => 'Santri ini merupakan wali asuh. Catatan tidak bisa dibuat.',
                ];
            }

            // 4. Cek jika masih ada catatan afektif aktif untuk santri
            $existing = Catatan_afektif::where('id_santri', $santri->id)
                ->whereNull('tanggal_selesai')
                ->where('status', 1)
                ->first();

            if ($existing) {
                return [
                    'status' => false,
                    'message' => 'Santri masih memiliki Catatan Afektif aktif.',
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

            // 6. Buat Catatan Afektif baru
            $afektif = Catatan_afektif::create([
                'id_santri' => $santri->id,
                'id_wali_asuh' => $waliAsuhId,
                'kepedulian_nilai' => $data['kepedulian_nilai'],
                'kepedulian_tindak_lanjut' => $data['kepedulian_tindak_lanjut'],
                'kebersihan_nilai' => $data['kebersihan_nilai'],
                'kebersihan_tindak_lanjut' => $data['kebersihan_tindak_lanjut'],
                'akhlak_nilai' => $data['akhlak_nilai'],
                'akhlak_tindak_lanjut' => $data['akhlak_tindak_lanjut'],
                'tanggal_buat' =>  now(),
                'status' => 1,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'status' => true,
                'data' => $afektif->fresh(),
            ];
        });
    }

    // public function keluarAfektif(array $input, int $id): array
    // {
    //     return DB::transaction(function () use ($input, $id) {
    //         $afektif = Catatan_afektif::find($id);
    //         if (! $afektif) {
    //             return ['status' => false, 'message' => 'Data tidak ditemukan.'];
    //         }

    //         if ($afektif->tanggal_selesai) {
    //             return [
    //                 'status' => false,
    //                 'message' => 'Data afektif sudah ditandai selesai/nonaktif.',
    //             ];
    //         }

    //         $tglSelesai = Carbon::parse($input['tanggal_selesai'] ?? '');

    //         if ($tglSelesai->lt(Carbon::parse($afektif->tanggal_buat))) {
    //             return [
    //                 'status' => false,
    //                 'message' => 'Tanggal selesai tidak boleh sebelum tanggal buat.',
    //             ];
    //         }

    //         $afektif->update([
    //             'status' => 0,
    //             'tanggal_selesai' => $tglSelesai,
    //             'updated_by' => Auth::id(),
    //         ]);

    //         return [
    //             'status' => true,
    //             'data' => $afektif,
    //         ];
    //     });
    // }
}
