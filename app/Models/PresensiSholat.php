<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PresensiSholat extends Model
{
    use SoftDeletes;

    protected $table = 'presensi_sholat';

    protected $fillable = [
        'santri_id',
        'sholat_id',
        'tanggal',
        'waktu_presensi',
        'status',
        'metode',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function sholat()
    {
        return $this->belongsTo(Sholat::class, 'sholat_id');
    }
}
