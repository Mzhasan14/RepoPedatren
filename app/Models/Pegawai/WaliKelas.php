<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaliKelas extends Model
{
    use HasFactory;

    protected $table = 'wali_kelas';
    public $incrementing = false;
    protected $keyType = 'string';


    protected $guarded = [
        'created_at'
    ];

    public function WaliKelasPengajar()
    {
        return $this->belongsTo(Pengajar::class,'id_pengajar','id');
    }

    public function ScopeActive($query)
    {
        return $query->where('wali_kelas.status_aktif','aktif');
    }
}
