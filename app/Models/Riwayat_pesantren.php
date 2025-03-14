<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Riwayat_pesantren extends Model
{
    protected $table = 'riwayat_pesantren';
    protected $fillable = [
        'id_peserta_didik',
        'id_wilayah',
        'id_blok',
        'id_kamar',
        'id_domisili',
        'tanggal_masuk',
        'tanggal_keluar',
        'keterangan',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];
}
