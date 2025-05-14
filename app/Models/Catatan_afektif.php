<?php

namespace App\Models;

use App\Http\Controllers\api\kewaliasuhan\WaliasuhController;
use App\Models\Kewaliasuhan\Wali_asuh;
use Illuminate\Database\Eloquent\Model;

class Catatan_afektif extends Model
{
    protected $table = 'catatan_afektif';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [
        'id'
    ];

    public function waliAsuh()
    {
        return $this->belongsTo(Wali_asuh::class, 'id_wali_asuh');
    }
    public function ScopeActive($query)
    {
        return $query->where('catatan_afektif.status',true);
    }
    public function santri()
    {
        return $this->belongsTo(Santri::class,'id_santri');
    }
}
