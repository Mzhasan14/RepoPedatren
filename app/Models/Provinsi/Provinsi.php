<?php

namespace App\Models\Provinsi;

use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
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
}
