<?php

namespace App\Models\Pendidikan;

use App\Models\Pendidikan;
use App\Models\RiwayatPendidikan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jurusan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'jurusan';

    protected $guarded = ['id'];

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class, 'lembaga_id', 'id');
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'jurusan_id', 'id');
    }

    public function pendidikan()
    {
        return $this->hasMany(Pendidikan::class);
    }
}
