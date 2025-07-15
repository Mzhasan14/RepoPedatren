<?php

namespace App\Models\Pegawai;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;

class JamPelajaran extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'jam_pelajaran';

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
            ->useLogName('jam_pelajaran')
            ->logOnlyDirty()
            ->logOnly([
                'jam_ke',
                'label',
                'jam_mulai',
                'jam_selesai',
                'created_by',
                'updated_by',
                'deleted_by',
            ])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'Sistem';

                return match ($eventName) {
                    'created' => "Jam Pelajaran ditambahkan oleh {$userName}",
                    'updated' => "Jam Pelajaran diperbarui oleh {$userName}",
                    'deleted' => "Jam Pelajaran dihapus oleh {$userName}",
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
    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class, 'jam_pelajaran_id');
    }
}
