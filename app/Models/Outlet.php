<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outlet extends Model
{
    use SoftDeletes;

    protected $table = 'outlet';

    protected $fillable = [
        'nama_outlet',
        'jenis_outlet',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];
}
