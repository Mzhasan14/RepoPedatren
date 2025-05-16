<?php

namespace App\Models;

use App\Models\Santri;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;

class Pelanggaran extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'pelanggaran';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'santri_id',
        'status_pelanggaran',
        'jenis_putusan',
        'jenis_pelanggaran',
        'diproses_mahkamah',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by'
    ];


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('pelanggaran')
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

                return "Data pelanggaran berhasil {$action} oleh {$user}.";
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

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }
}
