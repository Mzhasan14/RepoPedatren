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

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }
    public function blok(): BelongsTo
    {
        return $this->belongsTo(Blok::class, 'blok_id');
    }
    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }
    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }
    
}
