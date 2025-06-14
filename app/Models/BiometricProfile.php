<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BiometricProfile extends Model
{
    use SoftDeletes;

    protected $table = 'biometric_profiles';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'santri_id',
        'card_uid',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function fingerprints()
    {
        return $this->hasMany(BiometricFingerprint::class, 'biometric_profile_id');
    }

    public function logs()
    {
        return $this->hasMany(BiometricLog::class, 'biometric_profile_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
