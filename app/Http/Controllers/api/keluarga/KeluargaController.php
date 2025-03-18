<?php

namespace App\Http\Controllers\api\keluarga;

use App\Models\Biodata;
use App\Models\Keluarga;
use Illuminate\Http\Request;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\api\FilterController;

class KeluargaController extends Controller
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
        $keluarga = Keluarga::Active()->latest()->paginate(5);
        return new PdResource(true, 'List Keluarga', $keluarga);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_kk' => 'required|max:16',
            'status_wali' => 'nullable',
            'id_status_keluarga' => 'required',
            'created_by' => 'required',
            'status' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $keluarga = Keluarga::create($validator->validated());
        return new PdResource(true, 'Data berhasil Ditambah', $keluarga);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $keluarga = Keluarga::findOrFail($id);
        return new PdResource(true, 'detail data', $keluarga);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $keluarga = Keluarga::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'no_kk' => 'required|max:16',
            'status_wali' => 'nullable',
            'id_status_keluarga' => 'required',
            'updated_by' => 'nullable',
            'status' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $keluarga->update($validator->validated());
        return new PdResource(true, 'data berhasil diubah', $keluarga);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $keluarga = Keluarga::findOrFail($id);

        $keluarga->delete();
        return new PdResource(true, 'Data berhasil dihapus', null);
    }

    public function dataWali(Request $request)
    {
        $query = Keluarga::active()
                ->join('biodata','keluarga.no_kk','=','biodata.no_kk')
                ->leftjoin('berkas','berkas.id_biodata','=','biodata.id')
                ->leftJoin('jenis_berkas','berkas.id_jenis_berkas','=','jenis_berkas.id')
                ->join('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
            ->select(
                'keluarga.id',
                'biodata.nama',
                'biodata.nik',
                'biodata.no_telepon',
                'kabupaten.nama_kabupaten',
                'keluarga.updated_at as tanggal_update',
                'keluarga.created_at as tanggal_input',
                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
            )
            ->where('status_wali','=',true)
            ->groupBy('keluarga.id', 'biodata.nama', 'biodata.nik', 'biodata.no_telepon', 'kabupaten.nama_kabupaten', 'tanggal_update', 'tanggal_input');

            // Filter Umum (Alamat dan Jenis Kelamin)
            $query = $this->filterController->applyCommonFilters($query, $request);

            // Filter No Telepon
            if ($request->filled('phone_number')) {
                if (strtolower($request->phone_number) === 'mempunyai') {
                    // Hanya tampilkan data yang memiliki nomor telepon
                    $query->whereNotNull('biodata.no_telepon')->where('biodata.no_telepon', '!=', '');
                } elseif (strtolower($request->phone_number) === 'tidak mempunyai') {
                    // Hanya tampilkan data yang tidak memiliki nomor telepon
                    $query->whereNull('biodata.no_telepon')->orWhere('biodata.no_telepon', '');
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
                        "nama" => $item->nama,
                        "nik" => $item->nik,
                        "no_telepon" => $item->no_telepon,
                        "nama_kabupaten" => $item->nama_kabupaten,
                        "tanggal_update" => $item->tanggal_update,
                        "tanggal_input" => $item->tanggal_input,
                        "foto_profil" => url($item->foto_profil)
                    ];
                })
            ]);

    }

    public function keluarga() {
        $keluarga = Keluarga::join('status_keluarga','keluarga.id_status_keluarga','=','status_keluarga.id')
        ->join('biodata','keluarga.no_kk','=','biodata.no_kk')
        ->select(
           'keluarga.id',
           'keluarga.no_kk',
           'biodata.nama',
           'keluarga.status_wali',
           'status_keluarga.nama_status as hubungan',
        )->get();
        return new PdResource(true, 'Data berhasil ditampilkan', $keluarga);
    }
    
    
}
