<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BiometricLog extends Model
{
    use SoftDeletes;

    protected $table = 'biometric_logs';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'biometric_profile_id',
        'device_id',
        'method',
        'scanned_at',
        'success',
        'message',
    ];

    protected $casts = [
        'success' => 'boolean',
        'scanned_at' => 'datetime',
    ];

    public function profile()
    {
        return $this->belongsTo(BiometricProfile::class, 'biometric_profile_id');
    }

    public function device()
    {
        return $this->belongsTo(BiometricDevice::class, 'device_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
