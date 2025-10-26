<?php

namespace App\Models\Kewilayahan;

use App\Models\DomisiliSantri;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kamar extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'kamar';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $guarded = ['id'];

    public function blok()
    {
        return $this->belongsTo(Blok::class, 'blok_id', 'id');
    }
    public function domisiliSantri()
    {
        return $this->hasMany(DomisiliSantri::class, 'kamar_id');
    }
}
