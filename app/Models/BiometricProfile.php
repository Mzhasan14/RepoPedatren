<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BiometricProfile extends Model
{
     use HasUuids;

    protected $fillable = ['id', 'santri_id', 'fingerprint_template', 'card_uid'];
}
