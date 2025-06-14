<?php

namespace App\Models\Pendidikan;

use App\Models\RiwayatPendidikan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kelas extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'kelas';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $guarded = ['id'];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    // public function RiwayatPendidikan()
    // {
    //     $this->hasMany(RiwayatPendidikan::class, 'id_lembaga', 'id');
    // }

    // public function jurusan()
    // {
    //     return $this->belongsTo(Jurusan::class, 'id_jurusan', 'id');
    // }

    // public function rombel()
    // {
    //     return $this->hasMany(Rombel::class, 'id_kelas', 'id');
    // }
}
