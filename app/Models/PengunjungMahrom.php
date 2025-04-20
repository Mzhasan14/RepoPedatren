<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengunjungMahrom extends Model
{
    protected $table = 'pengunjung_mahrom';
    protected $guarded = ['id'];
    public function santri()
    {
        return $this->belongsTo(Santri::class, 'id_santri');
    }
}
