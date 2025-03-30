<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatJabatanKaryawan extends Model
{
    use HasFactory;

    protected $table = 'riwayat_jabatan_karyawan';
    public $incrementing = false;
    protected $keyType = 'string';


    protected $guarded = [
        'created_at'
    ];
}
