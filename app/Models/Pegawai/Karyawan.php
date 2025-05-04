<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawan';
    public $incrementing = false;
    protected $keyType = 'string';


    protected $guarded = [
        'created_at'
    ];
    public function ScopeActive($query)
    {
        return $query->where('karyawan.status_aktif','aktif');
    }
}
