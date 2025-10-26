<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Potongan extends Model
{
    use SoftDeletes;

    protected $table = 'potongan';

    protected $fillable = [
        'nama',
        'kategori',
        'jenis',
        'nilai',
        'status',
        'keterangan'
    ];

    public function tagihans()
    {
        return $this->belongsToMany(Tagihan::class, 'potongan_tagihan');
    }

    public function santris()
    {
        return $this->belongsToMany(Santri::class, 'santri_potongan')
            ->withTimestamps();
    }
}
