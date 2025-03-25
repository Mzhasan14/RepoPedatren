<?php

namespace App\Models;

use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Rombel;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Lembaga;
use App\Observers\PelajarObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pelajar extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'pelajar';
    protected $fillable = [
        'id_peserta_didik',
        'no_induk',
        'angkatan_pelajar',
        'tanggal_masuk_pelajar',
        'tanggal_keluar_pelajar',
        'status_pelajar',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function scopeActive($query)
    {
        return $query->where('pelajar.status_pelajar', 'aktif');
    }

    public function pesertaDidik()
    {
        return $this->belongsTo(Peserta_didik::class, 'id_peserta_didik', 'id');
    }
}
