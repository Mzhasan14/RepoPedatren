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

    public function Pengajar()
    {
        return $this->belongsTo(Pengajar::class);
    }
}
