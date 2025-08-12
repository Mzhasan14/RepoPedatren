<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kitab extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kitab';
    protected $fillable = [
        'nama_kitab',
        'total_bait',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Relasi
    public function nadhoman()
    {
        return $this->hasMany(Nadhoman::class);
    }

    public function rekapNadhoman()
    {
        return $this->hasMany(RekapNadhoman::class);
    }
}
