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


    public function ortu(Request $request) {

        $query = OrangTua::active()
            ->join('biodata','orang_tua.id_biodata','=','biodata.id')
            ->leftjoin('berkas','berkas.id_biodata','=','biodata.id')
            ->leftJoin('jenis_berkas','berkas.id_jenis_berkas','=','jenis_berkas.id')
            ->join('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
            ->select(
                'orang_tua.id',
                'biodata.nama',
                'biodata.nik',
                'biodata.no_telepon',
                'kabupaten.nama_kabupaten',
                'orang_tua.updated_at as tanggal_update',
                'orang_tua.created_at as tanggal_input',
                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
             )
             ->groupBy('orang_tua.id','biodata.nama','biodata.nik','biodata.no_telepon','kabupaten.nama_kabupaten','tanggal_update','tanggal_input');
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
}
