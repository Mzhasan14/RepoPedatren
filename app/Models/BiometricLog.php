<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BiometricLog extends Model
{
    use HasUuids;

    protected $fillable = ['id', 'biometric_profile_id', 'device_id', 'method', 'scanned_at', 'success', 'message'];
}
