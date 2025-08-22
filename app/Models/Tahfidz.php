<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tahfidz extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tahfidz';
    protected $fillable = [
        'santri_id',
        'tahun_ajaran_id',
        'tanggal',
        'jenis_setoran',
        'surat',
        'ayat_mulai',
        'ayat_selesai',
        'juz_mulai',
        'juz_selesai',
        'nilai',
        'catatan',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Relasi
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
