<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BerkasPelanggaran extends Model
{
    protected $table = 'berkas_pelanggaran';

    protected $fillable = [
        'pelanggaran_id',
        'file_path',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public function pelanggaran()
    {
        return $this->belongsTo(Pelanggaran::class);
    }
}
