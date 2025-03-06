<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Keluarga extends Model
{
    use HasFactory;

    use SoftDeletes;
    //
    protected $table = 'keluarga';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'no_kk',
        'status_wali',
        'id_status_keluarga',
        'created_by',
        'updated_by',
        'status'
    ];

    // public function referensiNokk() {
    //     return $this->belongsTo(Biodata::class, 'no_kk', 'no_kk');
    // }

    public function statusKeluarga() {
        return $this->belongsTo(Status_keluarga::class, 'id_status_keluarga');
    }

    // public function createdBy()
    // {
    //     return $this->belongsTo(user::class, 'created_by');
    // }
    // public function updatedBy()
    // {
    //     return $this->belongsTo(user::class, 'updated_by');
    // }


    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
} 