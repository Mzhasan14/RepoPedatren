<?php

namespace App\Http\Controllers\api\keluarga;

use App\Models\OrangTuaWali;
use Illuminate\Http\Request;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\api\FilterController;

class OrangTuaWaliController extends Controller
{
    protected $filterController;

    public function __construct(FilterController $filterController)
    {
        $this->filterController = $filterController;
    }

    public function index()
    {
        $ortu = OrangTuaWali::Active()->latest()->paginate(5);
        return new PdResource(true, 'List Orang Tua', $ortu);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_biodata' => 'required|exists:biodata,id',
            'pekerjaan' => 'required|string',
            'penghasilan' => 'nullable|integer',
            'created_by' => 'required',
            'status' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $ortu = OrangTuaWali::create($validator->validated());
        return new PdResource(true, 'Data berhasil Ditambah', $ortu);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ortu = OrangTuaWali::findOrFail($id);
        return new PdResource(true, 'detail data', $ortu);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $ortu = OrangTuaWali::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_biodata' => 'required|exists:biodata,id',
            'pekerjaan' => 'required|string',
            'penghasilan' => 'nullable|integer',
            'updated_by' => 'nullable',
            'status' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $ortu->update($validator->validated());
        return new PdResource(true, 'data berhasil diubah', $ortu);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ortu = OrangTuaWali::findOrFail($id);

        $ortu->delete();
        return new PdResource(true, 'Data berhasil dihapus', null);
    }


    public function orangTuaWali(Request $request)
    {
        $query = OrangTuaWali::Active()
            ->join('biodata', 'orang_tua_wali.id_biodata', '=', 'biodata.id')
            ->join('keluarga', 'biodata.id', '=', 'keluarga.id_biodata')
            ->leftjoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
            ->leftjoin('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
            ->select(
                'orang_tua_wali.id',
                DB::raw("COALESCE(biodata.nik, biodata.no_passport) as identitas"),
                'biodata.nama',
                'biodata.no_telepon',
                'biodata.no_telepon_2',
                DB::raw("CONCAT('Kab. ', kabupaten.nama_kabupaten) as kota_asal"),
                'biodata.updated_at as tanggal_update',
                'biodata.created_at as tanggal_input',
                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
            )
            ->groupBy(
                'orang_tua_wali.id',
                'biodata.nik',
                'biodata.no_passport',
                'biodata.nama',
                'biodata.no_telepon',
                'biodata.no_telepon_2',
                'kabupaten.nama_kabupaten',
                'tanggal_update',
                'tanggal_input'
            );

        // Filter Umum (Alamat dan Jenis Kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        // filter jenis kelamin peserta didik
        if ($request->filled('jenis_kelamin_peserta_didik')) {
            $jenis_kelamin_peserta_didik = strtolower($request->jenis_kelamin_peserta_didik);

            // Validasi input dan tentukan jenis kelamin
            $jenis_kelamin = null;
            if ($jenis_kelamin_peserta_didik === 'santri/pelajar putra') {
                $jenis_kelamin = 'l';
            } elseif ($jenis_kelamin_peserta_didik === 'santri/pelajar putri') {
                $jenis_kelamin = 'p';
            }

            // Jika input valid, terapkan filter
            if ($jenis_kelamin) {
                $query->whereExists(function ($q) use ($jenis_kelamin) {
                    $q->select(DB::raw(1))
                        ->from('keluarga as k')
                        ->join('biodata as b_anak', 'k.id_biodata', '=', 'b_anak.id')
                        ->join('peserta_didik as pd', 'b_anak.id', '=', 'pd.id_biodata') // Pastikan anak itu peserta didik
                        ->whereColumn('k.no_kk', 'keluarga.no_kk') // Dalam satu KK
                        ->where('b_anak.jenis_kelamin', $jenis_kelamin)
                        ->limit(1); // Batasi untuk mempercepat query
                });
            }
        }

        // filter orang tua dari
        if ($request->filled('orangtua_dari')) {
            $kategori = strtolower($request->orangtua_dari);

            $query->whereExists(function ($subQuery) use ($kategori) {
                $subQuery->select(DB::raw(1))
                    ->from('keluarga as k')
                    ->join('biodata as b_anak', 'k.id_biodata', '=', 'b_anak.id')
                    ->join('peserta_didik as pd', 'b_anak.id', '=', 'pd.id_biodata')
                    ->whereColumn('k.no_kk', 'keluarga.no_kk'); // Dalam satu KK

                // Filter berdasarkan kategori yang dipilih
                if ($kategori === 'santri') {
                    $subQuery->join('santri as s', 'pd.id', '=', 's.id_peserta_didik');
                } elseif ($kategori === 'santri non-pelajar') {
                    $subQuery->join('santri as s', 'pd.id', '=', 's.id_peserta_didik')
                        ->whereNotExists(function ($q) {
                            $q->select(DB::raw(1))
                                ->from('pelajar as p')
                                ->whereColumn('p.id_peserta_didik', 'pd.id');
                        });
                } elseif ($kategori === 'pelajar') {
                    $subQuery->join('pelajar as p', 'pd.id', '=', 'p.id_peserta_didik');
                } elseif ($kategori === 'pelajar non-santri') {
                    $subQuery->join('pelajar as p', 'pd.id', '=', 'p.id_peserta_didik')
                        ->whereNotExists(function ($q) {
                            $q->select(DB::raw(1))
                                ->from('santri as s')
                                ->whereColumn('s.id_peserta_didik', 'pd.id');
                        });
                } elseif ($kategori === 'santri sekaligus pelajar') {
                    $subQuery->join('santri as s', 'pd.id', '=', 's.id_peserta_didik')
                        ->join('pelajar as p', 'pd.id', '=', 'p.id_peserta_didik');
                }
            });
        }


        // Filter Wafat atau Hidup
        if ($request->filled('wafat')) {
            $wafat = strtolower($request->wafat);
            if ($wafat == 'sudah wafat') {
                $query->where('orang_tua_wali.wafat', true);
            } else if ($wafat == 'masih hidup') {
                $query->where('orang_tua_wali.wafat', false);
            }
        }

        // Filter Smartcard
        if ($request->filled('smartcard')) {
            $smartcard = strtolower($request->smartcard);
            if ($smartcard == 'memiliki smartcard') {
                $query->whereNotNull('biodata.smartcard');
            } else if ($smartcard == 'tanpa smartcard') {
                $query->whereNull('biodata.smartcard');
            }
        }

        // Filter No Telepon
        if ($request->filled('phone_number')) {
            $phone_number = strtolower($request->phone_number);
            if ($phone_number == 'memiliki phone number') {
                $query->whereNotNull('biodata.no_telepon')
                    ->where('biodata.no_telepon', '!=', '');
            } else if ($phone_number == 'tidak ada phone number') {
                $query->whereNull('biodata.no_telepon')
                    ->where('biodata.no_telepon', '=', '');
            }
        }

        // Ambil jumlah data per halaman (default 10 jika tidak diisi)
        $perPage = $request->input('limit', 25);

        // Ambil halaman saat ini (jika ada)
        $currentPage = $request->input('page', 1);

        // Menerapkan pagination ke hasil
        $hasil = $query->paginate($perPage, ['*'], 'page', $currentPage);

        // Jika Data Kosong
        if ($hasil->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "Data tidak ditemukan",
                "code" => 200
            ], 200);
        }

        return response()->json([
            "total_data" => $hasil->total(),
            "current_page" => $hasil->currentPage(),
            "per_page" => $hasil->perPage(),
            "total_pages" => $hasil->lastPage(),
            "data" => $hasil->map(function ($item) {
                return [
                    "id" => $item->id,
                    "nik/no_passport" => $item->identitas,
                    "nama" => $item->nama,
                    "no_telepon" => $item->no_telepon,
                    "no_telepon_2" => $item->no_telepon_2,
                    "nama_kabupaten" => $item->kota_asal,
                    "tanggal_update" => $item->tanggal_update,
                    "tanggal_input" => $item->tanggal_input,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }

    public function wali(Request $request)
    {
        $query = OrangTuaWali::Active()
            ->join('biodata', 'orang_tua_wali.id_biodata', '=', 'biodata.id')
            ->leftjoin('keluarga', 'biodata.id', '=', 'keluarga.id_biodata')
            ->leftjoin('peserta_didik', 'biodata.id', '=', 'peserta_didik.id_biodata')
            ->leftjoin('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
            ->leftjoin('pelajar', 'peserta_didik.id', '=', 'pelajar.id_peserta_didik')
            ->leftjoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
            ->leftjoin('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
            ->select(
                'biodata.id',
                DB::raw("COALESCE(biodata.nik, biodata.no_passport) as identitas"),
                'biodata.nama',
                'biodata.no_telepon',
                'biodata.no_telepon_2',
                DB::raw("CONCAT('Kab. ', kabupaten.nama_kabupaten) as kota_asal"),
                'biodata.updated_at as tanggal_update',
                'biodata.created_at as tanggal_input',
                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
            )
            ->groupBy(
                'biodata.id',
                'biodata.nik',
                'biodata.no_passport',
                'biodata.nama',
                'biodata.no_telepon',
                'biodata.no_telepon_2',
                'kabupaten.nama_kabupaten',
                'tanggal_update',
                'tanggal_input'
            )->where('orang_tua_wali.wali', true);

        // Filter Umum (Alamat dan Jenis Kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        // filter jenis kelamin peserta didik
        if ($request->filled('jenis_kelamin_peserta_didik')) {
            $jenis_kelamin_peserta_didik = strtolower($request->jenis_kelamin_peserta_didik);

            // Validasi input dan tentukan jenis kelamin
            $jenis_kelamin = null;
            if ($jenis_kelamin_peserta_didik === 'santri/pelajar putra') {
                $jenis_kelamin = 'l';
            } elseif ($jenis_kelamin_peserta_didik === 'santri/pelajar putri') {
                $jenis_kelamin = 'p';
            }

            // Jika input valid, terapkan filter
            if ($jenis_kelamin) {
                $query->whereExists(function ($q) use ($jenis_kelamin) {
                    $q->select(DB::raw(1))
                        ->from('keluarga as k')
                        ->join('biodata as b_anak', 'k.id_biodata', '=', 'b_anak.id')
                        ->join('peserta_didik as pd', 'b_anak.id', '=', 'pd.id_biodata') // Pastikan anak itu peserta didik
                        ->whereColumn('k.no_kk', 'keluarga.no_kk') // Dalam satu KK
                        ->where('b_anak.jenis_kelamin', $jenis_kelamin)
                        ->limit(1); // Batasi untuk mempercepat query
                });
            }
        }

        // filter orang tua dari
        if ($request->filled('orangtua_dari')) {
            $kategori = strtolower($request->orangtua_dari);

            $query->whereExists(function ($subQuery) use ($kategori) {
                $subQuery->select(DB::raw(1))
                    ->from('keluarga as k')
                    ->join('biodata as b_anak', 'k.id_biodata', '=', 'b_anak.id')
                    ->join('peserta_didik as pd', 'b_anak.id', '=', 'pd.id_biodata')
                    ->whereColumn('k.no_kk', 'keluarga.no_kk'); // Dalam satu KK

                // Filter berdasarkan kategori yang dipilih
                if ($kategori === 'santri') {
                    $subQuery->join('santri as s', 'pd.id', '=', 's.id_peserta_didik');
                } elseif ($kategori === 'santri non-pelajar') {
                    $subQuery->join('santri as s', 'pd.id', '=', 's.id_peserta_didik')
                        ->whereNotExists(function ($q) {
                            $q->select(DB::raw(1))
                                ->from('pelajar as p')
                                ->whereColumn('p.id_peserta_didik', 'pd.id');
                        });
                } elseif ($kategori === 'pelajar') {
                    $subQuery->join('pelajar as p', 'pd.id', '=', 'p.id_peserta_didik');
                } elseif ($kategori === 'pelajar non-santri') {
                    $subQuery->join('pelajar as p', 'pd.id', '=', 'p.id_peserta_didik')
                        ->whereNotExists(function ($q) {
                            $q->select(DB::raw(1))
                                ->from('santri as s')
                                ->whereColumn('s.id_peserta_didik', 'pd.id');
                        });
                } elseif ($kategori === 'santri sekaligus pelajar') {
                    $subQuery->join('santri as s', 'pd.id', '=', 's.id_peserta_didik')
                        ->join('pelajar as p', 'pd.id', '=', 'p.id_peserta_didik');
                }
            });
        }


        // Filter Wafat atau Hidup
        if ($request->filled('wafat')) {
            $wafat = strtolower($request->wafat);
            if ($wafat == 'sudah wafat') {
                $query->where('orang_tua_wali.wafat', true);
            } else if ($wafat == 'masih hidup') {
                $query->where('orang_tua_wali.wafat', false);
            }
        }

        // Filter Smartcard
        if ($request->filled('smartcard')) {
            $smartcard = strtolower($request->smartcard);
            if ($smartcard == 'memiliki smartcard') {
                $query->whereNotNull('biodata.smartcard');
            } else if ($smartcard == 'tanpa smartcard') {
                $query->whereNull('biodata.smartcard');
            }
        }

        // Filter No Telepon
        if ($request->filled('phone_number')) {
            $phone_number = strtolower($request->phone_number);
            if ($phone_number == 'memiliki phone number') {
                $query->whereNotNull('biodata.no_telepon')
                    ->where('biodata.no_telepon', '!=', '');
            } else if ($phone_number == 'tidak ada phone number') {
                $query->whereNull('biodata.no_telepon')
                    ->where('biodata.no_telepon', '=', '');
            }
        }

        // Ambil jumlah data per halaman (default 10 jika tidak diisi)
        $perPage = $request->input('limit', 25);

        // Ambil halaman saat ini (jika ada)
        $currentPage = $request->input('page', 1);

        // Menerapkan pagination ke hasil
        $hasil = $query->paginate($perPage, ['*'], 'page', $currentPage);

        // Jika Data Kosong
        if ($hasil->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "Data tidak ditemukan",
                "code" => 404
            ], 404);
        }

        return response()->json([
            "total_data" => $hasil->total(),
            "current_page" => $hasil->currentPage(),
            "per_page" => $hasil->perPage(),
            "total_pages" => $hasil->lastPage(),
            "data" => $hasil->map(function ($item) {
                return [
                    "id" => $item->id,
                    "nik/no_passport" => $item->identitas,
                    "nama" => $item->nama,
                    "no_telepon" => $item->no_telepon,
                    "no_telepon_2" => $item->no_telepon_2,
                    "nama_kabupaten" => $item->kota_asal,
                    "tanggal_update" => $item->tanggal_update,
                    "tanggal_input" => $item->tanggal_input,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
}
