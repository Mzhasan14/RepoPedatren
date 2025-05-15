<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Karyawan extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'karyawan';

    protected $guarded = [
        'id'
    ];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('karyawan')
            ->logOnlyDirty()
            ->logOnly([
                'pegawai_id', 'golongan_jabatan_id', 'lembaga_id', 'jabatan', 'keterangan_jabatan', 
                'tanggal_mulai', 'tanggal_selesai', 'status_aktif',
                'created_by', 'updated_by', 'deleted_by'
            ])
            ->setDescriptionForEvent(fn(string $eventName) => 
                "Karyawan {$eventName} oleh " . (Auth::user()->name ?? 'Sistem')
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
    public function ScopeActive($query)
    {
        return $query->where('karyawan.status_aktif','aktif');
    }
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}
