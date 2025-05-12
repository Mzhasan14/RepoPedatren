<?php

namespace App\Models;

use App\Models\Santri;
use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Rombel;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Lembaga;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class RiwayatPendidikan extends Model
{
    use SoftDeletes, LogsActivity;
    protected $table = 'riwayat_pendidikan';

    protected $fillable = [
        'santri_id',
        'no_induk',
        'lembaga_id',
        'jurusan_id',
        'kelas_id',
        'rombel_id',
        'tanggal_masuk',
        'tanggal_keluar',
        'status',
        'keterangan',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('riwayat_pendidikan')
            ->logOnlyDirty()
            ->logOnly($this->fillable)
            ->setDescriptionForEvent(fn(string $event) =>
            "Riwayat pendidikan {$event} oleh " . (Auth::user()->name ?? 'Sistem'));
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

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class, 'lembaga_id');
    }
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class, 'rombel_id');
    }
    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }
}
