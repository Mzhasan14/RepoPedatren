<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutletKategori extends Model
{
    protected $table = 'outlet_kategori';

    protected $fillable = [
        'outlet_id',
        'kategori_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

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
