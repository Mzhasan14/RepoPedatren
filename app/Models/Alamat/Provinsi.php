<?php

namespace App\Models\Alamat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    use HasFactory;

    protected $table = 'provinsi';
    protected $primaryKey = 'id';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [
        'id'
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
