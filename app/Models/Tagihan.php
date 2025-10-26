<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tagihan extends Model
{
    use SoftDeletes;

    protected $table = 'tagihan';

    protected $fillable = [
        'nama_tagihan',
        'tipe',
        'nominal',
        'jatuh_tempo',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function potongans()
    {
        return $this->belongsToMany(Potongan::class, 'potongan_tagihan')
            ->select([
                'potongan.id',     
                'nama',
                'kategori',
                'jenis',
                'nilai',
                'status',
                'keterangan'
            ]);
    }


    public function tagihanSantri()
    {
        return $this->hasMany(\App\Models\TagihanSantri::class);
    }
}
