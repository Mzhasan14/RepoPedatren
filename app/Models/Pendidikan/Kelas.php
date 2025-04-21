<?php

namespace App\Models\Pendidikan;
use App\Models\PesertaDidik;
use App\Models\Peserta_didik;
use App\Models\RiwayatPendidikan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
