<?php

namespace App\Models\Kewaliasuhan;

use App\Models\Santri;
use App\Models\PesertaDidik;
use App\Models\Peserta_didik;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Anak_asuh extends Model
{
    use HasFactory;

    use SoftDeletes;
    //
    protected $table = 'anak_asuh';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'id_santri',
        'id_grup_wali_asuh',
        'created_by',
        'updated_by',
        'status'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function pesertaDidik()
    {
        return $this->belongsTo(PesertaDidik::class, 'id_peserta_didik', 'id');
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'id_santri', 'id');
    }

    public function grupWaliAsuh()
    {
        return $this->belongsTo(Grup_WaliAsuh::class, 'id_grup_wali_asuh', 'id');
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
