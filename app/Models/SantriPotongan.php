<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SantriPotongan extends Model
{
    protected $table = 'santri_potongan';

    protected $fillable = [
        'santri_id',
        'potongan_id',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function potongan(): BelongsTo
    {
        return $this->belongsTo(Potongan::class);
    }
}
