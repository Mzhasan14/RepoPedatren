<?php

namespace App\Models;

use App\Models\LogPresensi;
use App\Models\JadwalSholat;
use App\Models\PresensiSholat;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sholat extends Model
{
    use SoftDeletes;

    protected $table = 'sholat';

    protected $fillable = [
        'nama_sholat',
        'urutan',
        'aktif',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('sholat')
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

                return "Data sholat berhasil {$action} oleh {$user}.";
            });
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_by ??= Auth::id();
        });
        // static::creating(fn($model) => $model->created_by = Auth::id());
        static::updating(fn($model) => $model->updated_by = Auth::id());
        static::deleting(function ($model) {
            $model->deleted_by = Auth::id();
            $model->save();
        });
    }

    // Relasi ke JadwalSholat
    public function jadwalSholat()
    {
        return $this->hasMany(JadwalSholat::class, 'sholat_id');
    }

    // Relasi ke PresensiSholat
    public function presensiSholat()
    {
        return $this->hasMany(PresensiSholat::class, 'sholat_id');
    }

    // Relasi ke LogPresensi
    public function logPresensi()
    {
        return $this->hasMany(LogPresensi::class, 'sholat_id');
    }
}
