<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sholat extends Model
{
    use SoftDeletes;

    protected $table = 'sholat';

    protected $fillable = [
        'nama_sholat',
        'urutan',
        'aktif',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    // Relasi ke JadwalSholat
    public function jadwalSholat()
    {
        return $this->hasMany(JadwalSholat::class, 'sholat_id');
    }

    // Relasi ke PresensiSholat
    public function presensiSholat()
    {
        return $this->hasMany(PresensiSholat::class, 'sholat_id');
    }

    // Relasi ke LogPresensi
    public function logPresensi()
    {
        return $this->hasMany(LogPresensi::class, 'sholat_id');
    }
}
