<?php

namespace App\Models\Kewaliasuhan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anak_asuh extends Model
{
   use SoftDeletes;
    //
    protected $table = 'anak_asuh';
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
        return $this->belongsTo(Peserta_didik::class,'nis','nis');
    }

    public function grupWaliAsuh() {
        return $this->belongsTo(Grup_WaliAsuh::class,'id_grup_wali_asuh','id');
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
