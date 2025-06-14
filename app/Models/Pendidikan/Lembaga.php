<?php

namespace App\Models\Pendidikan;

use App\Models\Pegawai\Pengajar;
use App\Models\RiwayatPendidikan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lembaga extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'lembaga';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $guarded = ['id'];

    // public function RiwayatPendidikan()
    // {
    //     $this->hasMany(RiwayatPendidikan::class, 'id_lembaga', 'id');
    // }

    // public function scopeActive($query)
    // {
    //     return $query->where('status', true);
    // }

    // public function jurusan()
    // {
    //     return $this->hasMany(Jurusan::class, 'id_lembaga', 'id');
    // }

    // public function pengajar()
    // {
    //     return $this->hasMany(Pengajar::class, 'id_lembaga','id');
    // }
    public function pengajar()
    {
        return $this->hasMany(Pengajar::class);
    }
}
