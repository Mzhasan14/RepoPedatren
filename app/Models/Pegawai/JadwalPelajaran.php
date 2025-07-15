<?php

namespace App\Models\Pegawai;

use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Lembaga;
use App\Models\Pendidikan\Rombel;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;
class JadwalPelajaran extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'jadwal_pelajaran';

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
            ->useLogName('jadwal_pelajaran')
            ->logOnlyDirty()
            ->logOnly([
                'hari',
                'semester_id',
                'lembaga_id',
                'jurusan_id',
                'kelas_id',
                'rombel_id',
                'mata_pelajaran_id',
                'jam_pelajaran_id',
                'created_by',
                'updated_by',
                'deleted_by',
            ])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'Sistem';

                return match ($eventName) {
                    'created' => "Jadwal Pelajaran ditambahkan oleh {$userName}",
                    'updated' => "Jadwal Pelajaran diperbarui oleh {$userName}",
                    'deleted' => "Jadwal Pelajaran dihapus oleh {$userName}",
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
    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }
    public function jamPelajaran()
    {
        return $this->belongsTo(JamPelajaran::class, 'jam_pelajaran_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }
    public function rombel()
    {
        return $this->belongsTo(Rombel::class, 'rombel_id');
    }
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
