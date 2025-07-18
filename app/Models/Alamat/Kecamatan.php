<?php

namespace App\Models\Alamat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    use HasFactory;

    protected $table = 'kecamatan';

    protected $primaryKey = 'id';

    public $timestamps = true;

    public $incrementing = true;

    protected $guarded = [
        'id',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'id_kabupaten', 'id');
    }
}
