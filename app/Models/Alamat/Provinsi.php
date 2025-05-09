<?php

namespace App\Models\Alamat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    use HasFactory;

    protected $table = 'provinsi';

    protected $fillable = [
        'negara_id',
        'nama_provinsi',
        'created_by',
        'updated_by',
        'deleted_by',
        'status'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function kabupaten() {
        return $this->hasMany(Kabupaten::class,'id_provinsi', 'id');
    }

    public function negara()
    {
        return $this->belongsTo(Negara::class, 'id_negara', 'id');
    }
}
