<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrangTua extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $table = 'orang_tua';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [];

    public function biodata() {
        return $this->belongsTo(Biodata::class,'id_biodata','id');
    }

    public function scopeActive($query)
    {
        return $query->where('orang_tua.status', true);
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
