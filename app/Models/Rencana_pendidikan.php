<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rencana_pendidikan extends Model
{
    protected $table = 'rencana_pendidikan';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    protected $fillable = [
        'id_peserta_didik',
        'id_lembaga',
        'id_jurusan',
        'id_kelas',
        'id_rombel',
        'mondok',
        'jenis_pendaftaran',
        'alumni',
        'no_induk'
    ];

    public function peserta_didik()
    {
        return $this->belongsTo(Peserta_didik::class, 'id_peserta_didik', 'id');
    }


}
