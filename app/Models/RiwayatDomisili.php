<?php

namespace App\Models;

use App\Models\Kewilayahan\Blok;
use App\Models\Kewilayahan\Kamar;
use Spatie\Activitylog\LogOptions;
use App\Models\Kewilayahan\Wilayah;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatDomisili extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'riwayat_domisili';

    protected $fillable = [
        'santri_id',
        'wilayah_id',
        'blok_id',
        'kamar_id',
        'tanggal_masuk',
        'tanggal_keluar',
        'status',
        'keterangan',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('riwayat_domisili')
            ->logOnlyDirty()
            ->logOnly($this->fillable)
            ->setDescriptionForEvent(fn(string $event) =>
            "Riwayat domisili {$event} oleh " . (Auth::user()->name ?? 'Sistem'));
    }

    protected static function booted()
    {
        static::creating(fn($model) => $model->created_by = Auth::id());
        static::updating(fn($model) => $model->updated_by = Auth::id());
        static::deleting(function ($model) {
            $model->deleted_by = Auth::id();
            $model->save();
        });
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }
    public function blok(): BelongsTo
    {
        return $this->belongsTo(Blok::class, 'blok_id');
    }
    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }
    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }
}
