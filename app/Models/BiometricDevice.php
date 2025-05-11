<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BiometricDevice extends Model
{
    use HasUuids;

    protected $fillable = ['id', 'device_name', 'location', 'ip_address', 'type', 'is_active'];
}
