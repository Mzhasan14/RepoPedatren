<?php

namespace App\Models;

use App\Models\Berkas;
use App\Models\Khadam;
use App\Models\Keluarga;
use App\Models\Alamat\Desa;
use App\Models\JenisBerkas;
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
    protected $guarded = ['id'];

    // // Master lookups
    // public function kecamatan()
    // {
    //     return $this->belongsTo(Kecamatan::class, 'id_kecamatan');
    // }
    // public function kabupaten()
    // {
    //     return $this->belongsTo(Kabupaten::class, 'id_kabupaten');
    // }
    // public function provinsi()
    // {
    //     return $this->belongsTo(Provinsi::class, 'id_provinsi');
    // }
    // public function negara()
    // {
    //     return $this->belongsTo(Negara::class, 'id_negara');
    // }

    // // “Latest” polymorphic relations
    // public function wargaPesantrenAktif()
    // {
    //     return $this->hasOne(WargaPesantren::class, 'id_biodata')
    //         ->where('status', true)
    //         ->latestOfMany('id');
    // }

    // public function pasFoto()
    // {
    //     // ambil sekali saja ID jenis berkas “Pas foto”
    //     $jenisId = JenisBerkas::where('nama_jenis_berkas', 'Pas foto')->value('id');
    //     return $this->hasOne(Berkas::class, 'id_biodata')
    //         ->where('id_jenis_berkas', $jenisId)
    //         ->latestOfMany('id');
    // }

    // // relasi keluarga (semua anggota satu no_kk)
    // public function keluarga()
    // {
    //     return $this->hasMany(Keluarga::class, 'id_biodata');
    // }

    // public function berkas()
    // {
    //     return $this->hasMany(Berkas::class, 'id_biodata', 'id');
    // }

    // public function scopeActive($query)
    // {
    //     return $query->where('status', true);
    // }

    // public function peserta_didik()
    // {
    //     return $this->hasOne(PesertaDidik::class, 'id_biodata', 'id');
    // }

    // public function pegawai()
    // {
    //     return $this->hasMany(Pegawai::class, 'id_biodata', 'id');
    // }

    // public function khadam()
    // {
    //     return $this->hasMany(Khadam::class, 'id_biodata', 'id');
    // }

    // public function keluarga()
    // {
    //     return $this->hasMany(Keluarga::class, 'no_kk', 'no_kk');
    // }
    // public function wargaPesantren()
    // {
    //     return $this->hasMany(Keluarga::class, 'id_biodata', 'id');
    // }

    // public function kabupaten(): BelongsTo
    // {
    //     return $this->belongsTo(Kabupaten::class, 'id_kabupaten');
    // }

    // public function berkas(): HasMany
    // {
    //     return $this->hasMany(Berkas::class, 'id_biodata');
    // }

    // public function jenisBerkas()
    // {
    //     return $this->belongsTo(JenisBerkas::class, 'id_jenis_berkas');
    // }
}
