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
    use HasFactory;

    use SoftDeletes;
    protected $table = 'peserta_didik';
    protected $fillable = [
        'id_biodata',
        'id_domisili',
        'id_lembaga',
        'id_jurusan',
        'id_kelas',
        'id_rombel',
        'no_induk',
        'nis',
        'tahun_masuk',
        'tahun_keluar',
        'status',
        'created_by',
        'updated_by'
    ];


    public function scopeSantri($query)
    {
        return $query->whereNotNull('nis')->where('nis', '!=', '');
    }

    public function scopeActive($query)
    {
        return $query->where('peserta_didik.status', true);
    }

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class, 'id_lembaga', 'id');
    }
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan', 'id');
    }
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id');
    }
    public function rombel()
    {
        return $this->belongsTo(Rombel::class, 'id_rombel', 'id');
    }

    public function biodata()
    {
        return $this->belongsTo(Biodata::class, 'id_biodata', 'id');
    }

    public function domisili()
    {
        return $this->BelongsTo(Domisili::class, 'id_domisili', 'id');
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
