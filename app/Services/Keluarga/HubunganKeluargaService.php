<?php

namespace App\Services\Keluarga;

use App\Models\HubunganKeluarga;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HubunganKeluargaService
{
    public function index(): array
    {
        $data = HubunganKeluarga::orderBy('id', 'asc')->get();

        return [
            'status' => true,
            'data' => $data->map(fn($item) => [
                'id' => $item->id,
                'nama_status' => $item->nama_status,
                'created_by' => $item->created_by,
                'created_at' => $item->created_at,
                'updated_by' => $item->updated_by,
                'updated_at' => $item->updated_at,
                'deleted_by' => $item->deleted_by,
                'deleted_at' => $item->deleted_at,
            ]),
        ];
    }

    public function store(array $input)
    {
        $hubungan = HubunganKeluarga::create([
            'nama_status' => $input['nama_status'],
            'created_by' => Auth::id(),
            'created_at' => now(),
            'status' => true,
        ]);

        return [
            'status' => true,
            'data' => $hubungan,
        ];
    }

    public function show(int $id)
    {
        $hubungan = HubunganKeluarga::find($id);

        if (! $hubungan) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        return [
            'status' => true,
            'data' => $hubungan,
        ];
    }

    public function update(array $input, int $id)
    {
        $hubungan = HubunganKeluarga::find($id);

        if (! $hubungan) {
            return [
                'status' => false,
                'message' => 'data tidak ditemukan',
            ];
        }

        $hubungan->update([
            'nama_status' => $input['nama_status'],
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        return [
            'status' => true,
            'data' => $hubungan,
        ];
    }

    public function delete(int $id)
    {
        $hubungan = HubunganKeluarga::find($id);

        if (! $hubungan) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        $hubungan->delete();

        return [
            'status' => true,
            'message' => 'Data berhasil dihapus',
        ];
    }

    public function setwali(string $BiodataId)
    {
        return DB::transaction(function () use ($BiodataId) {
            $wali = DB::table('biodata as b')
                ->join('keluarga as k', 'b.id', '=', 'k.id_biodata')
                ->where('b.id', $BiodataId)
                ->select(['k.no_kk'])
                ->first();

            if (!$wali) {
                return [
                    'status' => false,
                    'message' => 'Data biodata tidak ditemukan atau belum memiliki informasi keluarga (no_kk).'
                ];
            }

            $santri = DB::table('santri as s')
                ->join('biodata as b', 'b.id', '=', 's.biodata_id')
                ->join('keluarga as k', 'b.id', '=', 'k.id_biodata')
                ->where('k.no_kk', $wali->no_kk)
                ->where('s.status', 'aktif')
                ->select(['b.id', 'b.nama'])
                ->get();

            if ($santri->isEmpty()) {
                return [
                    'status' => false,
                    'message' => 'Biodata keluarga ini tidak bisa dijadikan wali karena tidak memiliki santri aktif.'
                ];
            }

            $existingWali = DB::table('orang_tua_wali as otw')
                ->where('otw.id_biodata', $BiodataId)
                ->select(['otw.wali'])
                ->first();

            if ($existingWali && $existingWali->wali == true) {
                return [
                    'status' => false,
                    'message' => 'Data ini sudah menjadi wali, tidak bisa di-set ulang.'
                ];
            }

            $isSantriAktif = DB::table('santri')
                ->where('biodata_id', $BiodataId)
                ->where('status', 'aktif')
                ->exists();

            if ($isSantriAktif) {
                return [
                    'status' => false,
                    'message' => 'Biodata ini tidak bisa dijadikan wali karena merupakan santri aktif.'
                ];
            }

            $hasOrtuWali = DB::table('orang_tua_wali')
                ->where('id_biodata', $BiodataId)
                ->exists();

            if (!$hasOrtuWali) {
                return [
                    'status' => false,
                    'message' => 'Biodata ini tidak memiliki data orang tua/wali, sehingga tidak bisa dijadikan wali.'
                ];
            }

            $keluargaIds = DB::table('keluarga')
                ->where('no_kk', $wali->no_kk)
                ->pluck('id_biodata')
                ->toArray();

            if (empty($keluargaIds)) {
                return [
                    'status' => false,
                    'message' => 'Gagal menemukan anggota keluarga untuk no_kk tersebut.'
                ];
            }

            DB::table('orang_tua_wali')
                ->whereIn('id_biodata', $keluargaIds)
                ->update([
                    'wali' => false,
                    'updated_at' => now()
                ]);

            DB::table('orang_tua_wali')
                ->where('id_biodata', $BiodataId)
                ->update([
                    'wali' => true,
                    'updated_at' => now()
                ]);

            $biodata = DB::table('biodata')->where('id', $BiodataId)->first();
            activity('penetapan_wali')
                ->causedBy(Auth::user())
                ->performedOn(new \App\Models\Biodata((array)$biodata))
                ->withProperties([
                    'biodata_id' => $biodata->id ?? null,
                    'no_kk'      => $wali->no_kk ?? null,
                    'santri_aktif' => $santri->pluck('nama'),
                    'ip'         => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->event('set_wali')
                ->log("Penetapan wali atas nama {$biodata->nama} berhasil. Wali lain di keluarga yang sama dinonaktifkan.");

            return [
                'status' => true,
                'message' => 'Biodata berhasil dijadikan wali. Wali sebelumnya di keluarga yang sama telah dinonaktifkan.',
                'wali' => $BiodataId
            ];
        });
    }
}
