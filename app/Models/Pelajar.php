<?php

namespace App\Models;

use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Rombel;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Lembaga;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pelajar extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'pelajar';
    protected $fillable = [
        'id_peserta_didik',
        'id_lembaga',
        'id_jurusan',
        'id_kelas',
        'id_rombel',
        'no_induk',
        'tanggal_masuk',
        'tanggal_keluar',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function scopeActive($query)
    {
        return $query->where('pelajar.status', true);
    }

    public function pesertaDidik()
    {
        return $this->belongsTo(Peserta_didik::class, 'id_peserta_didik', 'id');
    }

    public function alumniPelajar()
    {
        return $this->hasMany(AlumniPelajar::class, 'id_pelajar', 'id');
    }

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class, 'id_lembaga', 'id');
    }
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan', 'id');
    }
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id');
    }
    public function rombel()
    {
        return $this->belongsTo(Rombel::class, 'id_rombel', 'id');
    }
}
