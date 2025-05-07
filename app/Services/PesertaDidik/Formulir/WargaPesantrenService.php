<?php

namespace App\Services\PesertaDidik\Formulir;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WargaPesantrenService
{
    public function store(array $data, $bioId)
    {
        $exist = DB::table('warga_pesantren as wp')
            ->where('wp.biodata_id', $bioId)
            ->first();

        if ($exist) {
            return ['status' => false, 'message' => 'Biodata sudah memiliki NIUP'];
        }

        $wp = DB::table('warga_pesantren as wp')
            ->insertGetId([
                'biodata_id' => $bioId,
                'niup' => $data['niup'],
                'status' => $data['status'],
                'created_by' => Auth::id(),
                'created_at' => now()
            ]);

        return ['status' => true, 'data' => $wp];
    }

    public function edit(string $id)
    {
        $wp = DB::table('warga_pesantren as wp')
            ->where('id', $id)
            ->select(
                'id',
                'niup',
                'status',
            )
            ->first();

        if (!$wp) {
            return ['status' => false, 'data' => []];
        }
        return ['status' => true, 'data' => $wp];
    }

    public function update(array $data, string $id)
    {
        $wp = DB::table('warga_pesantren')
            ->where('id', $id)
            ->latest()
            ->first();

        if (!$wp) {
            return ['status' => false, 'message' => 'Data memiliki niup'];
        }

        // Cek perubahan lokasi
        $isNiupChanged = $wp->niup !== $data['niup'];
        $isStatusChanged = $wp->status !== $data['status'];

        if ($isNiupChanged || $isStatusChanged) {

            DB::table('warga_pesantren')
                ->where('id', $wp->id)
                ->update([
                    'status' => false,
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            $new = DB::table('warga_pesantren')->insertGetId([
                'biodata_id' => $wp->biodata_id,
                'niup' => $data['niup'],
                'status' => $data['status'],
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $newData = DB::table('warga_pesantren')->where('id', $new)->first();
            return ['status' => true, 'data' => $newData];
        }

        return ['status' => false, 'message' => 'Tidak ada perubahan data'];
    }
}
