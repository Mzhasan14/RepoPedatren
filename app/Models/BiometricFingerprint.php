<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BiometricFingerprint extends Model
{
    use SoftDeletes;

    protected $table = 'biometric_fingerprints';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'biometric_profile_id',
        'finger_position',
        'template',
        'scan_order'
    ];

    public function profile()
    {
        return $this->belongsTo(BiometricProfile::class, 'biometric_profile_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
