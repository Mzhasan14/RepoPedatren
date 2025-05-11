<?php

namespace App\Models\Kewaliasuhan;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kewaliasuhan extends Model
{
    use HasFactory;

    use SoftDeletes;
    //
    protected $table = 'kewaliasuhan';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id_wali_asuh',
        'id_anak_asuh',
        'tanggal_mulai',
        'tanggal_berakhir',
        'created_by',
        'updated_by',
        'status'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function WaliAsuh()
    {
        return $this->belongsTo(Wali_asuh::class, 'id_wali_asuh', 'id');
    }

    public function Anakasuh()
    {
        return $this->belongsTo(Anak_asuh::class, 'id_anak_asuh', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
