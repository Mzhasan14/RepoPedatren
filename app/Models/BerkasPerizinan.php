<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BerkasPerizinan extends Model
{
    protected $table = 'berkas_perizinan';

    protected $fillable = [
        'perizinan_id',
        'file_path',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public function perizinan()
    {
        return $this->belongsTo(Perizinan::class);
    }
}
