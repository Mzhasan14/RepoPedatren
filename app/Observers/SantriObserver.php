<?php

namespace App\Observers;

use App\Models\Santri;
use App\Models\RiwayatSantri;
use Illuminate\Support\Facades\DB;
use App\Services\PelajarSantriService;

class SantriObserver
{
   
    /**
     * Handle the Santri "created" event.
     */
    public function created(Santri $santri): void
    {
        //
    }

    /**
     * Handle the Santri "updated" event.
     */
    public function updated(Santri $santri)
    {
        if (in_array($santri->status_santri, ['lulus', 'do', 'berhenti', 'cuti'])) {
            DB::transaction(function () use ($santri) {
                RiwayatSantri::create([
                    'id_peserta_didik' => $santri->id_peserta_didik,
                    'id_wilayah' => $santri->id_wilayah,
                    'id_blok' => $santri->id_blok,
                    'id_kamar' => $santri->id_kamar,
                    'id_domisili' => $santri->id_domisili,
                    'nis' => $santri->nis,
                    'angkatan_santri' => $santri->angkatan_santri,
                    'tanggal_masuk_santri' => $santri->tanggal_masuk_santri,
                    'tanggal_keluar_santri' => now(),
                    'status_santri' => $santri->status_santri === 'lulus' ? 'alumni' : $santri->status,
                    'created_by' => $santri->created_by
                ]);
            });
        }
    }

    /**
     * Handle the Santri "deleted" event.
     */
    public function deleted(Santri $santri): void
    {
        //
    }

    /**
     * Handle the Santri "restored" event.
     */
    public function restored(Santri $santri): void
    {
        //
    }

    /**
     * Handle the Santri "force deleted" event.
     */
    public function forceDeleted(Santri $santri): void
    {
        //
    }
}
