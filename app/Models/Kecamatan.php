<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    protected $table = 'kecamatan';
    protected $primaryKey = 'id';
    public $timestamps = true;
    public $incrementing =true;

    protected $guarded =[
        'id'
    ];

    public function scopeActive($query)
    {
        return $query->where('status',1);
    }

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class,'id_kabupaten','id');
    }


    public function desa()
    {
        return $this->hasMany(Desa::class, 'id_kecamatan', 'id');
    }
}
