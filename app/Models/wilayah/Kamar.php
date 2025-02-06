<?php

namespace App\Models\wilayah;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kamar extends Model
{
    use SoftDeletes;
    protected $table = 'kamar';
    protected $fillable = [
        'nama_kamar',
        'created_by',
        'status',
    ];
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function blok()
    {
        return $this->belongsTo(Blok::class, 'id_blok', 'id');
    }

    public function domisili()
    {
        return $this->hasMany(Domisili::class, 'id_kamar', 'id');
    }
   
}
