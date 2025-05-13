<?php

namespace App\Models;

use App\Models\Khadam;
use Illuminate\Support\Str;
use App\Models\PesertaDidik;
use App\Models\DomisiliSantri;
use App\Models\Catatan_afektif;
use App\Models\RiwayatDomisili;
use App\Models\Catatan_kognitif;
use App\Models\PengunjungMahrom;
use App\Models\RiwayatPendidikan;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use App\Models\Kewaliasuhan\Anak_asuh;
use App\Models\Kewaliasuhan\Wali_asuh;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Santri extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    protected $table = 'santri';
    protected $guarded = ['id'];

    protected $fillable = [
        'biodata_id',
        'nis',
        'tanggal_masuk',
        'tanggal_keluar',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('santri')
            ->logOnlyDirty()
            ->logOnly($this->fillable)
            ->setDescriptionForEvent(fn(string $event) =>
            "Data santri {$event} oleh " . (Auth::user()->name ?? 'Sistem'));
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
}
