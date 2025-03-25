<?php

namespace App\Observers;

use App\Models\Pelajar;
use App\Models\RiwayatPelajar;
use Illuminate\Support\Facades\DB;
use App\Services\PelajarSantriService;

class PelajarObserver
{
    // protected $service;

    // public function __construct(PelajarSantriService $service)
    // {
    //     $this->service = $service;
    // }
    /**
     * Handle the Pelajar "created" event.
     */
    public function created(Pelajar $pelajar): void
    {
        //
    }

    /**
     * Handle the Pelajar "updated" event.
     */
    public function updated(Pelajar $pelajar)
    {
        if (in_array($pelajar->status_pelajar, ['lulus', 'do', 'berhenti', 'cuti'])) {
            DB::transaction(function () use ($pelajar) {
                RiwayatPelajar::create([
                    'id_peserta_didik' => $pelajar->id_peserta_didik,
                    'id_lembaga' => $pelajar->id_lembaga,
                    'id_jurusan' => $pelajar->id_jurusan,
                    'id_kelas' => $pelajar->id_kelas,
                    'id_rombel' => $pelajar->id_rombel,
                    'no_induk' => $pelajar->no_induk,
                    'angkatan_pelajar' => $pelajar->angkatan_pelajar,
                    'tanggal_masuk_pelajar' => $pelajar->tanggal_masuk_pelajar,
                    'tanggal_keluar_pelajar' => now(),
                    'status_pelajar' => $pelajar->status_pelajar === 'lulus' ? 'alumni' : $pelajar->status,
                    'created_by' => $pelajar->created_by
                ]);
            });
        }
    }

    /**
     * Handle the Pelajar "deleted" event.
     */
    public function deleted(Pelajar $pelajar): void
    {
        //
    }

    /**
     * Handle the Pelajar "restored" event.
     */
    public function restored(Pelajar $pelajar): void
    {
        //
    }

    /**
     * Handle the Pelajar "force deleted" event.
     */
    public function forceDeleted(Pelajar $pelajar): void
    {
        //
    }
}
