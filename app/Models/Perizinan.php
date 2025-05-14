<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;

class Perizinan extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'perizinan';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'santri_id',
        'pengasuh_id',
        'biktren_id',
        'kamtib_id',
        'pengantar_id',
        'alasan_izin',
        'alamat_tujuan',
        'tanggal_mulai',
        'tanggal_akhir',
        'tanggal_kembali',
        'jenis_izin',
        'status',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by'
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

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }
}
