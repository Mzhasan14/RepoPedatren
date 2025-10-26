<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembayaran extends Model
{
    use SoftDeletes;

    protected $table = 'pembayaran';

    protected $fillable = [
        'tagihan_santri_id',
        'virtual_account_id',
        'metode',
        'jumlah_bayar',
        'tanggal_bayar',
        'status',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    
    protected $casts = [
        'jumlah_bayar' => 'decimal:2',
        'tanggal_bayar' => 'datetime',
    ];

    /* ============ RELASI ============ */

    public function tagihanSantri()
    {
        return $this->belongsTo(TagihanSantri::class, 'tagihan_santri_id');
    }

    public function virtualAccount()
    {
        return $this->belongsTo(VirtualAccount::class, 'virtual_account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
