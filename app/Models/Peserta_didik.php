<?php

namespace App\Models;

use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Rombel;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Lembaga;
use App\Models\Kewilayahan\Domisili;
use App\Models\Kewaliasuhan\Anak_asuh;
use App\Models\Kewaliasuhan\Wali_asuh;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Peserta_didik extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'peserta_didik';
    protected $fillable = [
        'id_biodata',
        'status',
        'created_by',
        'updated_by'
    ];

    public function scopeSantri($query)
    {
        return $query->whereNotNull('santri.id');
    }

    public function scopeActive($query)
    {
        return $query->where('peserta_didik.status', true);
    }

    public function scopeNonActive($query)
    {
        return $query->where('peserta_didik.status', false);
    }

    public function biodata()
    {
        return $this->belongsTo(Biodata::class, 'id_biodata', 'id');
    }

    public function pelajar() {
        return $this->hasOne(Pelajar::class,'id_peserta_didik','id');
    }

    public function santri() {
        return $this->hasOne(Santri::class,'id_santri','id');
    }

    public function waliAsuh() {
        return $this->hasOne(Wali_asuh::class,'nis','nis');
    }

    public function anakAsuh() {
        return $this->hasOne(Anak_asuh::class,'nis','nis');
    }

    public function pelanggaran()
    {
        return $this->hasMany(Pelanggaran::class,'id_peserta_didik', 'id');
    }

    public function perizinan()
    {
        return $this->hasMany(Perizinan::class, 'id_peserta_didik', 'id');
    }

    public function catatanKognitif()
    {
        return $this->hasMany(Catatan_kognitif::class,'id_peserta_didik','id');
    }

    public function catatanAfektif()
    {
        return $this->hasMany(Catatan_afektif::class,'id_peserta_didik','id');
    }
    
}
