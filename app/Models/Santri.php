<?php

namespace App\Models;

use App\Models\Kewaliasuhan\Anak_asuh;
use App\Models\Kewaliasuhan\Wali_asuh;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Santri extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'santri';

    protected $guarded = ['id'];

    protected $fillable = [
        'biodata_id',
        'nis',
        'angkatan_id',
        'tanggal_masuk',
        'tanggal_keluar',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_keluar' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('santri')
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

                return "Data Santri berhasil {$action} oleh {$user}.";
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

    // Sinkron status
    public static function boot()
    {
        parent::boot();
    }

    public function kartu()
    {
        return $this->hasOne(Kartu::class, 'santri_id', 'id');
    }

    public function biodata()
    {
        return $this->belongsTo(Biodata::class, 'biodata_id', 'id');
    }

    public function riwayatDomisili()
    {
        return $this->hasMany(RiwayatDomisili::class, 'santri_id');
    }

    public function riwayatPendidikan()
    {
        return $this->hasMany(RiwayatPendidikan::class, 'santri_id');
    }

    public function kunjunganMahrom()
    {
        return $this->hasMany(PengunjungMahrom::class, 'santri_id');
    }

    public function waliAsuh()
    {
        return $this->hasMany(Wali_asuh::class, 'santri_id');
    }

    public function anakAsuh()
    {
        return $this->hasMany(Anak_asuh::class, 'santri_id');
    }

    public function catatanafektif()
    {
        return $this->hasMany(Catatan_afektif::class, 'id_santri');
    }

    public function catatankognitif()
    {
        return $this->hasMany(Catatan_kognitif::class, 'id_santri');
    }

    public function angkatan()
    {
        return $this->belongsTo(Angkatan::class, 'angkatan_id', 'id');
    }

    public function saldo()
    {
        return $this->hasOne(Saldo::class, 'santri_id', 'biodata_id');
    }

    public function transaksi()
    {
        return $this->hasMany(SaldoTransaksi::class, 'santri_id', 'biodata_id');
    }
}
