<?php

namespace App\Models\Kewilayahan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wilayah extends Model
{
    use SoftDeletes;
    protected $table = 'wilayah';
    protected $fillable = [
        'nama_wilayah',
        'created_by',
        'updated_by',
        'deleted_by',
        'status',
    ];
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function blok()
    {
        return $this->hasMany(Blok::class, 'id_wilayah', 'id');
    }
}
