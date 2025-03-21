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

    protected $fillable = [
        'no_kk',
        'id_biodata',
        'id_status_keluarga',
        'wali',
        'created_by',
        'updated_by',
        'deleted_by',
        'status'
    ];

    public function biodata() {
        return $this->belongsTo(Biodata::class, 'id_biodata', 'id');
    }

    public function statusKeluarga() {
        return $this->belongsTo(Status_keluarga::class, 'id_status_keluarga','id');
    }

    public function scopeActive($query)
    {
        return $query->where('keluarga.status', true);
    }
} 