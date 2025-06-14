<?php

namespace App\Models\Pendidikan;

use App\Models\RiwayatPendidikan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jurusan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'jurusan';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $guarded = ['id'];

    // public function scopeActive($query)
    // {
    //     return $query->where('status', true);
    // }

    // public function RiwayatPendidikan()
    // {
    //     $this->hasMany(RiwayatPendidikan::class, 'id_lembaga', 'id');
    // }
    // public function lembaga()
    // {
    //     return $this->belongsTo(Lembaga::class, 'id_lembaga', 'id');
    // }

    // public function kelas()
    // {
    //     return $this->hasMany(Kelas::class, 'id_jurusan', 'id');
    // }
}
