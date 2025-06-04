<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusPesertaDidik extends Model
{
    protected $table = 'status_peserta_didik';

    protected $fillable = [
        'biodata_id',
        'is_santri',
        'is_pelajar',
        'status_santri',
        'status_pelajar',
        'tanggal_keluar_santri',
        'tanggal_keluar_pelajar',
        'keterangan',
    ];

    public function biodata()
    {
        return $this->belongsTo(Biodata::class);
    }
}
