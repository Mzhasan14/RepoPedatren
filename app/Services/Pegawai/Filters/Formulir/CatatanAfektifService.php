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
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $afektif = Catatan_afektif::with([
            'santri.biodata',
            'santri.domisili.blok',
            'santri.domisili.wilayah',
            'santri.pendidikan.jurusan',
            'santri.pendidikan.lembaga',
            // Relasi untuk wali_asuh
            'waliAsuh.santri.biodata.berkas' => fn($q) => $q->where('jenis_berkas_id', $pasFotoId)->latest('id')->limit(1),
            // Relasi untuk superadmin (creator)
            'creator.biodata.berkas' => fn($q) => $q->where('jenis_berkas_id', $pasFotoId)->latest('id')->limit(1),
            'creator.roles'
        ])
            ->whereHas('santri.biodata', fn($q) => $q->where('id', $bioId))
            ->orderByDesc('tanggal_buat')
            ->get()
            ->map(function ($item) {
                // Tentukan pencatat
                $isSuperAdmin = optional($item->creator?->roles)->contains('name', 'superadmin');

                if ($isSuperAdmin) {
                    // Data superadmin
                    $pencatatBiodata = $item->creator?->biodata;
                    $pencatatUuid = $pencatatBiodata?->id;
                    $namaPencatat = $pencatatBiodata?->nama ?? $item->creator?->name ?? 'Super Admin';
                    $fotoPath = $pencatatBiodata?->berkas?->first()?->file_path ?? 'default.jpg';
                    $status = 'Superadmin';
                } elseif ($item->waliAsuh) {
                    // Data wali asuh
                    $pencatatBiodata = $item->waliAsuh?->santri?->biodata;
                    $pencatatUuid = $pencatatBiodata?->id;
                    $namaPencatat = $pencatatBiodata?->nama ?? '-';
                    $fotoPath = $pencatatBiodata?->berkas?->first()?->file_path ?? 'default.jpg';
                    $status = 'Wali Asuh';
                } else {
                    $pencatatUuid = null;
                    $namaPencatat = '-';
                    $fotoPath = 'default.jpg';
                    $status = 'Tidak diketahui';
                }

                $pendidikan = $item->santri?->pendidikan?->first();

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
                    'status' => $status,
                    'status_aktif' => (bool) $item->status,
                ];
            });

        return [
            'status' => true,
            'data' => $afektif
        ];
    }

    public function show($id): array
    {
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $afektif = Catatan_afektif::with([
            // Relasi wali asuh → santri → biodata → berkas
            'waliAsuh.santri.biodata.berkas' => fn($q) => $q
                ->where('jenis_berkas_id', $pasFotoId)
                ->latest('id')
                ->limit(1),
            // Relasi superadmin → biodata → berkas
            'creator.biodata.berkas' => fn($q) => $q
                ->where('jenis_berkas_id', $pasFotoId)
                ->latest('id')
                ->limit(1),
            'creator.roles'
        ])->find($id);

        if (!$afektif) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        $isSuperAdmin = optional($afektif->creator)->hasRole('superadmin');

        if ($isSuperAdmin) {
            // Pencatat = superadmin → ambil dari biodata user
            $pencatatBiodata = $afektif->creator?->biodata;
            $fotoPath = $pencatatBiodata?->berkas?->first()?->file_path ?? 'default.jpg';
            $namaPencatat = $pencatatBiodata?->nama ?? $afektif->creator?->name ?? 'Super Admin';
            $status = 'Superadmin';
        } elseif (!empty($afektif->id_wali_asuh) && $afektif->waliAsuh) {
            // Pencatat = wali asuh → santri → biodata
            $pencatatBiodata = $afektif->waliAsuh?->santri?->biodata;
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
            'id' => $afektif->id,
            'kepedulian_nilai' => $afektif->kepedulian_nilai,
            'kepedulian_tindak_lanjut' => $afektif->kepedulian_tindak_lanjut,
            'kebersihan_nilai' => $afektif->kebersihan_nilai,
            'kebersihan_tindak_lanjut' => $afektif->kebersihan_tindak_lanjut,
            'akhlak_nilai' => $afektif->akhlak_nilai,
            'akhlak_tindak_lanjut' => $afektif->akhlak_tindak_lanjut,
            'tanggal_buat' => $afektif->tanggal_buat,
            'tanggal_selesai' => $afektif->tanggal_selesai,
            'foto_pencatat' => url($fotoPath),
            'nama_pencatat' => $namaPencatat,
            'status' => $status,
            'status_aktif' => (bool) $afektif->status,
        ];

        return ['status' => true, 'data' => $data];
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

        // Pastikan hanya superadmin
        if (! $user->hasRole('superadmin')) {
            return [
                'status' => false,
                'message' => 'Hanya superadmin yang dapat membuat catatan afektif untuk biodata ini.',
            ];
        }

        return DB::transaction(function () use ($data, $bioId, $user) {
            // Ambil santri berdasarkan biodata_id
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

            // Ambil anak_asuh terkait santri
            $anakAsuh = DB::table('anak_asuh as aa')
                ->join('grup_wali_asuh as g', 'aa.grup_wali_asuh_id', '=', 'g.id')
                ->select('aa.*', 'g.wali_asuh_id')
                ->where('aa.id_santri', $santri->id)
                ->first();

            if (! $anakAsuh) {
                return [
                    'status' => false,
                    'message' => 'Santri ini masih belum menjadi anak asuh. Catatan tidak bisa dibuat.',
                ];
            }

            // Cek apakah santri adalah wali_asuh → jika iya, tidak boleh
            $isWaliAsuh = DB::table('wali_asuh')
                ->where('id_santri', $santri->id)
                ->exists();

            if ($isWaliAsuh) {
                return [
                    'status' => false,
                    'message' => 'Santri ini merupakan wali asuh. Catatan tidak bisa dibuat.',
                ];
            }

            // Ambil wali_asuh_id dari grup anak_asuh, boleh null
            $waliAsuhId = $anakAsuh->wali_asuh_id ?: null;

            // Buat Catatan Afektif baru
            $afektif = Catatan_afektif::create([
                'id_santri' => $santri->id,
                'id_wali_asuh' => $waliAsuhId, // bisa nullable
                'kepedulian_nilai' => $data['kepedulian_nilai'],
                'kepedulian_tindak_lanjut' => $data['kepedulian_tindak_lanjut'],
                'kebersihan_nilai' => $data['kebersihan_nilai'],
                'kebersihan_tindak_lanjut' => $data['kebersihan_tindak_lanjut'],
                'akhlak_nilai' => $data['akhlak_nilai'],
                'akhlak_tindak_lanjut' => $data['akhlak_tindak_lanjut'],
                'tanggal_buat' => now(),
                'status' => 1,
                'created_by' => $user->id,
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
