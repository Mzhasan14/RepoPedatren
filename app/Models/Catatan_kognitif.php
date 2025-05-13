<?php

namespace App\Models;

use App\Models\Kewaliasuhan\Wali_asuh;
use Illuminate\Database\Eloquent\Model;

class Catatan_kognitif extends Model
{
    protected $table = 'catatan_kognitif';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [
        'id'
    ];


    public function ScopeActive($query)
    {
        return $query->where('catatan_kognitif.status',true);
    }
    public function santri()
    {
        return $this->belongsTo(Santri::class, 'id_santri');
    }

    public function waliAsuh()
    {
        return $this->belongsTo(Wali_asuh::class, 'id_wali_asuh');
    }
}
