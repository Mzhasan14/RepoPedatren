<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Model;

class JenisBerkas extends Model
{
    protected $table = 'jenis_berkas';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [
        'id'
    ];

    public function JenisBerkas()
    {
        return $this->hasMany(Berkas::class,'id_jenis_berkas','id');
    }
}
