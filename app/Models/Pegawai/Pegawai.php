<?php

namespace App\Models\Pegawai;

use App\Models\Biodata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;

class Pegawai extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'pegawai';


    protected $guarded = [
        'id'
    ];

    // Logging
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('pegawai')
            ->logOnlyDirty()
            ->logOnly([
                'biodata_id',
                'status_aktif',
                'created_by',
                'updated_by',
                'deleted_by',
            ])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'Sistem';

                return match ($eventName) {
                    'created' => "Pegawai ditambahkan oleh {$userName}",
                    'updated' => "Pegawai diperbarui oleh {$userName}",
                    'deleted' => "Pegawai dihapus oleh {$userName}",
                };
            });
    }

    // Audit trail
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_by ??= Auth::id();
        });

        static::updating(fn($model) => $model->updated_by = Auth::id());

        static::deleting(function ($model) {
            $model->deleted_by = Auth::id();
            $model->save(); // perlu simpan sebelum soft delete
        });
    }
    
    public function ScopeActive($query)
    {
        return $query->where('pegawai.status_aktif','aktif');
    }

    public function karyawan()
    {
        return $this->hasMany(Karyawan::class);
    }
    public function pengajar()
    {
        return $this->hasMany(Pengajar::class);
    }

    public function biodata()
    {
        return $this->belongsTo(Biodata::class);
    }
    public function pengurus()
    {
        return $this->hasMany(Pengurus::class);
    }
    public function wali_kelas()
    {
        return $this->hasMany(WaliKelas::class); // atau ->hasOne() jika satu-satu
    }


}
