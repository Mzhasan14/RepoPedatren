<?php

namespace App\Models\Pegawai;

use App\Models\Peserta_didik;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnakPegawai extends Model
{
    use HasFactory;

    protected $table = 'anak_pegawai';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [
        'id'
    ];

    public function AnakpegawaiPesertaDidik()
    {
        return $this->belongsTo(Peserta_didik::class,'id_peserta_didik','id');
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
