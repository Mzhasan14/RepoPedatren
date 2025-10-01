<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransaksiSaldo extends Model
{
    use SoftDeletes;

    protected $table = 'transaksi_saldo';

    protected $fillable = [
        'santri_id',
        'outlet_id',
        'kategori_id',
        'user_outlet_id',
        'keterangan',
        'tipe',
        'jumlah',
        'created_by',
        'updated_by',
        'deleted_by'
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

    /**
     * Relasi ke User Outlet
     */
    public function userOutlet()
    {
        return $this->belongsTo(DetailUserOutlet::class, 'user_outlet_id');
    }

    /**
     * Relasi ke User (created_by)
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke User (updated_by)
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relasi ke User (deleted_by)
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
