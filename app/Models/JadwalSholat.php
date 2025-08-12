<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalSholat extends Model
{
    use SoftDeletes;

    protected $table = 'jadwal_sholat';

    protected $fillable = [
        'sholat_id',
        'jam_mulai',
        'jam_selesai',
        'berlaku_mulai',
        'berlaku_sampai',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function sholat()
    {
        return $this->belongsTo(Sholat::class, 'sholat_id');
    }
}
