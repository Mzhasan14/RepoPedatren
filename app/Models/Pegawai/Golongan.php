<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Golongan extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'golongan';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $guarded = [
        'id'
    ];
    public function kategoriGolongan()
    {
        return $this->belongsTo(Golongan::class,'id_kategori_golongan','id');
    }
    public function pengajar()
    {
        return $this->hasMany(Pengajar::class,'golongan_id');
    }
        public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('golongan')
            ->logOnlyDirty()
            ->logOnly(['nama_golongan', 'kategori_golongan_id', 'status', 'created_by', 'updated_by', 'deleted_by'])
            ->setDescriptionForEvent(fn(string $eventName) => 
                "Golongan {$eventName} oleh " . (Auth::user()->name ?? 'Sistem')
            );
    }

    protected static function booted()
    {
        static::creating(fn($model) => $model->created_by ??= Auth::id());
        static::updating(fn($model) => $model->updated_by = Auth::id());
        static::deleting(function ($model) {
            $model->deleted_by = Auth::id();
            $model->save();
        });
    }
    

}
