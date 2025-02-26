<?php

namespace App\Models;

use App\Models\Alamat\Desa;
use App\Models\Pegawai\Pegawai;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Biodata extends Model
{
    use SoftDeletes;
    protected $table = 'biodata';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    protected $fillable = [
        'id_desa',
        'nama',
        'niup',
        'jenis_kelamin',
        'tanggal_lahir',
        'tempat_lahir',
        'nik',
        'no_kk',
        'no_telepon',
        'email',
        'jenjang_pendidikan_terakhir',
        'nama_pendidikan_terakhir',
        'image_url',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
        
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function peserta_didik()
    {
        return $this->hasOne(Peserta_didik::class, 'id_biodata', 'id');
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa', 'id');
    }

    public function BiodataPegawai()
    {
        return $this->hasMany(Pegawai::class,'id_biodata', 'id');
    }

    // public function keluarga() {
    //     return $this->hasMany(Keluarga::class, 'no_kk', 'no_kk');
    // }
}
