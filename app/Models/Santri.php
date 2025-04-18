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
        'tanggal_masuk',
        'tanggal_keluar',
        'status',
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
        return $query->where('santri.status', 'aktif');
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
