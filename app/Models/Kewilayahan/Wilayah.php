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

    protected $guarded = ['id'];

    public function blok()
    {
        return $this->hasMany(Blok::class, 'wilayah_id', 'id');
    }

    public function grupKewaliasuhan()
    {
        return $this->hasMany(Grup_WaliAsuh::class, 'id_wilayah', 'id');
    }
}
