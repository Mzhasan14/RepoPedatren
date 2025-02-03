<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Peserta_didik extends Model
{
    use SoftDeletes;
    protected $table = 'peserta_didik';

    protected $fillable = [
        'id_biodata',
        'nis',
        'anak_keberapa',
        'dari_saudara',
        'tinggal_bersama',
        'smartcard',
        'tahun_masuk',
        'tahun_keluar',
        'status',
        'created_by'
    ];

    // protected $primaryKey = 'id_peserta_didik';

    public function scopeSantri($query)
    {
        return $query->whereNotNull('nis')->where('nis', '!=', '');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
