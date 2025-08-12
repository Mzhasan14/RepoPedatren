<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nadhoman extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nadhoman';
    protected $fillable = [
        'santri_id',
        'kitab_id',
        'tahun_ajaran_id',
        'tanggal',
        'jenis_setoran',
        'bait_mulai',
        'bait_selesai',
        'nilai',
        'catatan',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Relasi
    public function kitab()
    {
        return $this->belongsTo(Kitab::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
