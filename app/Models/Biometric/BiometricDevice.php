<?php

namespace App\Models\Biometric;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BiometricDevice extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'device_name',
        'location',
        'ip_address',
        'type',
        'is_active',
    ];

       protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }


    public function logs()
    {
        return $this->hasMany(BiometricLog::class, 'device_id');
    }
}
