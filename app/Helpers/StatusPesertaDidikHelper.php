<?php

namespace App\Helpers;

use App\Models\StatusPesertaDidik;
use App\Models\Santri;
use App\Models\RiwayatPendidikan;
use Carbon\Carbon;

class StatusPesertaDidikHelper
{
    public static function updateFromSantri($biodataId)
    {
        $santri = Santri::where('biodata_id', $biodataId)->latest()->first();

        $status = StatusPesertaDidik::firstOrNew(['biodata_id' => $biodataId]);
        $status->is_santri = $santri ? true : false;

        if ($santri) {
            // Pastikan hanya mengambil status yang valid dari enum
            $validStatuses = ['aktif', 'alumni', 'do', 'berhenti', 'nonaktif'];
            $statusSantri = in_array($santri->status, $validStatuses) ? $santri->status : 'nonaktif';

            $status->status_santri = $statusSantri;
            $status->tanggal_keluar_santri = $santri->tanggal_keluar ?? null;
        } else {
            $status->status_santri = 'nonaktif';
            $status->tanggal_keluar_santri = null;
        }

        $status->save();
    }


    public static function updateFromPendidikan($biodataId)
    {
        $pendidikan = RiwayatPendidikan::where('biodata_id', $biodataId)->latest()->first();

        $status = StatusPesertaDidik::firstOrNew(['biodata_id' => $biodataId]);
        $status->is_pelajar = $pendidikan ? true : false;

        if ($pendidikan) {
            $validStatuses = ['aktif', 'do', 'berhenti', 'lulus', 'pindah', 'cuti', 'naik_kelas', 'nonaktif'];
            $statusPelajar = in_array($pendidikan->status, $validStatuses) ? $pendidikan->status : 'nonaktif';

            $status->status_pelajar = $statusPelajar;
            $status->tanggal_keluar_pelajar = $pendidikan->tanggal_keluar ?? null;
        } else {
            $status->status_pelajar = 'nonaktif';
            $status->tanggal_keluar_pelajar = null;
        }

        $status->save();
    }
}
