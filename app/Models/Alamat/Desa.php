<?php

namespace App\Models\Alamat;

use App\Models\Biodata;
use Illuminate\Database\Eloquent\Model;

class Desa extends Model
{
    protected $table = 'desa';
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

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'id_kecamatan', 'id');
    }
    public function biodata()
    {
        return $this->hasMany(Biodata::class,'id_desa', 'id');
    }
}
