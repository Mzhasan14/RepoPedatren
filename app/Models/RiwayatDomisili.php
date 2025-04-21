<?php

namespace App\Models;

use App\Models\Kewilayahan\Blok;
use App\Models\Kewilayahan\Kamar;
use App\Models\Kewilayahan\Wilayah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatDomisili extends Model
{
    protected $table = 'riwayat_domisili';

    protected $guarded = ['id'];

    // public function wilayah(): BelongsTo
    // {
    //     return $this->belongsTo(Wilayah::class, 'id_wilayah');
    // }
    // public function blok(): BelongsTo
    // {
    //     return $this->belongsTo(Blok::class, 'id_blok');
    // }
    // public function kamar(): BelongsTo
    // {
    //     return $this->belongsTo(Kamar::class, 'id_kamar');
    // }
}
