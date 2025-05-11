<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrangTuaWali extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'orang_tua_wali';

    protected $fillable = [
        'id_biodata',
        'id_hubungan_keluarga',
        'wali',
        'pekerjaan',
        'penghasilan',
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

    public function biodata()
    {
        return $this->belongsTo(Biodata::class, 'id_biodata', 'id');
    }

    public function hubunganKeluarga()
    {
        return $this->belongsTo(HubunganKeluarga::class, 'id_hubungan_keluarga', 'id');
    }

    public function keluarga()
    {
        return $this->hasMany(Keluarga::class, 'id_biodata', 'id_biodata');
    }

    public function scopeActive($query)
    {
        return $query->where('orang_tua_wali.status', true);
    }
}
