<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Riwayat_pendidikan extends Model
{
    protected $table = 'riwayat_pendidikan';
    protected $fillable = [
        'id_peserta_didik',
        'id_lembaga',
        'id_jurusan',
        'id_kelas',
        'id_rombel',
        'no_induk',
        'tanggal_masuk',
        'tanggal_keluar',
        'keterangan',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];
}
