<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Berkas extends Model
{
    use HasFactory;

    protected $table = 'berkas';

    protected $fillable = [
        'id_biodata',
        'id_jenis_berkas',
        'file_path',
        'created_by',
        'updated_by',
        'deleted_by',
        'status'
    ];

    public function jenisBerkas()
    {
        return $this->belongsTo(JenisBerkas::class,'id_jenis_berkas','id');
    }

    public function biodata()
    {
        return $this->belongsTo(Biodata::class, 'id_biodata');
    }

}
