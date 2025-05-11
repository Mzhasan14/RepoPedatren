<?php

namespace App\Models\Kewaliasuhan;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Kewilayahan\Wilayah;
use App\Models\Kewaliasuhan\Anak_asuh;
use App\Models\Kewaliasuhan\Wali_asuh;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grup_WaliAsuh extends Model
{
    use HasFactory;

    use SoftDeletes;
    //
    protected $table = 'grup_wali_asuh';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;
    protected $dates = ['deleted_at'];
    
    protected $fillable = [
        'id_wilayah',
        'nama_grup',
        'created_by',
        'updated_by',
        'status'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

            public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function waliAsuh() {
        return $this->hasOne(Wali_asuh::class,'id_grup_wali_asuh','id');
    }

    public function anakAsuh() {
        return $this->hasMany(Anak_asuh::class,'id_grup_wali_asuh','id');
    }

    public function wilayah() {
        return $this->belongsTo(Wilayah::class,'id_wilayah','id');
    }
}
