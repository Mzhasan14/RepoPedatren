<?php

namespace App\Models;

use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Rombel;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Lembaga;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPendidikan extends Model
{
    protected $table = 'riwayat_pendidikan';

    protected $guarded = ['id'];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class, 'lembaga_id');
    }
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class, 'rombel_id');
    }
      public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }
}
