<?php

namespace App\Models;

use App\Models\Khadam;
use Illuminate\Support\Str;
use App\Models\PesertaDidik;
use App\Models\DomisiliSantri;
use App\Models\Catatan_afektif;
use App\Models\RiwayatDomisili;
use App\Models\Catatan_kognitif;
use App\Models\PengunjungMahrom;
use App\Models\RiwayatPendidikan;
use App\Models\Kewaliasuhan\Anak_asuh;
use App\Models\Kewaliasuhan\Wali_asuh;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Santri extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'santri';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function catatanAfektifLatest()
    {
        return $this->hasOne(Catatan_afektif::class, 'id_santri')
            ->latestOfMany('created_at');
    }

    public function catatanKognitifLatest()
    {
        return $this->hasOne(Catatan_kognitif::class, 'id_santri')
            ->latestOfMany('created_at');
    }

    public function riwayatDomisili()
    {
        return $this->hasMany(RiwayatDomisili::class, 'id_peserta_didik');
    }
    public function riwayatPendidikan()
    {
        return $this->hasMany(RiwayatPendidikan::class, 'id_peserta_didik');
    }
    public function kunjunganMahrom()
    {
        return $this->hasMany(PengunjungMahrom::class, 'id_santri');
    }
    public function waliAsuh()
    {
        return $this->hasMany(Wali_asuh::class, 'id_santri');
    }
    public function anakAsuh()
    {
        return $this->hasMany(Anak_asuh::class, 'id_santri');
    }
    public function khadam()
    {
        return $this->hasOne(Khadam::class, 'id_biodata', 'id_peserta_didik');
    }

    // public function scopeActive($query)
    // {
    //     return $query->where('santri.status', 'aktif');
    // }

    // public function pesertaDidik()
    // {
    //     return $this->BelongsTo(PesertaDidik::class, 'id_peserta_didik', 'id');
    // }

    // public function domisiliSantri()
    // {
    //     return $this->BelongsTo(DomisiliSantri::class, 'id_santri', 'id');
    // }
}
