<?php

namespace App\Models\Alamat;

use Illuminate\Database\Eloquent\Model;

class Negara extends Model
{
    protected $table = 'negara';
    protected $fillable = [
        'nama_negara',
        'created_by',
        'updated_by',
        'deleted_by',
        'status'
    ];

    public function provinsi()
    {
        return $this->hasMany(Provinsi::class, 'id_negara', 'id');
    }
}
