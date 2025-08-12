<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogPresensi extends Model
{
    use SoftDeletes;

    protected $table = 'log_presensi';

    protected $fillable = [
        'santri_id',
        'kartu_id',
        'sholat_id',
        'waktu_scan',
        'hasil',
        'pesan',
        'metode',
        'user_id',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function kartu()
    {
        return $this->belongsTo(Kartu::class, 'kartu_id');
    }

    public function sholat()
    {
        return $this->belongsTo(Sholat::class, 'sholat_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
