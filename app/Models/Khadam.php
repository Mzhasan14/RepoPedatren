<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Khadam extends Model
{
    use HasFactory;

    protected $table = 'khadam';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [
        'id'
    ];

    public function SantriKhadam()
    {
        return $this->belongsTo(Peserta_didik::class, 'id_peserta_didik' , 'id');
    }
}
