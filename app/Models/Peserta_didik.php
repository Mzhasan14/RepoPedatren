<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Kewilayahan\Domisili;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\Kewaliasuhan\Anak_asuh;
class Peserta_didik extends Model
{
    use SoftDeletes;
    protected $table = 'peserta_didik';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    protected $fillable = [
        'id_biodata',
        'id_domisili',
        'nis',
        'anak_keberapa',
        'dari_saudara',
        'tinggal_bersama',
        'smartcard',
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
        return $query->where('status', true);
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

    public function KhadamSantri()
    {
        return $this->hasOne(Khadam::class,'id_peserta_didik', 'id');
    }
    public function SantriPelanggaran()
    {
        return $this->hasMany(Pelanggaran::class,'id_peserta_didik', 'id');
    }
    public function SantriPerizinan()
    {
        return $this->hasMany(Perizinan::class, 'id_peserta_didik', 'id');
    }

    public function rencana_pendidikan()
    {
        return $this->hasMany(Rencana_pendidikan::class, 'id_peserta_didik', 'id');
    }
    
}
