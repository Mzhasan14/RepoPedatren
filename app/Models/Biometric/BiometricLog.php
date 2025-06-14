<?php

namespace App\Models\Biometric;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BiometricLog extends Model
{
    use HasFactory;

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

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function device()
    {
        return $this->belongsTo(BiometricDevice::class, 'device_id');
    }

    public function profile()
    {
        return $this->belongsTo(BiometricProfile::class, 'biometric_profile_id');
    }
}
