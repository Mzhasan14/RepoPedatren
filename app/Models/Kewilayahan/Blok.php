<?php

namespace App\Models\Kewilayahan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
