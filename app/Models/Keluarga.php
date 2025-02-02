<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Keluarga extends Model
{
    use SoftDeletes;
    //
    protected $table = 'keluarga';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'no_kk',
        'status_wali',
        'id_status_keluarga',
        'created_by',
        'updated_by',
        'status'
    ];


    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
} 