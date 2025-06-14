<?php

namespace App\Models;

use App\Models\Kewaliasuhan\Wali_asuh;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Catatan_afektif extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'catatan_afektif';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $timestamps = true;

    public $incrementing = true;

    protected $guarded = [
        'id',
    ];

    protected $dates = [
        'tanggal_buat',
        'tanggal_selesai',
        'deleted_at',
    ];

    // LogsActivity Spatie
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('catatan_afektif')
            ->logOnlyDirty()
            ->logOnly([
                'id_santri',
                'id_wali_asuh',
                'kepedulian_nilai', 'kepedulian_tindak_lanjut',
                'kebersihan_nilai', 'kebersihan_tindak_lanjut',
                'akhlak_nilai', 'akhlak_tindak_lanjut',
                'tanggal_buat',
                'tanggal_selesai',
                'status',
                'created_by', 'updated_by', 'deleted_by',
            ])
            ->setDescriptionForEvent(fn (string $eventName) => "Catatan Afektif {$eventName} oleh ".(Auth::user()->name ?? 'Sistem')
            );
    }

    // Auto set created_by, updated_by, deleted_by
    protected static function booted()
    {
        static::creating(fn ($model) => $model->created_by ??= Auth::id());
        static::updating(fn ($model) => $model->updated_by = Auth::id());
        static::deleting(function ($model) {
            $model->deleted_by = Auth::id();
            $model->save();
        });
    }

    public function waliAsuh()
    {
        return $this->belongsTo(Wali_asuh::class, 'id_wali_asuh');
    }

    public function ScopeActive($query)
    {
        return $query->where('catatan_afektif.status', true);
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'id_santri');
    }
}
