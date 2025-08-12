<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RekapTahfidz extends Model
{
    use SoftDeletes;

    protected $table = 'rekap_tahfidz';

    protected $fillable = [
        'santri_id',
        'tahun_ajaran_id',
        'total_surat',
        'persentase_khatam',
        'surat_tersisa', 
        'sisa_persentase',
        'total_ayat',
        'persentase_ayat',
        'jumlah_setoran',
        'rata_rata_nilai',
        'tanggal_mulai',
        'tanggal_selesai',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Relasi ke Santri
    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    // Relasi ke Tahun Ajaran
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    // Relasi ke User (yang membuat)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke User (yang update)
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Relasi ke User (yang hapus soft delete)
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
