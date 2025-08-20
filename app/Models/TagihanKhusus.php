<?php

namespace App\Models;

use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Lembaga;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TagihanKhusus extends Model
{
    use SoftDeletes;

    protected $table = 'tagihan_khusus';

    protected $fillable = [
        'tagihan_id',
        'angkatan_id',
        'lembaga_id',
        'jurusan_id',
        'jenis_kelamin',
        'kategori_santri',
        'domisili',
        'kondisi_khusus',
        'nominal',
    ];

    /** RELASI */
    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function angkatan()
    {
        return $this->belongsTo(Angkatan::class);
    }

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }
}
