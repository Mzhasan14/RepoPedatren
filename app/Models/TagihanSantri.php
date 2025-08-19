<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TagihanSantri extends Model
{
    use SoftDeletes;

    protected $table = 'tagihan_santri';

    protected $fillable = [
        'tagihan_id',
        'santri_id',
        'nominal',
        'sisa',
        'status',
        'tanggal_jatuh_tempo',
        'tanggal_bayar',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'sisa' => 'decimal:2',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_bayar' => 'datetime',
    ];

    /* =======================
     * 🔗 RELATIONS
     * ======================= */

    // Tagihan utama (misal: SPP, Uang makan, dll)
    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }

    // Santri pemilik tagihan
    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    // Relasi ke user pembuat
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

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'tagihan_santri_id');
    }
}
