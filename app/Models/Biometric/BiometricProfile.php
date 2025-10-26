<?php

namespace App\Models\Biometric;

use App\Models\Santri;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
