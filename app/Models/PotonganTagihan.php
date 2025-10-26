<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PotonganTagihan extends Model
{
    protected $table = 'potongan_tagihan';

    protected $fillable = [
        'potongan_id',
        'tagihan_id',
    ];

    // Relasi ke Potongan
    public function potongan()
    {
        return $this->belongsTo(Potongan::class);
    }

    // Relasi ke Tagihan
    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }
}
