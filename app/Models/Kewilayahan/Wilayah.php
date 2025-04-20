<?php

namespace App\Models\Kewilayahan;

use App\Models\Kewaliasuhan\Grup_WaliAsuh;
use App\Models\RiwayatDomisili;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wilayah extends Model
{
    use HasFactory;

    use SoftDeletes;
    protected $table = 'wilayah';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'nama_wilayah',
        'created_by',
        'updated_by',
        'deleted_by',
        'status',
    ];

    public function RiwayatDomisili()
    {
        $this->hasMany(RiwayatDomisili::class, 'id_wilayah', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function blok()
    {
        return $this->hasMany(Blok::class, 'id_wilayah', 'id');
    }

    public function grupKewaliasuhan() {
        return $this->hasMany(Grup_WaliAsuh::class,'id_wilayah','id');
    }
}
