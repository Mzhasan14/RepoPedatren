<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class WaliKelas extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'wali_kelas';


    protected $guarded = [
        'created_at'
    ];

    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('wali_kelas')
            ->logOnlyDirty()
            ->logOnly([
                'pegawai_id', 'lembaga_id', 'jurusan_id', 'kelas_id', 'rombel_id', 'jumlah_murid', 'status_aktif',
                'created_by', 'updated_by', 'deleted_by'
            ])
            ->setDescriptionForEvent(fn(string $eventName) => 
                "Wali Kelas {$eventName} oleh " . (Auth::user()->name ?? 'Sistem')
            );
    }

    protected static function booted()
    {
        static::creating(fn($model) => $model->created_by ??= Auth::id());
        static::updating(fn($model) => $model->updated_by = Auth::id());
        static::deleting(function ($model) {
            $model->deleted_by = Auth::id();
            $model->save();
        });
    }
    public function WaliKelasPengajar()
    {
        return $this->belongsTo(Pengajar::class,'id_pengajar','id');
    }

    public function ScopeActive($query)
    {
        return $query->where('wali_kelas.status_aktif','aktif');
    }
}
