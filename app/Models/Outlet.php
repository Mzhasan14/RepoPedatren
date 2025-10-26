<?php

namespace App\Models;

use App\Models\Kategori;
use App\Models\Transaksi;
use App\Models\DetailUserOutlet;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outlet extends Model
{
    use SoftDeletes;

    protected $table = 'outlets';

    protected $fillable = [
        'nama_outlet',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('outlet')
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

                return "Data outlet berhasil {$action} oleh {$user}.";
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

    /**
     * Relasi ke kategori melalui pivot outlet_kategori
     */
    public function kategori()
    {
        return $this->belongsToMany(Kategori::class, 'outlet_kategori')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Relasi ke detail user outlet (banyak pengelola)
     */
    public function detailUsers()
    {
        return $this->hasMany(DetailUserOutlet::class, 'outlet_id');
    }

    /**
     * Relasi ke transaksi outlet
     */
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'outlet_id');
    }
}
