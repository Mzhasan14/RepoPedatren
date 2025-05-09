<?php

namespace App\Models;

use App\Models\Berkas;
use App\Models\Khadam;
use App\Models\Keluarga;
use App\Models\Alamat\Desa;
use App\Models\JenisBerkas;
use Illuminate\Support\Str;
use App\Models\PesertaDidik;
use App\Models\Alamat\Negara;
use App\Models\WargaPesantren;
use App\Models\Alamat\Provinsi;
use App\Models\Pegawai\Pegawai;
use App\Models\Alamat\Kabupaten;
use App\Models\Alamat\Kecamatan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Biodata extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'biodata';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
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
}
