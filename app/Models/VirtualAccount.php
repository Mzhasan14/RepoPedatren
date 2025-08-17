<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VirtualAccount extends Model
{
    use SoftDeletes;

    protected $table = 'virtual_accounts';

    protected $fillable = [
        'santri_id',
        'bank_code',
        'va_number',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Relasi ke Santri
    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    // Relasi ke User (pencatat aktivitas)
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
