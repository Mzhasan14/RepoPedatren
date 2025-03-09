<?php

namespace App\Models\Pendidikan;
use App\Models\Peserta_didik;
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

    protected $fillable = [
        'nama_kelas',
        'id_jurusan',
        'created_by',
        'updated_by',
        'deleted_by',
        'status',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function peserta_didik()
    {
        $this->hasMany(Peserta_didik::class, 'id_kelas', 'id');
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan', 'id');
    }

    public function rombel()
    {
        return $this->hasMany(Rombel::class, 'id_kelas', 'id');
    }
}
