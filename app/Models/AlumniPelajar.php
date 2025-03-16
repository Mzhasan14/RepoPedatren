<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniPelajar extends Model
{
    protected $table = 'alumni_pelajar';
    protected $fillable = [
        'id_pelajar',
        'id_lembaga',
        'id_jurusan',
        'id_kelas',
        'id_rombel',
        'status_alumni',
        'tahun_keluar',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function pelajar()
    {
        return $this->belongsTo(Pelajar::class, 'id_pelajar', 'id');
    }

}
