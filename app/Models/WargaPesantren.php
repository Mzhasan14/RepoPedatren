<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WargaPesantren extends Model
{
    use SoftDeletes, HasFactory;
    protected $table = 'warga_pesantren';
    protected $guarded = ['id'];

    public function biodata()
    {
        return $this->belongsTo(Biodata::class, 'id_biodata', 'id');
    }
}
