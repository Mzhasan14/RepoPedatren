<?php

namespace App\Models;

use App\Models\Biodata;
use App\Models\JenisBerkas;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Berkas extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'berkas';

    protected $fillable = [
        'biodata_id',
        'jenis_berkas_id',
        'file_path',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('berkas')
            ->logOnlyDirty()
            ->logOnly($this->fillable)
            ->setDescriptionForEvent(fn(string $event) =>
            "Berkas {$event} oleh " . (Auth::user()->name ?? 'Sistem'));
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

    public function jenisBerkas()
    {
        return $this->belongsTo(JenisBerkas::class, 'id_jenis_berkas', 'id');
    }

    public function biodata()
    {
        return $this->belongsTo(Biodata::class, 'id_biodata');
    }
}
