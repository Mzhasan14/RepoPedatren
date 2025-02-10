<?php

namespace App\Models\Pendidikan;

use App\Models\Pegawai\Pengajar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lembaga extends Model
{
    use SoftDeletes;
    protected $table = 'lembaga';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'nama_lembaga',
        'created_by',
        'updated_by',
        'status',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function jurusan()
    {
        return $this->hasMany(Jurusan::class, 'id_lembaga', 'id');
    }

    public function LembagaPengajar()
    {
        return $this->hasMany(Pengajar::class, 'id_lembaga','id');
    }
    
}
