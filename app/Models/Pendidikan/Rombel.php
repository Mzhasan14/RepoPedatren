<?php

namespace App\Models\Pendidikan;

use App\Models\RiwayatPendidikan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rombel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rombel';

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
    //     $this->hasMany(RiwayatPendidikan::class, 'lembaga_id', 'id');
    // }

    // public function kelas()
    // {
    //     return $this->belongsTo(Lembaga::class, 'kelas_id', 'id');
    // }
}
