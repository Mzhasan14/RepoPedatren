<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPengurus extends Model
{
    use HasFactory;

    protected $table = 'riwayat_jabatan_pengurus';
    public $incrementing = false;
    protected $keyType = 'string';


    protected $guarded = [
        'created_at'
    ];
}
