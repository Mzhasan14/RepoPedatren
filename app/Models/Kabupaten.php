<?php

namespace App\Models;

use App\Models\Provinsi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kabupaten extends Model
{
    //
    use SoftDeletes;

    protected $table = 'kabupaten';
    protected $primaryKey = 'id';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'id_provinsi',
        'nama_kabupaten',
        'created_by',
        'updated_by',
        'status'
    ];

    public function provinsi() {
        return $this->belongsTo(Provinsi::class,'id_provinsi','id');
    }

    public function kecamatan()
    {
        return $this->hasMany(Kecamatan::class,'id_kabupaten','id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

}
