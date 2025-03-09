<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengurus extends Model
{
    use HasFactory;

    protected $table = 'pengurus';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [
        'id'
    ];
    
    public function scopeActive($query)
    {
        return $query->where('pengurus.status',true);
    }
    public function PengurusGolongan()
    {
        return $this->belongsTo(Golongan::class,'id_golongan','id');
    }
    public function PengurusPegawai()
    {
        return $this->belongsTo(Pegawai::class,'id_pegawai','id');
    }


}
