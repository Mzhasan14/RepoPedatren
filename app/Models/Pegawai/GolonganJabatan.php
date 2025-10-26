<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class GolonganJabatan extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'golongan_jabatan';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $timestamps = true;

    public $incrementing = true;

    protected $guarded = [
        'id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('golongan_jabatan')
            ->logOnlyDirty()
            ->logOnly(['nama_golongan_jabatan', 'status', 'created_by', 'updated_by', 'deleted_by'])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'Sistem';
                return "Golongan Jabatan {$eventName} oleh {$userName}";
            });
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
    public function karyawan()
    {
        return $this->hasMany(Karyawan::class, 'golongan_jabatan_id');
    }
    
    public function pengurus()
    {
        return $this->hasMany(Pengurus::class, 'golongan_jabatan_id');
    }

}
