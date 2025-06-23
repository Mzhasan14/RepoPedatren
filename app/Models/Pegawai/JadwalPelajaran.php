<?php

namespace App\Models\Pegawai;

use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Lembaga;
use App\Models\Pendidikan\Rombel;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{
    use HasFactory;

    protected $table = 'jadwal_pelajaran';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $timestamps = true;

    public $incrementing = true;

    protected $guarded = [
        'id',
    ];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }
    public function jamPelajaran()
    {
        return $this->belongsTo(JamPelajaran::class, 'jam_pelajaran_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }
    public function rombel()
    {
        return $this->belongsTo(Rombel::class, 'rombel_id');
    }
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
