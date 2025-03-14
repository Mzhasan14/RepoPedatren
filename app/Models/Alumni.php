<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumni extends Model
{
    protected $table = 'alumni';
    protected $fillable = [
        'id_pelajar',
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

    public function scopeActive($query)
    {
        return $query->where('peserta_didik.status', true);
    }
}
