<?php

namespace App\Models;

use App\Models\Biodata;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WargaPesantren extends Model
{
    use SoftDeletes, HasFactory, LogsActivity;
    protected $table = 'warga_pesantren';
    protected $fillable = [
        'biodata_id',
        'niup',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('warga_pesantren')
            ->logOnlyDirty()
            ->logOnly($this->fillable)
            ->setDescriptionForEvent(fn(string $event) =>
            "Warga pesantren {$event} oleh " . (Auth::user()->name ?? 'Sistem'));
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

    public function biodata()
    {
        return $this->belongsTo(Biodata::class, 'biodata_id');
    }
}
