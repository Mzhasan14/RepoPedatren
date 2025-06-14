<?php

namespace App\Models\Kewaliasuhan;

use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Anak_asuh extends Model
{
    use HasFactory;
    use SoftDeletes;

    //
    protected $table = 'anak_asuh';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $timestamps = true;

    public $incrementing = true;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id_santri',
        'created_by',
        'updated_by',
        'status',
    ];

    // protected static function boot()
    // {
    //     parent::boot();
    //     static::creating(function ($model) {
    //         $model->id = (string) Str::uuid();
    //     });
    // }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'id_santri', 'id');
    }

    public function Kewaliasuhan()
    {
        return $this->hasMany(Kewaliasuhan::class, 'id_anak_asuh', 'id');
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
