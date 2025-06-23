<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JamPelajaran extends Model
{
    use HasFactory;
    protected $table = 'jam_pelajaran';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $timestamps = true;

    public $incrementing = true;

    protected $guarded = [
        'id',
    ];
    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class, 'jam_pelajaran_id');
    }
}
