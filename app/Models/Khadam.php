<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Khadam extends Model
{
    use HasFactory;

    protected $table = 'khadam';

    protected $guarded = ['id'];

    // public function scopeActive($query)
    // {
    //     return $query->where('status', true);
    // }

    // public function biodata()
    // {
    //     return $this->belongsTo(Biodata::class, 'id_biodata', 'id');
    // }
}
