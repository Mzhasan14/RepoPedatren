<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisPresensi extends Model
{
    use SoftDeletes;

    protected $table = 'jenis_presensi';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function presensiSantri()
    {
        return $this->hasMany(PresensiSantri::class, 'jenis_presensi_id');
    }
}
