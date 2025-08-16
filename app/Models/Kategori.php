<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kategori extends Model
{
    use SoftDeletes;

    protected $table = 'kategori';
    protected $fillable = ['nama_kategori', 'status', 'created_by', 'updated_by', 'deleted_by'];

    public function outlet()
    {
        return $this->belongsToMany(Outlet::class, 'outlet_kategori')
            ->withPivot('status')
            ->withTimestamps();
    }
}
