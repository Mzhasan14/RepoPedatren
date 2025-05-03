<?php

namespace App\Services\Administrasi\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FilterCatatanKognitifService
{
    /**
     * Panggil semua filter berurutan
     */
    public function applyAllFilters(Builder $query, Request $request): Builder
    {
        $query = $this->applyJenisKelaminFilter($query, $request);
        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyWilayahFilter($query, $request);
        $query = $this->applyNegaraFilter($query, $request);
        $query = $this->applyLembagaFilter($query, $request);
        $query = $this->applyPhoneNumberFilter($query, $request);
        $query = $this->applyPeriodeFilter($query, $request);
        $query = $this->applyMateriFilter($query, $request);
        $query = $this->applyScoreFilter($query, $request);
        return $query;
    }
    private function applyNegaraFilter(Builder $query, Request $request): Builder
{              // Filter berdasarkan lokasi (negara, provinsi, kabupaten, kecamatan, desa)
              if ($request->filled('negara')) {
                $query->leftJoin('negara', 'CatatanBiodata.negara_id', '=', 'negara.id')
                    ->where('negara.nama_negara', $request->negara);
        
                if ($request->filled('provinsi')) {
                    $query->leftJoin('provinsi', 'CatatanBiodata.provinsi_id', '=', 'provinsi.id')
                        ->where('provinsi.nama_provinsi', $request->provinsi);
        
                    if ($request->filled('kabupaten')) {
                        $query->leftJoin('kabupaten', 'CatatanBiodata.kabupaten_id', '=', 'kabupaten.id')
                            ->where('kabupaten.nama_kabupaten', $request->kabupaten);
        
                        if ($request->filled('kecamatan')) {
                            $query->leftJoin('kecamatan', 'CatatanBiodata.kecamatan_id', '=', 'kecamatan.id')
                                ->where('kecamatan.nama_kecamatan', $request->kecamatan);
                        }
                    }
                }
            }
        return $query;
        }
        private function applyNamaFilter(Builder $query, Request $request): Builder
{    
            // Filter Search Nama
            if ($request->filled('nama')) {
                $query->whereRaw("MATCH(CatatanBiodata.nama) AGAINST(? IN BOOLEAN MODE)", [$request->nama]);
            }
        return $query;
    }
    private function applyLembagaFilter(Builder $query, Request $request): Builder
{    
            // Filter Lembaga
            if ($request->filled('lembaga')) {
                $query->where('lembaga.nama_lembaga', strtolower($request->lembaga));
        
                if ($request->filled('jurusan')) {
                    $query->where('jurusan.nama_jurusan', strtolower($request->jurusan));
        
                    if ($request->filled('kelas')) {
                        $query->where('kelas.nama_kelas', strtolower($request->kelas));
        
                        if ($request->filled('rombel')) {
                            $query->where('rombel.nama_rombel', strtolower($request->rombel));
                        }
                    }
                }
            }
        return $query;
    }
    private function applyWilayahFilter(Builder $query, Request $request): Builder
{            // Filter Wilayah
            if ($request->filled('wilayah')) {
                $wilayah = strtolower($request->wilayah);
                $query->where('wilayah.nama_wilayah', $wilayah);
        
                if ($request->filled('blok')) {
                    $blok = strtolower($request->blok);
                    $query->where('blok.nama_blok', $blok);
        
                    if ($request->filled('kamar')) {
                        $kamar = strtolower($request->kamar);
                        $query->where('kamar.nama_kamar', $kamar);
                    }
                }
            }
        return $query;
    }
    private function applyJenisKelaminFilter(Builder $query, Request $request): Builder
 {       
            // Filter Jenis Kelamin
            if ($request->filled('jenis_kelamin')) {
                $jenis_kelamin = strtolower($request->jenis_kelamin);
                if ($jenis_kelamin == 'laki-laki') {
                    $query->where('CatatanBiodata.jenis_kelamin', 'l');
                } else if ($jenis_kelamin == 'perempuan') {
                    $query->where('CatatanBiodata.jenis_kelamin', 'p');
                }
            }
        return $query;
    }
    private function applyPhoneNumberFilter(Builder $query, Request $request): Builder
   {     
            // Filter Nomor Telepon
            if ($request->filled('phone_number')) {
                $query->where(function ($q) use ($request) {
                    if (strtolower($request->phone_number) === 'mempunyai') {
                        $q->whereNotNull('CatatanBiodata.no_telepon')
                          ->where('CatatanBiodata.no_telepon', '!=', '');
                    } elseif (strtolower($request->phone_number) === 'tidak mempunyai') {
                        $q->where(function($q2) {
                            $q2->whereNull('CatatanBiodata.no_telepon')
                               ->orWhere('CatatanBiodata.no_telepon', '');
                        });
                    }
                });
            }
        return $query;
    }
    private function applyPeriodeFilter(Builder $query, Request $request): Builder
{            // Filter Periode
            if ($request->filled('periode')) {
                [$year, $month] = explode('-', $request->periode);
                $query->whereYear('catatan_kognitif.created_at', $year)
                      ->whereMonth('catatan_kognitif.created_at', $month);
            }
        return $query;
    }
    private function applyMateriFilter(Builder $query, Request $request): Builder
{            // Filter berdasarkan kategori catatan kognitif
            if ($request->filled('materi')) {
                $materiMap = [
                    'Kebahasaan' => 'kebahasaan_nilai',
                    'Baca Kitab Kuning' => 'baca_kitab_kuning_nilai',
                    'Hafalan Tahfidz' => 'hafalan_tahfidz_nilai',
                    'Furudul Ainiyah' => 'furudul_ainiyah_nilai',
                    'Tulis Al-Quran' => 'tulis_alquran_nilai',
                    'Baca Al-Quran' => 'baca_alquran_nilai',
                ];
    
                $kategori = $request->materi;
    
                if (array_key_exists($kategori, $materiMap)) {
                    $query->whereNotNull($materiMap[$kategori]);
                }
            }
        return $query;
    }
    private function applyScoreFilter(Builder $query, Request $request): Builder
{            // Filter berdasarkan skor nilai dari semua kategori penilaian kognitif
            if ($request->filled('score') && in_array($request->score, ['A', 'B', 'C', 'D', 'E'])) {
                $materiFields = [
                    'catatan_kognitif.kebahasaan_nilai',
                    'catatan_kognitif.baca_kitab_kuning_nilai',
                    'catatan_kognitif.hafalan_tahfidz_nilai',
                    'catatan_kognitif.furudul_ainiyah_nilai',
                    'catatan_kognitif.tulis_alquran_nilai',
                    'catatan_kognitif.baca_alquran_nilai',
                ];
    
                $query->where(function ($q) use ($materiFields, $request) {
                    foreach ($materiFields as $field) {
                        $q->orWhere($field, $request->score);
                    }
                });
            }
        return $query;
    }
}