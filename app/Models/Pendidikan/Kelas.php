<?php

namespace App\Models\Pendidikan;

use App\Models\Pendidikan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kelas extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'kelas';

    protected $guarded = ['id'];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id', 'id');
    }

    public function rombel()
    {
        return $this->hasMany(Rombel::class, 'kelas_id', 'id');
    }

    public function pendidikan()
    {
        return $this->hasMany(Pendidikan::class);
    }
}
