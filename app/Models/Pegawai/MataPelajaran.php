<?php

namespace App\Models\Pegawai;

use App\Models\Pendidikan\Lembaga;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MataPelajaran extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'mata_pelajaran';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $timestamps = true;

    public $incrementing = true;

    protected $guarded = [
        'id',
    ];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('mata_pelajaran')
            ->logOnlyDirty()
            ->logOnly([
                'lembaga_id',
                'pengajar_id',
                'nama_mapel',
                'kode_mapel',
                'created_by',
                'updated_by',
                'deleted_by',
            ])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'Sistem';

                return match ($eventName) {
                    'created' => "Mata Pelajaran ditambahkan oleh {$userName}",
                    'updated' => "Mata Pelajaran diperbarui oleh {$userName}",
                    'deleted' => "Mata Pelajaran dihapus oleh {$userName}",
                };
            });
    }
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_by ??= Auth::id();
        });

        static::updating(fn ($model) => $model->updated_by = Auth::id());

        static::deleting(function ($model) {
            $model->deleted_by = Auth::id();
            $model->save();
        });
    }
    public function pengajar()
    {
        return $this->belongsTo(Pengajar::class, 'pengajar_id');
    }

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class, 'lembaga_id');
    }

    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class, 'mata_pelajaran_id');
    }
}
