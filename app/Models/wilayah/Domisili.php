<?php

namespace App\Models\wilayah;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Peserta_didik;

class Domisili extends Model
{
    use SoftDeletes;
    protected $table = 'domisili';
    protected $fillable = [
        'nama_domisili',
        'nis',
        'created_by',
        'status',
    ];
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function kamar()
    {
        return $this->belongsTo(Kamar::class, 'id_kamar', 'id');
    }

    public function Peserta_didik()
    {
        return $this->belongsTo(Peserta_didik::class, 'nis', 'nis');
    }
    
}
