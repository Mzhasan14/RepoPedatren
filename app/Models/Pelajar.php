<?php

namespace App\Models;

use App\Models\Pendidikan\Rombel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        'tahun_masuk',
        'tahun_keluar',
        'status',
        'created_by',
        'updated_by'
    ];

    public function scopeActive($query)
    {
        return $query->where('pelajar.status', true);
    }

    public function pesertaDidik()
    {
        return $this->belongsTo(Peserta_didik::class, 'id_peserta_didik', 'id');
    }
    public function rombel()
    {
        return $this->belongsTo(Rombel::class, 'id_rombel', 'id');
    }
}
