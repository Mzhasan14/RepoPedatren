<?php

namespace App\Models;

use App\Models\Kewilayahan\Blok;
use App\Models\Kewilayahan\Kamar;
use App\Models\Kewilayahan\Wilayah;
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
        'tanggal_masuk',
        'tanggal_keluar',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function scopeActive($query)
    {
        return $query->where('santri.status', true);
    }

    public function pesertaDidik()
    {
        return $this->BelongsTo(Peserta_didik::class, 'id_peserta_didik', 'id');
    }
    public function wilayah()
    {
        return $this->BelongsTo(Wilayah::class, 'id_wilayah', 'id');
    }
    public function blok()
    {
        return $this->BelongsTo(Blok::class, 'id_blok', 'id');
    }
    public function kamar()
    {
        return $this->BelongsTo(Kamar::class, 'id_kamar', 'id');
    }
    public function domisili()
    {
        return $this->BelongsTo(Domisili::class, 'id_domisili', 'id');
    }
}
