<?php

namespace App\Models\Pegawai;

use App\Models\Biodata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawai';


    protected $guarded = [
        'id'
    ];


    public function entitasPegawai()
    {
        return $this->hasMany(EntitasPegawai::class,'id_pegawai','id');
    }
    public function anakPegawai()
    {
        return $this->hasMany(AnakPegawai::class,'id_pegawai','id');
    }
    public function ScopeActive($query)
    {
        return $query->where('pegawai.status_aktif','aktif');
    }

    public function karyawan()
    {
        return $this->hasMany(Karyawan::class);
    }
    public function pengajar()
    {
        return $this->hasMany(Pengajar::class);
    }

    public function biodata()
    {
        return $this->belongsTo(Biodata::class);
    }
        public function pengurus()
    {
        return $this->hasMany(Pengurus::class);
    }


}
