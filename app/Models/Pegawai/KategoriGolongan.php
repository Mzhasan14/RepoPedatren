<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriGolongan extends Model
{
    use HasFactory;

    protected $table = 'kategori_golongan';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [
        'id'
    ];

    public function KategoriGolonganGolongan()
    {
        return $this->hasMany(Golongan::class,'id_kategori_golongan','id');
    }
}
