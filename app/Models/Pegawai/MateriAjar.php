<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriAjar extends Model
{
    use HasFactory;

    protected $table = 'materi_ajar';

    protected $guarded = [
        'id'
    ];

    public function MateriAjarPengajar()
    {
        return $this->belongsTo(Pengajar::class,'id_pengajar','id');
    }
}
