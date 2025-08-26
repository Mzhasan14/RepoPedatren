<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class RekapNadhoman extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rekap_nadhoman';
    protected $fillable = [
        'kitab_id',
        'total_bait',
        'persentase_selesai',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Relasi
    public function kitab()
    {
        return $this->belongsTo(Kitab::class);
    }
}
