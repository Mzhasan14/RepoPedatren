<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Model;

class WaliKelas extends Model
{
    protected $table = 'wali_kelas';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [
        'id'
    ];

    public function WaliKelasPengajar()
    {
        return $this->belongsTo(Pengajar::class,'id_pengajar','id');
    }
}
