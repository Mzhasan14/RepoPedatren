<?php

namespace App\Models;

use App\Models\Peserta_didik;
use App\Models\Kewilayahan\Blok;
use App\Models\Kewilayahan\Kamar;
use App\Observers\SantriObserver;
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
        'nis',
        'angkatan_santri',
        'tanggal_masuk_santri',
        'tanggal_keluar_santri',
        'status_santri',
        'created_by',
        'updated_by',
        'deleted_by'
    ];



    public function scopeActive($query)
    {
        return $query->where('santri.status_santri', 'aktif');
    }

    public function pesertaDidik()
    {
        return $this->BelongsTo(Peserta_didik::class, 'id_peserta_didik', 'id');
    }
}
