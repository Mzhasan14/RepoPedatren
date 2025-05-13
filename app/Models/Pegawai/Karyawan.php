<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawan';
    protected $keyType = 'string';


    protected $guarded = [
        'created_at'
    ];
    public function ScopeActive($query)
    {
        return $query->where('karyawan.status_aktif','aktif');
    }
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}
