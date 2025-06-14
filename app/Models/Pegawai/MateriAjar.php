<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MateriAjar extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'materi_ajar';

    protected $guarded = [
        'id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('materi_ajar')
            ->logOnlyDirty()
            ->logOnly([
                'pengajar_id', 'nama_materi', 'jumlah_menit', 'tahun_masuk', 'tahun_akhir', 'status_aktif',
            ])
            ->setDescriptionForEvent(fn (string $eventName) => "Materi Ajar {$eventName} oleh ".(Auth::user()->name ?? 'Sistem')
            );
    }

    protected static function booted()
    {
        static::creating(fn ($model) => $model->created_by ??= Auth::id());
        static::updating(fn ($model) => $model->updated_by = Auth::id());
    }

    public function Pengajar()
    {
        return $this->belongsTo(Pengajar::class);
    }
}
