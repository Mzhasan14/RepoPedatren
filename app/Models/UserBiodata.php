<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBiodata extends Model
{
    /** @use HasFactory<\Database\Factories\UserBiodataFactory> */
    use HasFactory;

    protected $table = 'user_biodata';

    protected $fillable = [
        'user_id',
        'biodata_id'
    ];
}
