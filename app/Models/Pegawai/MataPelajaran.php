<?php

namespace App\Models\Pegawai;

use App\Models\Pendidikan\Lembaga;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;
    protected $table = 'mata_pelajaran';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $timestamps = true;

    public $incrementing = true;

    protected $guarded = [
        'id',
    ];
    public function pengajar()
    {
        return $this->belongsTo(Pengajar::class, 'pengajar_id');
    }

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class, 'lembaga_id');
    }

    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class, 'mata_pelajaran_id');
    }
}
