<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisBerkas extends Model
{
    use HasFactory;

    protected $table = 'jenis_berkas';

    protected $fillable = [
        'nama_jenis_berkas',
        'wajib',
        'created_by',
        'updated_by',
        'deleted_by',
        'status',
    ];

    public function berkas()
    {
        return $this->hasMany(Berkas::class, 'jenis_berkas_id', 'id');
    }
}
