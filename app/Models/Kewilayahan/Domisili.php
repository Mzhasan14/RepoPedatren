<?php

namespace App\Models\Kewilayahan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Peserta_didik;
class Domisili extends Model
{
    use SoftDeletes;
    protected $table = 'domisili';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    protected $fillable = [
        'nama_domisili',
        'id_kamar',
        'created_by',
        'updated_by',
        'deleted_by',
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
        return $this->hasMany(Peserta_didik::class, 'id_domisili', 'id');
    }
}
