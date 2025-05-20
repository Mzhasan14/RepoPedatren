<?php

namespace App\Models\Biometric;

use App\Models\Santri;
use Illuminate\Support\Str;
use App\Models\Biometric\BiometricLog;
use Illuminate\Database\Eloquent\Model;
use App\Models\Biometric\BiometricFingerPosition;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BiometricProfile extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'santri_id',
        'card_uid',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }


    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function fingerPositions()
    {
        return $this->hasMany(BiometricFingerPosition::class);
    }

    public function logs()
    {
        return $this->hasMany(BiometricLog::class);
    }
}
