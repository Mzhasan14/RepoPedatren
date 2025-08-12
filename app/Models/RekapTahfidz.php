<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class RekapTahfidz extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rekap_tahfidz';
    protected $fillable = [
        'santri_id',
        'tahun_ajaran_id',
        'total_surat',
        'persentase_khatam',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Relasi
    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
