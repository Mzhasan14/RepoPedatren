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

    protected $fillable = [
        'id_biodata',
        'pekerjaan',
        'penghasilan',
        'wafat',
        'created_by',
        'updated_by',
        'deleted_by',
        'status'
    ];

    public function biodata()
    {
        return $this->belongsTo(Biodata::class, 'id_biodata');
    }

    public function keluarga()
    {
        return $this->hasMany(Keluarga::class, 'id_biodata', 'id_biodata');
    }

    public function scopeActive($query)
    {
        return $query->where('orang_tua.status', true);
    }
}
