<?php

namespace App\Models\Pendidikan;

use App\Models\Pendidikan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rombel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rombel';

    protected $guarded = ['id'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'id');
    }

    public function pendidikan()
    {
        return $this->hasMany(Pendidikan::class);
    }
}
