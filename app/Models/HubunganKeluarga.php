<?php

namespace App\Models;

use App\Models\Keluarga;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HubunganKeluarga extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hubungan_keluarga';

    protected $fillable = [
        'nama_status',
        'created_by',
        'updated_by',
        'deleted_by',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function orangTuaWali()
    {
        return $this->hasMany(Keluarga::class, 'id_hubungan_keluarga', 'id');
    }
}
