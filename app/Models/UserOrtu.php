<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserOrtu extends Authenticatable
{
    use HasRoles, HasApiTokens, SoftDeletes, Notifiable;

    protected $table = 'user_ortu';

    protected $fillable = [
        'no_kk',
        'no_hp',
        'email',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $guard_name = 'web'; // atau 'api'


    // public function biodata()
    // {
    //     return $this->belongsTo(Biodata::class, 'biodata_id');
    // }
}
