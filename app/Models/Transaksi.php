<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaksi extends Model
{
    use SoftDeletes;

    protected $table = 'transaksi';

    protected $fillable = [
        'santri_id',
        'outlet_id',
        'kategori_id',
        'total_bayar',
        'tanggal',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'total_bayar' => 'decimal:2',
        'tanggal'     => 'datetime',
    ];

    /**
     * Relasi ke Santri
     */
    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    /**
     * Relasi ke Outlet
     */
    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    /**
     * Relasi ke Kategori
     */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }
}
