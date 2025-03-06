<?php

namespace App\Models\Pegawai;

use App\Models\Pendidikan\Lembaga;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengajar extends Model
{
    use HasFactory;

    protected $table = 'pengajar';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [
        'id'
    ];

    public function PengajarPegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id');
    }

    public function PengajarLembaga()
    {
        return $this->belongsTo(Lembaga::class, 'id_lembaga','id');
    }

    public function PengajarWaliKelas()
    {
        return $this->hasOne(WaliKelas::class,'id_pengajar','id');
    }

    public function PengajarGolongan()
    {
        return $this->belongsTo(Golongan::class,'id_golongan','id');
    }
}
