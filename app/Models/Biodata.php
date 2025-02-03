<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Biodata extends Model
{
    use SoftDeletes;
    protected $table = 'biodata';
    protected $fillable = [
        'id_desa',
        'nama',
        'niup',
        'jenis_kelamin',
        'tanggal_lahir',
        'tempat_lahir',
        'nik',
        'no_kk',
        'no_telepon',
        'email',
        'jenjang_pendidikan_terakhir',
        'nama_pendidikan_terakhir',
        'status',
        'created_by',
        'updated_by',
    ];
    
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
