<?php

namespace App\Models\wilayah;

use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blok extends Model
{
    use SoftDeletes;
    protected $table = 'blok';
    protected $fillable = [
        'nama_blok',
        'id_wilayah',
        'created_by',
        'status',
    ];
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'id_wilayah', 'id');
    }

    public function kamar()
    {
        return $this->hasMany(Kamar::class, 'id_blok', 'id');
    }
    
}
