<?php

namespace App\Models;

use App\Models\Kewaliasuhan\Wali_asuh;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perizinan extends Model
{
    use HasFactory;

    protected $table = 'perizinan';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [
        'id'
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

}
