<?php

namespace App\Services\Pegawai\Filters;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterPengajarService
{
    public function applyPengajarFilters($query, Request $request)
    {
        $query = $this->applyAlamatFilter($query, $request);
        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyLembagaFilter($query, $request);
        $query = $this->applyGolonganJabatanFilter($query, $request);
        $query = $this->applyWargaPesantrenFilter($query, $request);
        $query = $this->applyPemberkasanFilter($query, $request);
        $query = $this->applyUmurFilter($query, $request);
        $query = $this->applyPhoneFilter($query, $request);
        $query = $this->applyJenisKelaminFilter($query, $request);

        $query = $this->applyMapelFilter($query, $request);
        $query = $this->applyMasaKerjaFilter($query, $request);
        $query = $this->applyJabatanPengajarFilter($query, $request);

        return $query;
    }

    private function applyPhoneFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('phone_number')) {
            $phone = strtolower($request->phone_number);

            if ($phone === 'memiliki phone number') {
                // Salah satu dari no_telepon atau no_telepon_2 harus terisi
                $query->where(function ($q) {
                    $q->whereNotNull('b.no_telepon')->where('b.no_telepon', '!=', '')
                        ->orWhere(function ($q2) {
                            $q2->whereNotNull('b.no_telepon_2')->where('b.no_telepon_2', '!=', '');
                        });
                });
            } elseif ($phone === 'tidak ada phone number') {
                // Keduanya harus kosong atau null
                $query->where(function ($q) {
                    $q->where(function ($q1) {
                        $q1->whereNull('b.no_telepon')->orWhere('b.no_telepon', '');
                    })->where(function ($q2) {
                        $q2->whereNull('b.no_telepon_2')->orWhere('b.no_telepon_2', '');
                    });
                });
            } else {
                $query->whereRaw('0 = 1'); // default error fallback
            }
        }

        return $query;
    }

    private function applyJenisKelaminFilter(Builder $query, Request $request): Builder
    {

        // 🔹 Filter jenis kelamin (dari biodata)
        if ($request->filled('jenis_kelamin')) {
            // normalisasi input: lowercase, buang spasi dan strip
            $raw = $request->input('jenis_kelamin');
            $input = strtolower(str_replace([' ', '-'], '', $raw));

            // definisi map untuk male ('l') & female ('p')
            $mapping = [
                'l' => ['l', 'laki', 'lakilaki', 'pria', 'ayah'],
                'p' => ['p', 'perempuan', 'wanita', 'ibu'],
            ];

            if (in_array($input, $mapping['l'], true)) {
                $query->where('b.jenis_kelamin', 'l');
            } elseif (in_array($input, $mapping['p'], true)) {
                $query->where('b.jenis_kelamin', 'p');
            }
            // jika input tidak cocok, kita skip filter—hasil tidak akan di‐empty
        }

        return $query;
    }

    private function applySmartcardFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('smartcard')) {
            $smartcard = strtolower($request->smartcard);
            if ($smartcard == 'memiliki smartcard') {
                $query->whereNotNull('b.smartcard');
            } elseif ($smartcard == 'tanpa smartcard') {
                $query->whereNull('b.smartcard');
            } else {
                $query->whereRaw('0 = 1');
            }
        }

        return $query;
    }

    private function applyUmurFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('umur')) {
            $umurInput = $request->umur;

            if (strpos($umurInput, '-') !== false) {
                [$umurMin, $umurMax] = explode('-', $umurInput);
            } else {
                $umurMin = $umurInput;
                $umurMax = $umurInput;
            }

            $query->whereBetween(
                DB::raw('TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE())'),
                [(int) $umurMin, (int) $umurMax]
            );
        }

        return $query;
    }

    private function applyLembagaFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('lembaga')) {
            $query->whereRaw('LOWER(l.nama_lembaga) = ?', [strtolower($request->lembaga)]);
        }

        return $query;
    }

    private function applyAlamatFilter(Builder $query, Request $request): Builder
    {
        // Filter berdasarkan lokasi (negara, provinsi, kabupaten, kecamatan, desa)
        if ($request->filled('negara')) {
            $negara = strtolower($request->negara);
            $query->join('negara', 'b.negara_id', '=', 'negara.id')
                ->whereRaw('LOWER(negara.nama_negara) = ?', [$negara]);
        }

        if ($request->filled('provinsi')) {
            $provinsi = strtolower($request->provinsi);
            $query->leftJoin('provinsi', 'b.provinsi_id', '=', 'provinsi.id')
                ->whereRaw('LOWER(provinsi.nama_provinsi) = ?', [$provinsi]);
        }

        if ($request->filled('kabupaten')) {
            $kabupaten = strtolower($request->kabupaten);
            $query->leftJoin('kabupaten', 'b.kabupaten_id', '=', 'kabupaten.id')
                ->whereRaw('LOWER(kabupaten.nama_kabupaten) = ?', [$kabupaten]);
        }

        if ($request->filled('kecamatan')) {
            $kecamatan = strtolower($request->kecamatan);
            $query->leftJoin('kecamatan', 'b.kecamatan_id', '=', 'kecamatan.id')
                ->whereRaw('LOWER(kecamatan.nama_kecamatan) = ?', [$kecamatan]);
        }

        return $query;
    }

    public function applyNamaFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('nama')) {
            return $query;
        }

        $nama = trim($request->nama);

        return $query->whereRaw('LOWER(b.nama) LIKE ?', ['%' . strtolower($nama) . '%']);
    }

    private function applyGolonganJabatanFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('golongan_jabatan')) {
            $query->where('kg.nama_kategori_golongan', strtolower($request->golongan_jabatan));
            if ($request->filled('golongan')) {
                $query->where('g.nama_golongan', strtolower($request->golongan));
            }
        }

        return $query;
    }

    private function applyMapelFilter(Builder $query, Request $request): Builder
    {
        if ($request->has('materi_ajar')) {
            $value = $request->materi_ajar;

            if ($value === '0') {
                $query->havingRaw('COUNT(DISTINCT mata_pelajaran.id) = 0');
            } elseif ($value === '1') {
                $query->havingRaw('COUNT(DISTINCT mata_pelajaran.id) = 1');
            } elseif ($value === '>1') {
                $query->havingRaw('COUNT(DISTINCT mata_pelajaran.id) > 1');
            } elseif ($value === '2') {
                $query->havingRaw('COUNT(DISTINCT mata_pelajaran.id) = 2');
            } elseif ($value === '>2') {
                $query->havingRaw('COUNT(DISTINCT mata_pelajaran.id) > 2');
            } elseif ($value === '3') {
                $query->havingRaw('COUNT(DISTINCT mata_pelajaran.id) = 3');
            } elseif ($value === '>3') {
                $query->havingRaw('COUNT(DISTINCT mata_pelajaran.id) > 3');
            }
        }

        return $query;
    }

    private function applyMasaKerjaFilter(Builder $query, Request $request): Builder
    {
        $masaKerja = $request->input('masa_kerja');
        $today = now(); // tanggal hari ini

        if (preg_match('/^(\d+)-(\d+)$/', $masaKerja, $matches)) {
            // Format "min-max", misal: "1-5"
            $min = (int) $matches[1];
            $max = (int) $matches[2];

            $query->whereRaw('
                TIMESTAMPDIFF(YEAR, pengajar.tahun_masuk, COALESCE(pengajar.tahun_akhir, ?)) BETWEEN ? AND ?
            ', [$today, $min, $max]);
        } elseif (is_numeric($masaKerja)) {
            // Format tunggal, misal: "1" untuk kurang dari 1 tahun
            $query->whereRaw('
                TIMESTAMPDIFF(YEAR, pengajar.tahun_masuk, COALESCE(pengajar.tahun_akhir, ?)) < ?
            ', [$today, (int) $masaKerja]);
        }

        return $query;
    }

    private function applyJabatanPengajarFilter(Builder $query, Request $request): Builder
    {
        if ($request->has('jabatan')) {
            $query->where('pengajar.jabatan', strtolower($request->jabatan));
        }

        return $query;
    }

    private function applyWargaPesantrenFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('warga_pesantren')) {
            $status = strtolower($request->warga_pesantren);
            if ($status === 'memiliki niup') {
                $query->whereNotNull('wp.niup');
            } elseif ($status === 'tanpa niup') {
                $query->whereNull('wp.niup');
            } else {
                $query->whereRaw('0 = 1');
            }
        }

        return $query;
    }

    private function applyPemberkasanFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('pemberkasan')) {
            return $query;
        }

        switch (strtolower($request->pemberkasan)) {
            case 'tidak ada berkas':
                $query->whereNull('br.biodata_id');
                break;

            case 'tidak ada foto diri':
                $query->where(function ($q) {
                    $q->where('br.jenis_berkas_id', 4)->whereNull('br.file_path')
                        ->orWhereNull('br.jenis_berkas_id');
                });
                break;

            case 'memiliki foto diri':
                $query->where('br.jenis_berkas_id', 4)->whereNotNull('br.file_path');
                break;

            case 'memiliki kk':
                $query->where('br.jenis_berkas_id', 1)->whereNotNull('br.file_path');
                break;

            case 'memiliki akta kelahiran':
                $query->where('br.jenis_berkas_id', 3)->whereNotNull('br.file_path');
                break;

            case 'memiliki ijazah':
                $query->where('br.jenis_berkas_id', 5)->whereNotNull('br.file_path');
                break;

            default:
                $query->whereRaw('0 = 1'); // fallback: tidak ada hasil
        }

        return $query;
    }
}
