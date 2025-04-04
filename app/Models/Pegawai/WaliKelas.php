<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaliKelas extends Model
{
    use HasFactory;

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

    public function ScopeActive($query)
    {
        return $query->where('wali_kelas.status',true);
    }
}
