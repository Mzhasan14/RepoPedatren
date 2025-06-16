<?php

namespace App\Models\Kewilayahan;

use App\Models\DomisiliSantri;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Blok extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'blok';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $guarded = ['id'];
    // public function scopeActive($query)
    // {
    //     return $query->where('status', true);
    // }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id', 'id');
    }

    public function kamar()
    {
        return $this->hasMany(Kamar::class, 'blok_id', 'id');
    }

    public function domisiliSantri()
    {
        return $this->hasMany(DomisiliSantri::class, 'blok_id');
    }
}
