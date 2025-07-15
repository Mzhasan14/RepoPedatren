<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Angkatan extends Model
{
    protected $table = 'angkatan';

    protected $fillable = [
        'angkatan',
        'kategori',
        'tahun_ajaran_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }
}
