<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Model;

class Golongan extends Model
{

    protected $table = 'golongan';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [
        'id'
    ];
    public function KategoriGolonganGolongan()
    {
        return $this->belongsTo(Golongan::class,'id_kategori_golongan','id');
    }
    public function GolonganPengajar()
    {
        return $this->hasMany(Golongan::class,'id_golongan','id');
    }
}
