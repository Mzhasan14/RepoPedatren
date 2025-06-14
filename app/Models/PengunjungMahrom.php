<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PengunjungMahrom extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'pengunjung_mahrom';

    protected $fillable = [
        'biodata_id',
        'santri_id',
        'nama_pengunjung',
        'hubungan_id',
        'jumlah_rombongan',
        'tanggal_kunjungan',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('pengunjung_mahrom')
            ->logOnlyDirty()
            ->logOnly($this->fillable)
            ->setDescriptionForEvent(function (string $event) {
                $verbs = [
                    'created' => 'ditambahkan',
                    'updated' => 'diperbarui',
                    'deleted' => 'dihapus',
                ];

                $action = $verbs[$event] ?? $event;
                $user = Auth::user()->name ?? 'Sistem';

                return "Data pengunjung mahrom berhasil {$action} oleh {$user}.";
            });
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_by ??= Auth::id();
        });
        // static::creating(fn($model) => $model->created_by = Auth::id());
        static::updating(fn ($model) => $model->updated_by = Auth::id());
        static::deleting(function ($model) {
            $model->deleted_by = Auth::id();
            $model->save();
        });
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function biodata()
    {
        return $this->belongsTo(Biodata::class, 'biodata_id');
    }

    public function hubungan()
    {
        return $this->belongsTo(HubunganKeluarga::class, 'hubungan_id');
    }
}
