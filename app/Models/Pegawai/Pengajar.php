<?php

namespace App\Models\Pegawai;

use App\Models\Biodata;
use App\Models\Pendidikan\Lembaga;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pengajar extends Model
{
    use HasFactory;

    protected $table = 'pengajar';
    protected $guarded = [
        'id'
    ];

    public function biodata()
    {
        return $this->hasOneThrough(Biodata::class, Pegawai::class, 'id', 'id', 'id', 'id');
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id');
    }

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class, 'id_lembaga','id');
    }

    public function waliKelas()
    {
        return $this->hasOne(WaliKelas::class,'id_pengajar','id');
    }

    public function golongan()
    {
        return $this->belongsTo(Golongan::class,'id_golongan','id');
    }
    public function ScopeActive($query)
    {
        return $query->where('pengajar.status',true);
    }
    public function MateriAjarPengajar()
    {
        return $this->belongsTo(MateriAjar::class,'id_pengajar','id');
    }
}
