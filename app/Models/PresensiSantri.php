<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PresensiSantri extends Model
{
    use SoftDeletes;

    protected $table = 'presensi_santri';

    protected $fillable = [
        'santri_id',
        'jenis_presensi_id',
        'tanggal',
        'waktu_presensi',
        'status',
        'keterangan',
        'lokasi',
        'metode',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = ['tanggal', 'waktu_presensi'];
}
