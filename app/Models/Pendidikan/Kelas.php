<?php

namespace App\Models\Pendidikan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kelas extends Model
{
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

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan', 'id');
    }

    public function rombel()
    {
        return $this->hasMany(Rombel::class, 'id_kelas', 'id');
    }
}
