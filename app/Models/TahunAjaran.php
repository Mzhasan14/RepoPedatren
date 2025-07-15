<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $fillable = [
        'tahun_ajaran',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];
}
