<?php

namespace App\Models\Pendidikan;

use App\Models\Pelajar;
use App\Models\PesertaDidik;
use App\Models\Peserta_didik;
use App\Models\Pegawai\Pengajar;
use App\Models\RiwayatPendidikan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lembaga extends Model
{
    use HasFactory;

    use SoftDeletes;
    protected $table = 'lembaga';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'nama_lembaga',
        'created_by',
        'updated_by',
        'deleted_by',
        'status',
    ];

    public function RiwayatPendidikan()
    {
        $this->hasMany(RiwayatPendidikan::class, 'id_lembaga', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function pelajar()
    {
        $this->hasMany(Pelajar::class, 'id_lembaga', 'id');
    }

    public function jurusan()
    {
        return $this->hasMany(Jurusan::class, 'id_lembaga', 'id');
    }

    public function pengajar()
    {
        return $this->hasMany(Pengajar::class, 'id_lembaga','id');
    }
    
}
