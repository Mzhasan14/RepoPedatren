<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kartu extends Model
{
    use SoftDeletes;

    protected $table = 'kartu';

    protected $fillable = [
        'santri_id',
        'uid_kartu',
        'pin',
        'aktif',
        'tanggal_terbit',
        'tanggal_expired',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function logPresensi()
    {
        return $this->hasMany(LogPresensi::class, 'kartu_id');
    }
}
