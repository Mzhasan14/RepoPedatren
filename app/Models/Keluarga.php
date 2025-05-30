<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Keluarga extends Model
{
    use HasFactory;

    use SoftDeletes;
    //
    protected $dates = ['deleted_at'];
    protected $table = 'keluarga';

    protected $fillable = [
        'no_kk',
        'id_biodata',
        'created_by',
        'updated_by',
        'deleted_by',
        'status'
    ];

    // protected static function boot()
    // {
    //     parent::boot();
    //     static::creating(function ($model) {
    //         $model->id = (string) Str::uuid();
    //     });
    // }

  // Di model Keluarga
public function biodataDetail()
{
    return $this->belongsTo(Biodata::class, 'id_biodata', 'id');
}


    public function biodata()
    {
        return $this->belongsTo(Biodata::class, 'id_biodata', 'id');
    }

    public function hubunganKeluarga()
    {
        return $this->belongsTo(HubunganKeluarga::class, 'id_hubungan_keluarga', 'id');
    }

    public function orangTua()
    {
        return $this->belongsTo(OrangTuaWali::class, 'id_biodata', 'id_biodata');
    }

    public function scopeActive($query)
    {
        return $query->where('keluarga.status', true);
    }
}
