<?php

namespace App\Models\Pendidikan;

use App\Models\Pendidikan;
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

    protected $guarded = ['id'];

    public function jurusan()
    {
        return $this->hasMany(Jurusan::class, 'lembaga_id', 'id');
    }

    public function pendidikan()
    {
        return $this->hasMany(Pendidikan::class);
    }

    public function pengajar()
    {
        return $this->hasMany(Pengajar::class);
    }
}
