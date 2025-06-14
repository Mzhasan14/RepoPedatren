<?php

namespace App\Models\Biometric;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BiometricFingerPosition extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'biometric_profile_id',
        'finger_position',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function profile()
    {
        return $this->belongsTo(BiometricProfile::class, 'biometric_profile_id');
    }

    public function templates()
    {
        return $this->hasMany(BiometricFingerprintTemplate::class, 'finger_position_id');
    }
}
