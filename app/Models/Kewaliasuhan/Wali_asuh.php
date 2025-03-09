<?php

namespace App\Models\Kewaliasuhan;

use App\Models\Catatan_afektif;
use App\Models\Catatan_kognitif;
use App\Models\Perizinan;
use App\Models\Peserta_didik;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wali_asuh extends Model
{
    use HasFactory;

    use SoftDeletes;
    //
    protected $table = 'wali_asuh';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'nis',
        'id_grup_wali_asuh',
        'created_by',
        'updated_by',
        'status'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function pesertaDidik() {
        return $this->belongsTo(Peserta_didik::class,'id_peserta_didik','id');
    }

    public function grupWaliAsuh() {
        return $this->belongsTo(Grup_WaliAsuh::class,'id_grup_wali_asuh','id');
    }

    public function WaliAsuhPesrizinan()
    {
        return $this->hasOne(Perizinan::class,'id_wali_asuh', 'id');
    }
    public function WaliAsuhCatatanKognitif()
    {
        return $this->belongsTo(Catatan_kognitif::class,'id_wali_asuh','id');
    }
    public function WaliAsuhCatatanAfektif()
    {
        return $this->belongsTo(Catatan_afektif::class,'id_wali_asuh','id');
    }

        // public function createdBy()
    // {
    //     return $this->belongsTo(user::class, 'created_by');
    // }
    // public function updatedBy()
    // {
    //     return $this->belongsTo(user::class, 'updated_by');
    // }
}
