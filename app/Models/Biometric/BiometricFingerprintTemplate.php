<?php

namespace App\Models\Biometric;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BiometricFingerprintTemplate extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'finger_position_id',
        'template',
        'scan_order',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function fingerPosition()
    {
        return $this->belongsTo(BiometricFingerPosition::class, 'finger_position_id');
    }
}
