<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pelajar extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'pelajar';
    public $incrementing = false;
    protected $keyType = 'string';
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

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function scopeActive($query)
    {
        return $query->where('pelajar.status_pelajar', 'aktif');
    }

    public function pesertaDidik()
    {
        return $this->belongsTo(PesertaDidik::class, 'id_peserta_didik', 'id');
    }

    public function pendidikanPelajar()
    {
        return $this->hasMany(PendidikanPelajar::class, 'id_pelajar', 'id');
    }
}
