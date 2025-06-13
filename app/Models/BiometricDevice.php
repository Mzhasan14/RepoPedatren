<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BiometricDevice extends Model
{
    use SoftDeletes;

    protected $table = 'biometric_devices';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'device_name',
        'location',
        'ip_address',
        'type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function logs()
    {
        return $this->hasMany(BiometricLog::class, 'device_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
