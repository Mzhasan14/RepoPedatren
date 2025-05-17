<?php

namespace App\Services\PesertaDidik\Filters;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class FilterBersaudaraService
{
    public function bersaudaraFilters(Builder $query, Request $request): Builder
    {
        $query = $this->applyAlamatFilter($query, $request);
        $query = $this->applyJenisKelaminFilter($query, $request);
        $query = $this->applySmartcardFilter($query, $request);
        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyWilayahFilter($query, $request);
        $query = $this->applyLembagaPendidikanFilter($query, $request);
        $query = $this->applyStatusPesertaFilter($query, $request);
        $query = $this->applyStatusWargaPesantrenFilter($query, $request);
        $query = $this->applyAngkatanSantri($query, $request);
        $query = $this->applyAngkatanPelajar($query, $request);
        $query = $this->applyPhoneNumber($query, $request);
        $query = $this->applyPemberkasan($query, $request);
        $query = $this->applySorting($query, $request);
        $query = $this->applyStatusSaudara($query, $request);

        return $query;
    }

    public function applyAlamatFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('negara')) {
            return $query;
        }

        // Filter berdasarkan lokasi (negara, provinsi, kabupaten, kecamatan, desa)
        if ($request->filled('negara')) {
            $query->join('negara', 'b.negara_id', '=', 'negara.id')
                ->where('negara.nama_negara', $request->negara);

            if ($request->filled('provinsi')) {
                $query->leftJoin('provinsi', 'b.provinsi_id', '=', 'provinsi.id')
                    ->where('provinsi.nama_provinsi', $request->provinsi);

                if ($request->filled('kabupaten')) {
                    // Pastikan join ke tabel kabupaten dilakukan sebelum pemakaian filter
                    $query->where('kb.nama_kabupaten', $request->kabupaten);

                    if ($request->filled('kecamatan')) {
                        $query->leftJoin('kecamatan', 'b.kecamatan_id', '=', 'kecamatan.id')
                            ->where('kecamatan.nama_kecamatan', $request->kecamatan);
                    }
                } else {
                    // Jika nilai kabupaten tidak valid, hasilkan query kosong
                    $query->whereRaw('0 = 1');
                }
            }
        }

        return $query;
    }

    public function applyJenisKelaminFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('jenis_kelamin')) {
            return $query;
        }

        $jenis_kelamin = strtolower($request->jenis_kelamin);
        if ($jenis_kelamin === 'laki-laki' || $jenis_kelamin === 'ayah') {
            $query->where('b.jenis_kelamin', 'l');
        } elseif ($jenis_kelamin === 'perempuan' || $jenis_kelamin === 'ibu') {
            $query->where('b.jenis_kelamin', 'p');
        } else {
            // Jika nilai jenis_kelamin tidak valid, hasilkan query kosong
            $query->whereRaw('0 = 1');
        }

        return $query;
    }

    public function applySmartcardFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('smartcard')) {
            return $query;
        }

        $smartcard = strtolower($request->smartcard);
        if ($smartcard === 'memiliki smartcard') {
            $query->whereNotNull('b.smartcard');
        } elseif ($smartcard === 'tanpa smartcard') {
            $query->whereNull('b.smartcard');
        } else {
            $query->whereRaw('0 = 1');
        }

        return $query;
    }

    public function applyNamaFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('nama')) {
            return $query;
        }

        // tambahkan tanda kutip ganda di awal‑akhir
        $phrase = '"' . trim($request->nama) . '"';

        return $query->whereRaw(
            "MATCH(b.nama) AGAINST(? IN BOOLEAN MODE)",
            [$phrase]
        );
    }

    public function applyWilayahFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('wilayah')) {
            return $query;
        }

        // Filter non domisili pesantren
        if ($request->wilayah === 'non domisili') {

            return $query->where(fn($q) => $q->whereNull('rd.id')->orWhere('rd.status', '!=', 'aktif'));
        }

        $query->where('w.nama_wilayah', $request->wilayah);

        if ($request->filled('blok')) {
            $query->join('blok AS bl', 'rd.blok_id', '=', 'bl.id')
                ->where('bl.nama_blok', $request->blok);

            if ($request->filled('kamar')) {
                $query->join('kamar AS km', 'rd.kamar_id', '=', 'km.id')
                    ->where('km.nama_kamar', $request->kamar);
            }
        }

        return $query;
    }

    public function applyLembagaPendidikanFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('lembaga')) {
            return $query;
        }

        $query->where('l.nama_lembaga', $request->lembaga);

        if ($request->filled('jurusan')) {
            $query->join('jurusan AS j', 'rp.jurusan_id', '=', 'j.id')
                ->where('j.nama_jurusan', $request->jurusan);

            if ($request->filled('kelas')) {
                $query->join('kelas AS kls', 'rp.kelas_id', '=', 'kls.id')
                    ->where('kls.nama_kelas', $request->kelas);

                if ($request->filled('rombel')) {
                    $query->join('rombel AS r', 'rp.rombel_id', '=', 'r.id')
                        ->where('r.nama_rombel', $request->rombel);
                }
            }
        }

        return $query;
    }

    public function applyStatusPesertaFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('status')) {
            return $query;
        }

        switch (strtolower($request->status)) {
            case 'santri':
                $query->where('s.status', 'aktif');
                break;
            case 'santri non pelajar':
                $query->where('s.status', 'aktif')
                    ->where(fn($q) => $q->whereNull('rp.id')->orWhere('rp.status', '!=', 'aktif'));
                break;
            case 'pelajar':
                $query->where('rp.status', 'aktif');
                break;
            case 'pelajar non santri':
                $query->where('rp.status', 'aktif')
                    ->where('s.status', '!=', 'aktif');
                break;
            case 'santri-pelajar':
            case 'pelajar-santri':
                $query->where('rp.status', 'aktif')
                    ->where('s.status', 'aktif');
                break;
            default:
                $query->whereRaw('0 = 1');
        }

        return $query;
    }

    public function applyStatusWargaPesantrenFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('warga_pesantren')) {
            return $query;
        }

        $flag = strtolower($request->warga_pesantren);
        if ($flag === 'memiliki niup') {
            $query->whereNotNull('wp.niup');
        } elseif ($flag === 'tanpa niup') {
            $query->whereNull('wp.niup');
        } else {
            $query->whereRaw('0 = 1');
        }

        return $query;
    }

    public function applyAngkatanSantri(Builder $query, Request $request): Builder
    {
        if (! $request->filled('angkatan_santri')) {
            return $query;
        }


        $query->whereYear('s.tanggal_masuk', $request->angkatan_santri);
        return $query;
    }

    public function applyAngkatanPelajar(Builder $query, Request $request): Builder
    {
        if (! $request->filled('angkatan_pelajar')) {
            return $query;
        }

        $query->whereYear('rp.tanggal_masuk', $request->angkatan_pelajar);
        return $query;
    }

    public function applyPhoneNumber(Builder $query, Request $request): Builder
    {
        if (! $request->filled('phone_number')) {
            return $query;
        }

        $pn = strtolower($request->phone_number);
        if ($pn === 'memiliki phone number') {
            $query->whereNotNull('b.no_telepon')
                ->where('b.no_telepon', '!=', '');
        } elseif ($pn === 'tidak ada phone number') {
            $query->where(fn($q) => $q->whereNull('b.no_telepon')->orWhere('b.no_telepon', '=', ''));
        } else {
            $query->whereRaw('0 = 1');
        }

        return $query;
    }

    public function applyPemberkasan(Builder $query, Request $request): Builder
    {
        if (! $request->filled('pemberkasan')) {
            return $query;
        }

        switch (strtolower($request->pemberkasan)) {
            case 'tidak ada berkas':
                $query->whereNull('br.biodata_id');
                break;
            case 'tidak ada foto diri':
                $query->where('br.jenis_berkas_id', 4)
                    ->whereNull('br.file_path');
                break;
            case 'memiliki foto diri':
                $query->where('br.jenis_berkas_id', 4)
                    ->whereNotNull('br.file_path');
                break;
            case 'tidak ada kk':
                $query->where('br.jenis_berkas_id', 1)
                    ->whereNull('br.file_path');
                break;
            case 'tidak ada akta kelahiran':
                $query->where('br.jenis_berkas_id', 3)
                    ->whereNull('br.file_path');
                break;
            case 'tidak ada ijazah':
                $query->where('br.jenis_berkas_id', 5)
                    ->whereNull('br.file_path');
                break;
            default:
                $query->whereRaw('0 = 1');
        }

        return $query;
    }

    public function applySorting(Builder $query, Request $request): Builder
    {
        if (! $request->filled('sort_by')) {
            return $query;
        }

        $allowed = ['id', 'nama', 'niup', 'jenis_kelamin'];
        $by      = strtolower($request->sort_by);
        $order   = ($request->filled('sort_order') && strtolower($request->sort_order) === 'desc') ? 'desc' : 'asc';

        if (in_array($by, $allowed, true)) {
            $query->orderBy($by, $order);
        } else {
            $query->whereRaw('0 = 1');
        }

        return $query;
    }

    public function applyStatusSaudara(Builder $query, Request $request)
    {
        if (! $request->filled('status_saudara')) {
            return $query;
        }

        $status_saudara = strtolower($request->status_saudara);
        switch ($status_saudara) {
            case 'ibu kandung terisi':
                $query->whereNotNull('parents.nama_ibu');
                break;
            case 'ibu kandung tidak terisi':
                $query->where(fn($sub) => $sub->whereNull('parents.nama_ibu')->orWhere('parents.nama_ibu', 'Tidak Diketahui'));
                break;
            case 'kk sama dgn ibu kandung':
                // Pastikan sudah join subquery ibu_info di atas
                $query->whereColumn('k.no_kk', 'parents.no_kk');
                break;
            case 'kk berbeda dgn ibu kandung':
                // Pastikan sudah join subquery ibu_info di atas
                $query->whereColumn('k.no_kk', '!=', 'parents.no_kk');
                break;
            default:
                $query->whereRaw('0 = 1');
                break;
        }
        return $query;
    }
}
