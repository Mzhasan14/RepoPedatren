<?php

namespace App\Models\Pegawai;

use App\Models\Biodata;
use App\Models\Pendidikan\Lembaga;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Pengajar extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'pengajar';
    protected $guarded = [
        'created_at'
    ];

        public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('pengajar')
            ->logOnlyDirty()
            ->logOnly([
                'pegawai_id', 'lembaga_id', 'golongan_id', 'jabatan', 'tahun_masuk', 'tahun_akhir', 'status_aktif', 
                'created_by', 'updated_by', 'deleted_by'
            ])
            ->setDescriptionForEvent(fn(string $eventName) => 
                "Pengajar {$eventName} oleh " . (Auth::user()->name ?? 'Sistem')
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


    public function biodata()
    {
        return $this->hasOneThrough(Biodata::class, Pegawai::class, 'id', 'id', 'id', 'id');
    }
    
    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class,'lembaga_id');
    }

    public function waliKelas()
    {
        return $this->hasOne(WaliKelas::class,'id_pengajar','id');
    }

    public function golongan()
    {
        return $this->belongsTo(Golongan::class,'golongan_id');
    }
    public function ScopeActive($query)
    {
        return $query->where('pengajar.status_aktif','aktif');
    }
    public function MateriAjar()
    {
        return $this->hasMany(MateriAjar::class);
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

}
