<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Santri extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'santri';
    public $incrementing = false;
    protected $keyType = 'string';
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

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function scopeActive($query)
    {
        return $query->where('santri.status_santri', 'aktif');
    }

    public function pesertaDidik()
    {
        return $this->BelongsTo(PesertaDidik::class, 'id_peserta_didik', 'id');
    }

    public function domisiliSantri()
    {
        return $this->BelongsTo(DomisiliSantri::class, 'id_santri', 'id');
    }
}
