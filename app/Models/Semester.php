<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semester';

    protected $fillable = [
        'tahun_ajaran_id',
        'semester',
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
