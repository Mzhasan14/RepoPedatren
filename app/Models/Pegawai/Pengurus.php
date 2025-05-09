<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengurus extends Model
{
    use HasFactory;

    protected $table = 'pengurus';

    protected $guarded = [
        'created_at'
    ];
    
    public function scopeActive($query)
    {
        return $query->where('pengurus.status_aktif','aktif');
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
