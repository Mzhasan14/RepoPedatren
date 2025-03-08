<?php

namespace App\Http\Controllers\Api\Pegawai;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Biodata;
use Illuminate\Http\Request;
use App\Models\Pegawai\Pengajar;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\FilterController;

class PengajarController extends Controller
{

    protected $filterController;

    public function __construct(FilterController $filterController)
    {
        $this->filterController = $filterController;
    }

    public function index()
    {
        $pengajar = Pengajar::all();
        return new PdResource(true, 'Data berhasil ditampilkan', $pengajar);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pegawai'   => 'required|integer',
            'id_golongan'  => 'required|integer',
            'id_lembaga'   => 'required|integer',
            'mapel'        => 'required|string|max:255',
            'created_by'   => 'required|integer',
            'status'       => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }

        $pengajar = Pengajar::create($validator->validated());
        return new PdResource(true, 'Data berhasil ditambahkan', $pengajar);
    }

    public function show(string $id)
    {
        $pengajar = Pengajar::findOrFail($id);
        return new PdResource(true, 'Data berhasil ditampilkan', $pengajar);
    }
    public function update(Request $request, string $id)
    {
        $pengajar = Pengajar::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'id_pegawai'   => 'required|integer',
            'id_golongan'  => 'required|integer',
            'id_lembaga'   => 'required|integer',
            'mapel'        => 'required|string|max:255',
            'updated_by'   => 'nullable|integer',
            'status'       => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $pengajar->update($validator->validated());
        return new PdResource(true, 'Data berhasil diupdate', $pengajar);
    }
    public function destroy(string $id)
    {
        $pengajar = Pengajar::findOrFail($id);
        $pengajar->delete();
        return new PdResource(true, 'Data berhasil dihapus', $pengajar);
    }

    public function Pengajar()
    {
        $pengajar = Biodata::join('pegawai', 'biodata.id', '=', 'pegawai.id_biodata')
            ->join('pengajar', 'pegawai.id', '=', 'pengajar.id_pegawai')
            ->select(
                'pengajar.id as id_pengajar',
                'biodata.nama',
                'biodata.niup',
                'biodata.nama_pendidikan_terakhir',
                'biodata.image_url'
            )
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditampilkan',
            'data' => $pengajar
        ]);
    }

    public function filterPengajar(Request $request)
    {
        $query = Pengajar::join('pegawai', 'pengajar.id_pegawai', '=', 'pegawai.id')
            ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
            ->join('lembaga', 'pengajar.id_lembaga', '=', 'lembaga.id')
            ->join('golongan', 'pengajar.id_golongan', '=', 'golongan.id')
            ->join('kategori_golongan', 'golongan.id_kategori_golongan', '=', 'kategori_golongan.id')
            ->select([
                'biodata.nama',
                'biodata.niup',
                DB::raw("GROUP_CONCAT(DISTINCT lembaga.nama_lembaga ORDER BY lembaga.nama_lembaga ASC SEPARATOR ', ') as lembaga")
            ])->groupBy('biodata.nama', 'biodata.niup');

        // 🔹 Terapkan filter umum (lokasi & jenis kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        // 🔹 Filter Lembaga
        if ($request->has('id_lembaga')) {
            $query->where('pengajar.id_lembaga', $request->id_lembaga);
        }

        // 🔹 Filter Kategori Golongan
        if ($request->has('id_kategori_golongan')) {
            $query->where('kategori_golongan.id', $request->id_kategori_golongan);
        }

        // 🔹 Filter Golongan
        if ($request->has('id_golongan')) {
            $query->where('golongan.id', $request->id_golongan);
        }

        // 🔹 Filter Jabatan
        // if ($request->has('jabatan')) {
        //     $query->where('entitas.nama_entitas', $request->jabatan);
        // }

        // 🔹 Filter Masa Kerja (dari tanggal masuk & keluar)
        // if ($request->has('masa_kerja_min') || $request->has('masa_kerja_max')) {
        //     if ($request->has('masa_kerja_min')) {
        //         $query->whereRaw("TIMESTAMPDIFF(YEAR, pengajar.tanggal_masuk, NOW()) >= ?", [$request->masa_kerja_min]);
        //     }
        //     if ($request->has('masa_kerja_max')) {
        //         $query->whereRaw("TIMESTAMPDIFF(YEAR, pengajar.tanggal_masuk, NOW()) <= ?", [$request->masa_kerja_max]);
        //     }
        // }

        // 🔹 Filter Umur (dari biodata.tanggal_lahir)
        // if ($request->has('umur')) {
        //     $umur = intval($request->umur);
        //     $tanggal_batas = Carbon::now()->subYears($umur)->format('Y-m-d');
        //     $query->where('biodata.tanggal_lahir', '<=', $tanggal_batas);
        // }

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil difilter',
            'data' => $query->distinct()->get()
        ]);
    }
}
