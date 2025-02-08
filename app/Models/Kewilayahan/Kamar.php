<?php

namespace App\Models\Kewilayahan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kamar extends Model
{
    use SoftDeletes;
    protected $table = 'kamar';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    protected $fillable = [
        'nama_kamar',
        'id_blok',
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
        return $this->belongsTo(Blok::class, 'id_blok', 'id');
    }

    public function domisili()
    {
        return $this->hasMany(Domisili::class, 'id_kamar', 'id');
    }
}
