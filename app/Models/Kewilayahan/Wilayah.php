<?php

namespace App\Models\Kewilayahan;

use App\Models\DomisiliSantri;
use App\Models\RiwayatDomisili;
use Illuminate\Database\Eloquent\Model;
use App\Models\Kewaliasuhan\Grup_WaliAsuh;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wilayah extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'wilayah';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $guarded = ['id'];

    public function blok()
    {
        return $this->hasMany(Blok::class, 'wilayah_id', 'id');
    }

    public function grupKewaliasuhan()
    {
        return $this->hasMany(Grup_WaliAsuh::class, 'id_wilayah', 'id');
    }

    public function domisiliSantri()
    {
        return $this->hasMany(DomisiliSantri::class, 'wilayah_id');
    }
}
