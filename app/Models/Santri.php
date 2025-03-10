<?php

namespace App\Models;

use App\Models\Kewilayahan\Domisili;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Santri extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'santri';
    protected $fillable = [
        'id_peserta_didik',
        'id_wilayah',
        'id_blok',
        'id_kamar',
        'id_domisili',
        'nis',
        'tahun_masuk',
        'tahun_keluar',
        'status',
        'created_by',
        'updated_by'
    ];

    public function scopeActive($query)
    {
        return $query->where('santri.status', true);
    }

    public function pesertaDidik()
    {
        return $this->BelongsTo(Peserta_didik::class, 'id_peserta_didik', 'id');
    }
    public function domisili()
    {
        return $this->BelongsTo(Domisili::class, 'id_domisili', 'id');
    }
}
