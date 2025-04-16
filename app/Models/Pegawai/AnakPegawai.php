<?php

namespace App\Models\Pegawai;

use App\Models\Peserta_didik;
use App\Models\PesertaDidik;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnakPegawai extends Model
{
    use HasFactory;

    protected $table = 'anak_pegawai';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [
        'created_at'
    ];

    public function AnakpegawaiPesertaDidik()
    {
        return $this->belongsTo(PesertaDidik::class,'id_peserta_didik','id');
    }
    public function anakPegawai()
    {
        return $this->belongsTo(Pegawai::class,'id_pegawai','id');
    }
    public function ScopeActive($query)
    {
        return $query->where('anak_pegawai.status',true);
    }
}
