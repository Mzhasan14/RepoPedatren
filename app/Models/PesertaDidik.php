<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Models\Kewilayahan\Wilayah;
use App\Models\Pegawai\AnakPegawai;
use App\Models\Kewaliasuhan\Anak_asuh;
use App\Models\Kewaliasuhan\Wali_asuh;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PesertaDidik extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'peserta_didik';
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

    public function biodata()
    {
        return $this->belongsTo(Biodata::class, 'id_biodata', 'id');
    }

    public function santri()
    {
        return $this->hasOne(Santri::class, 'id_peserta_didik', 'id');
    }


    // public function biodata()
    // {
    //     return $this->belongsTo(Biodata::class, 'id_biodata');
    // }
    // public function activePelajar()
    // {
    //     return $this->hasOne(Pelajar::class, 'id_peserta_didik')->where('status', 'aktif');
    // }
    // public function activeRiwayatPendidikan()
    // {
    //     return $this->hasOne(RiwayatPendidikan::class, 'id_peserta_didik')->where('status', 'aktif');
    // }

    // public function activeSantri()
    // {
    //     return $this->hasOne(Santri::class, 'id_peserta_didik')->where('status', 'aktif');
    // }

    // // Relasi ke wilayah melalui domisili
    // public function wilayah()
    // {
    //     return $this->belongsToThrough(Wilayah::class, RiwayatDomisili::class, 'id_biodata');
    // }
    // public function activeRiwayatDomisili()
    // {
    //     return $this->hasOne(RiwayatDomisili::class, 'id_peserta_didik')->where('status', 'aktif');
    // }
    // public function latestWargaPesantren()
    // {
    //     return $this->hasOne(WargaPesantren::class, 'id_biodata', 'id_biodata')->where('status', true)->latestOfMany();
    // }
    // public function latestPasFoto()
    // {
    //     return $this->hasOne(Berkas::class, 'id_biodata', 'id_biodata')->whereHas('jenisBerkas', fn($q) => $q->where('nama_jenis_berkas', 'Pas foto'))->latestOfMany();
    // }


    // public function scopeActive($query)
    // {
    //     return $query->where('peserta_didik.status', true);
    // }

    // public function biodata()
    // {
    //     return $this->belongsTo(Biodata::class, 'id_biodata', 'id');
    // }

    // public function santri()
    // {
    //     return $this->hasOne(Santri::class, 'id_peserta_didik', 'id');
    // }

    // public function waliAsuh()
    // {
    //     return $this->hasOne(Wali_asuh::class, 'id_peserta_didik', 'id');
    // }

    // public function anakAsuh()
    // {
    //     return $this->hasOne(Anak_asuh::class, 'id_peserta_didik', 'id');
    // }

    // public function pelanggaran()
    // {
    //     return $this->hasMany(Pelanggaran::class, 'id_peserta_didik', 'id');
    // }

    // public function perizinan()
    // {
    //     return $this->hasMany(Perizinan::class, 'id_peserta_didik', 'id');
    // }

    // public function catatanKognitif()
    // {
    //     return $this->hasMany(Catatan_kognitif::class, 'id_peserta_didik', 'id');
    // }

    // public function catatanAfektif()
    // {
    //     return $this->hasMany(Catatan_afektif::class, 'id_peserta_didik', 'id');
    // }

    // public function santriAktif()
    // {
    //     return $this->hasOne(Santri::class, 'id_peserta_didik', 'id')->where('status', 'aktif');
    // }
    // public function AnakpegawaiPesertaDidik()
    // {
    //     return $this->hasMany(AnakPegawai::class, 'id_peserta_didik', 'id');
    // }


}
