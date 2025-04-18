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

    public function PesertaDidikCatatanAfektif()
    {
        return $this->belongsTo(PesertaDidik::class,'id_peserta_didik','id');
    }
    public function WaliAsuhCatatanAfektif()
    {
        return $this->belongsTo(Wali_asuh::class,'id_wali_asuh','id');
    }
    public function ScopeActive($query)
    {
        return $query->where('catatan_afektif.status',true);
    }
}
