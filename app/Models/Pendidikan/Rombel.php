<?php

namespace App\Models\Pendidikan;
use App\Models\PesertaDidik;
use App\Models\Peserta_didik;
use App\Models\RiwayatPendidikan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rombel extends Model
{
    use HasFactory;

    use SoftDeletes;
    protected $table = 'rombel';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'nama_rombel',
        'id_kelas',
        'created_by',
        'updated_by',
        'deleted_by',
        'status',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function RiwayatPendidikan()
    {
        $this->hasMany(RiwayatPendidikan::class, 'id_lembaga', 'id');
    }

    public function kelas()
    {
        return $this->belongsTo(Lembaga::class, 'id_kelas', 'id');
    }
}
