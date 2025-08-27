<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SantriPotongan extends Model
{
    protected $table = 'santri_potongan';

    protected $fillable = [
        'santri_id',
        'potongan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
    ];

    // Relasi ke Santri
    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    // Relasi ke Potongan
    public function potongan()
    {
        return $this->belongsTo(Potongan::class);
    }
}
