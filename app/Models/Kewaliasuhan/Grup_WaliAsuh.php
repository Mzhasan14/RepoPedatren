<?php

namespace App\Models\Kewaliasuhan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grup_WaliAsuh extends Model
{
    use SoftDeletes;
    //
    protected $table = 'grup_wali_asuh';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;
    
    protected $fillable = [
        'nama_grup',
        'created_by',
        'updated_by',
        'status'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

            // public function createdBy()
    // {
    //     return $this->belongsTo(user::class, 'created_by');
    // }
    // public function updatedBy()
    // {
    //     return $this->belongsTo(user::class, 'updated_by');
    // }

    public function waliAsuh() {
        return $this->hasMany(Wali_asuh::class,'id_grup_wali_asuh','id');
    }

    public function anakAsuh() {
        return $this->hasMany(Anak_asuh::class,'id_grup_wali_asuh','id');
    }
}
