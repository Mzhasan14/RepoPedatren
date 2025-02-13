<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Model;

class EntitasPegawai extends Model
{
    protected $table = 'entitas_pegawai';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [
        'id'
    ];

    public function EntitasPegawai()
    {
        return $this->belongsTo(Pegawai::class,'id_pegawai','id');
    }
}
