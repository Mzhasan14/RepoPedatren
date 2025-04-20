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
    public function PesertaDidikCatatanKognitif()
    {
        return $this->belongsTo(PesertaDidik::class,'id_peserta_didik','id');
    }
    public function WaliAsuhCatatanKognitif()
    {
        return $this->belongsTo(Wali_asuh::class,'id_wali_asuh','id');
    }
}
