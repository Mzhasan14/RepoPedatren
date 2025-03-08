<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Golongan extends Model
{
    use HasFactory;

    protected $table = 'golongan';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [
        'id'
    ];
    public function kategoriGolongan()
    {
        return $this->belongsTo(Golongan::class,'id_kategori_golongan','id');
    }
    public function golongan()
    {
        return $this->hasMany(Golongan::class,'id_golongan','id');
    }
}
