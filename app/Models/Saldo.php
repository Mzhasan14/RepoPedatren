<?php

namespace App\Models;

use App\Models\Santri;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Saldo extends Model
{
    use SoftDeletes;

    protected $table = 'saldo';

    protected $fillable = [
        'santri_id',
        'saldo',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
        'status' => 'boolean',
    ];

    /**
     * Relasi ke Santri
     */
    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }
}
