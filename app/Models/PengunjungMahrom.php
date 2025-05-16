<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengunjungMahrom extends Model
{
    use HasFactory;

    protected $table = 'pengunjung_mahrom';
    protected $guarded = ['id'];
    public function santri()
    {
        return $this->belongsTo(Santri::class, 'id_santri');
    }
}
