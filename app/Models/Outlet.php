<?php

namespace App\Models;

use App\Models\Kategori;
use App\Models\DetailUserOutlet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outlet extends Model
{
    use SoftDeletes;

    protected $table = 'outlets';

    protected $fillable = [
        'nama_outlet',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Relasi ke kategori melalui pivot outlet_kategori
     */
    public function kategori()
    {
        return $this->belongsToMany(Kategori::class, 'outlet_kategori')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Relasi ke detail user outlet (banyak pengelola)
     */
    public function detailUsers()
    {
        return $this->hasMany(DetailUserOutlet::class, 'outlet_id');
    }

    /**
     * Relasi ke transaksi outlet
     */
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'outlet_id');
    }
}
