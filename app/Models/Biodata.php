<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Models\Alamat\Negara;
use App\Models\Alamat\Provinsi;
use App\Models\Alamat\Kabupaten;
use App\Models\Alamat\Kecamatan;
use App\Models\Pegawai\Pegawai;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Biodata extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'biodata';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'negara_id',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'jalan',
        'kode_pos',
        'nama',
        'no_passport',
        'jenis_kelamin',
        'tanggal_lahir',
        'tempat_lahir',
        'nik',
        'no_telepon',
        'no_telepon_2',
        'email',
        'jenjang_pendidikan_terakhir',
        'nama_pendidikan_terakhir',
        'anak_keberapa',
        'dari_saudara',
        'tinggal_bersama',
        'smartcard',
        'status',
        'wafat',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('biodata')
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

                return "Data Biodata berhasil {$action} oleh {$user}.";
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

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'id_kecamatan');
    }
    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'id_kabupaten');
    }
    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'id_provinsi');
    }
    public function negara()
    {
        return $this->belongsTo(Negara::class, 'id_negara');
    }
    public function santri()
    {
        return $this->hasMany(Santri::class, 'biodata_id');
    }
    public function berkas()
    {
        return $this->hasMany(Berkas::class);
    }
    public function pegawai()
    {
        return $this->hasMany(Pegawai::class);
    }

    public function keluarga()
    {
        return $this->hasMany(Keluarga::class, 'id_biodata', 'id');
    }
}
