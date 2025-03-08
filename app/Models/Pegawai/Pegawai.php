<?php

namespace App\Models\Pegawai;

use App\Models\Biodata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawai';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [
        'id'
    ];

    public function biodata()
    {
        return $this->belongsTo(Biodata::class,'id_biodata', 'id');
    }

    public function pengajar()
    {
        return $this->hasMany(Pengajar::class,'id_pegawai', 'id');
    }

    public function entitasPegawai()
    {
        return $this->hasMany(EntitasPegawai::class,'id_pegawai','id');
    }
}
