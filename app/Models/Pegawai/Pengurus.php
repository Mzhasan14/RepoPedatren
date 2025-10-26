<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Pengurus extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'pengurus';

    protected $guarded = [
        'created_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('pengurus')
            ->logOnlyDirty()
            ->logOnly([
                'pegawai_id', 'golongan_jabatan_id', 'jabatan', 'satuan_kerja', 'keterangan_jabatan',
                'tanggal_mulai', 'tanggal_akhir', 'status_aktif',
                'created_by', 'updated_by', 'deleted_by',
            ])
            ->setDescriptionForEvent(fn (string $eventName) => "Pengurus {$eventName} oleh ".(Auth::user()->name ?? 'Sistem')
            );
    }

    protected static function booted()
    {
        static::creating(fn ($model) => $model->created_by ??= Auth::id());
        static::updating(fn ($model) => $model->updated_by = Auth::id());
        static::deleting(function ($model) {
            $model->deleted_by = Auth::id();
            $model->save();
        });
    }

    public function scopeActive($query)
    {
        return $query->where('pengurus.status_aktif', 'aktif');
    }

    public function PengurusGolongan()
    {
        return $this->belongsTo(Golongan::class, 'id_golongan', 'id');
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}
