<?php

namespace App\Models;

use App\Models\Kewilayahan\Blok;
use App\Models\Kewilayahan\Kamar;
use App\Models\Kewilayahan\Wilayah;
use Illuminate\Database\Eloquent\Model;

class DomisiliSantri extends Model
{
    protected $table = 'domisili_santri';

    protected $guarded = ['id'];

    public function santri()
    {
        return $this->BelongsTo(Santri::class, 'id_santri', 'id');
    }
    public function wilayah()
    {
        return $this->BelongsTo(Wilayah::class, 'id_wilayah', 'id');
    }
    public function blok()
    {
        return $this->BelongsTo(Blok::class, 'id_blok', 'id');
    }
    public function kamar()
    {
        return $this->BelongsTo(Kamar::class, 'id_kamar', 'id');
    }
}
