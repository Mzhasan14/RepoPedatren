<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaldoTransaksi extends Model
{
    use SoftDeletes;
    protected $table = 'saldo_transaksi';
    protected $fillable = [
        'santri_id',
        'orang_tua_wali_id',
        'nominal',
        'metode_pembayaran',
        'bukti_transfer',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id', 'biodata_id');
    }

    public function orangTuaWali()
    {
        return $this->belongsTo(OrangTuaWali::class, 'orang_tua_wali_id');
    }

    public function adminApprove()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
