<?php

namespace App\Http\Controllers\api\keluarga;

use App\Models\Biodata;
use App\Models\OrangTua;
use Illuminate\Http\Request;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\api\FilterController;

class OrangTuaController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $filterController;

    public function __construct(FilterController $filterController)
    {
        $this->filterController = $filterController;
    }

    public function index()
    {
        $ortu = OrangTua::Active()->latest()->paginate(5);
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

        $ortu = OrangTua::create($validator->validated());
        return new PdResource(true, 'Data berhasil Ditambah', $ortu);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ortu = OrangTua::findOrFail($id);
        return new PdResource(true, 'detail data', $ortu);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $ortu = OrangTua::findOrFail($id);

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
        $ortu = OrangTua::findOrFail($id);

        $ortu->delete();
        return new PdResource(true, 'Data berhasil dihapus', null);
    }


    public function orang_tua(Request $request)
    {
        $query = OrangTua::Active()
            ->join('biodata', 'orang_tua.id_biodata', '=', 'biodata.id')
            ->leftjoin('peserta_didik', 'biodata.id', '=', 'peserta_didik.id_biodata')
            ->leftjoin('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
            ->leftjoin('pelajar', 'peserta_didik.id', '=', 'pelajar.id_peserta_didik')
            ->leftjoin('keluarga', 'biodata.id', '=', 'keluarga.id_biodata')
            ->leftjoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
            ->leftjoin('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
            ->select(
                'orang_tua.id',
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
                'orang_tua.id',
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

        // Filter berdasarkan kategori "santri/pelajar putra" atau "santri/pelajar putri"
        if ($request->filled('jenis_kelamin_peserta_didik')) {
            $jenis_kelamin_peserta_didik = strtolower($request->jenis_kelamin_peserta_didik);

            if ($jenis_kelamin_peserta_didik === 'santri/pelajar putra') {
                // Filter untuk orang tua yang memiliki anak laki-laki dalam satu KK
                $query->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('keluarga as k_anak')
                        ->join('biodata as b_anak', 'k_anak.id_biodata', '=', 'b_anak.id')
                        ->whereColumn('k_anak.no_kk', 'keluarga.no_kk') // Pastikan dalam satu KK
                        ->where('k_anak.id_status_keluarga', 3) // Status keluarga = Anak
                        ->where('b_anak.jenis_kelamin', 'L'); // Jenis kelamin = L (Laki-laki)
                });
            } elseif ($jenis_kelamin_peserta_didik === 'santri/pelajar putri') {
                // Filter untuk orang tua yang memiliki anak perempuan dalam satu KK
                $query->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('keluarga as k_anak')
                        ->join('biodata as b_anak', 'k_anak.id_biodata', '=', 'b_anak.id')
                        ->whereColumn('k_anak.no_kk', 'keluarga.no_kk') // Pastikan dalam satu KK
                        ->where('k_anak.id_status_keluarga', 3) // Status keluarga = Anak
                        ->where('b_anak.jenis_kelamin', 'P'); // Jenis kelamin = P (Perempuan)
                });
            }
        }

        // filter orangtua_dari
        if ($request->filled('')) {
            $kategori = strtolower($request->kategori);

            $query->whereExists(function ($subQuery) use ($kategori) {
                $subQuery->select(DB::raw(1))
                    ->from('keluarga as k_anak')
                    ->join('biodata as b_anak', 'k_anak.id_biodata', '=', 'b_anak.id')
                    ->join('peserta_didik', 'b_anak.id', '=', 'peserta_didik.id_biodata')
                    ->whereColumn('k_anak.no_kk', 'keluarga.no_kk')
                    ->where('k_anak.id_status_keluarga', 3); // Anak dalam KK

                if ($kategori === 'santri') {
                    $subQuery->join('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik');
                } elseif ($kategori === 'santri non-pelajar') {
                    $subQuery->join('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
                        ->whereNotExists(function ($q) {
                            $q->select(DB::raw(1))
                                ->from('pelajar')
                                ->whereColumn('pelajar.id_peserta_didik', 'peserta_didik.id');
                        });
                } elseif ($kategori === 'pelajar') {
                    $subQuery->join('pelajar', 'peserta_didik.id', '=', 'pelajar.id_peserta_didik');
                } elseif ($kategori === 'pelajar non-santri') {
                    $subQuery->join('pelajar', 'peserta_didik.id', '=', 'pelajar.id_peserta_didik')
                        ->whereNotExists(function ($q) {
                            $q->select(DB::raw(1))
                                ->from('santri')
                                ->whereColumn('santri.id_peserta_didik', 'peserta_didik.id');
                        });
                } elseif ($kategori === 'santri sekaligus pelajar') {
                    $subQuery->join('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
                        ->join('pelajar', 'peserta_didik.id', '=', 'pelajar.id_peserta_didik');
                }
            });
        }

        // Filter Wafat atau Hidup
        if ($request->filled('wafat')) {
            $wafat = strtolower($request->wafat);
            if ($wafat == 'sudah wafat') {
                $query->where('orang_tua.wafat', true);
            } else if ($wafat == 'masih hidup') {
                $query->where('orang_tua.wafat', false);
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
                    "nik" => $item->nik,
                    "nama" => $item->nama,
                    "no_telepon" => $item->no_telepon,
                    "no_telepon_2" => $item->no_telepon,
                    "nama_kabupaten" => $item->kota_asal,
                    "tanggal_update" => $item->tanggal_update,
                    "tanggal_input" => $item->tanggal_input,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
}
