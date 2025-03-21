<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Status_keluarga extends Model
{
    use HasFactory;

    //
    use SoftDeletes;

    protected $table = 'status_keluarga';

    protected $fillable = [
        'nama_status',
        'created_by',
        'updated_by',
        'deleted_by',
        'status'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function keluarga() {
        return $this->hasMany(Keluarga::class, 'id_status_keluarga');
    }
}
