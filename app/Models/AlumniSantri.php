<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniSantri extends Model
{
    protected $table = 'alumni_santri';
    protected $fillable = [
        'id_santri',
        'id_wilayah',
        'id_blok',
        'id_kamar',
        'id_domisili',
        'status_alumni',
        'tahun_keluar',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'id_santri', 'id');
    }
}
