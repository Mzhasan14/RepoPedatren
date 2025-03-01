<?php

namespace App\Models\Pendidikan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rombel extends Model
{
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

    public function kelas()
    {
        return $this->belongsTo(Lembaga::class, 'id_kelas', 'id');
    }
}
