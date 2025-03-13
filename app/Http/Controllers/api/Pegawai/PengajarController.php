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
            ->leftJoin('lembaga', 'pengajar.id_lembaga', '=', 'lembaga.id')
            ->leftJoin('golongan', 'pengajar.id_golongan', '=', 'golongan.id')
            ->leftJoin('kategori_golongan', 'golongan.id_kategori_golongan', '=', 'kategori_golongan.id')
            ->leftJoin('entitas_pegawai','entitas_pegawai.id_pegawai','=','pegawai.id')
            ->leftJoin('berkas','biodata.id','=','berkas.id_biodata')
            ->leftJoin('jenis_berkas','jenis_berkas.id','=','berkas.id_jenis_berkas');

        // 🔹 Terapkan filter umum (lokasi & jenis kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);
    
        // 🔹 Filter Kategori Golongan
        if ($request->has('kategori_golongan')) {
            $query->where('kategori_golongan.nama_kategori_golongan', $request->kategori_golongan);
        }
        // 🔹 Filter Golongan
        if ($request->has('golongan')) {
            $query->where('golongan.nama_golongan', $request->jabatan);
        }


        if ($request->has('materi_ajar')) {
            $query->where('pengajar.mapel', $request->materi_ajar);
        }

        if ($request->filled('masa_kerja')) {
            $masaKerja = (int) $request->masa_kerja;
            $today = Carbon::now()->format('Y-m-d');
        
            $query->whereRaw("
                TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, ?)) >= ?
            ", [$today, $masaKerja]);
        
            if ($masaKerja == 1) {
                $query->whereRaw("
                    TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, ?)) < ?
                ", [$today, 1]);
            } elseif ($masaKerja == 5) {
                $query->whereRaw("
                    TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, ?)) BETWEEN ? AND ?
                ", [$today, 1, 5]);
            } elseif ($masaKerja == 10) {
                $query->whereRaw("
                    TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, ?)) BETWEEN ? AND ?
                ", [$today, 6, 10]);
            }
        }
        // untuk ini saya sendiri masih bimbang karena colom tampilannya di front ent kurang jelas
        if ($request->filled('pemberkasan')) {
            if ($request->pemberkasan == 'lengkap') {
                $query->whereNotNull('berkas.id'); // Jika ada berkas
            } elseif ($request->pemberkasan == 'tidak lengkap') {
                $query->whereNull('berkas.id'); // Jika tidak ada berkas
            }
        }
        if ($request->filled('warga_pesantren')) {
            $query->where('pegawai.warga_pesantren', $request->warga_pesantren == 'iyaa' ? 1 : 0);
        }
        if ($request->filled('umur')) {
            $umurInput = $request->umur;
        
            // Cek apakah input umur dalam format rentang (misalnya "20-25")
            if (strpos($umurInput, '-') !== false) {
                [$umurMin, $umurMax] = explode('-', $umurInput);
            } else {
                // Jika input angka tunggal, jadikan batas atas dan bawah sama
                $umurMin = $umurInput;
                $umurMax = $umurInput;
            }
        
            // Filter berdasarkan umur yang dihitung dari tanggal_lahir
            $query->whereBetween(
                DB::raw('TIMESTAMPDIFF(YEAR, biodata.tanggal_lahir, CURDATE())'),
                [(int)$umurMin, (int)$umurMax]
            );
        }
        $hasil = $query->select([
            'pengajar.id as id',
            'biodata.nama',
            'biodata.niup',
            DB::raw("GROUP_CONCAT(DISTINCT lembaga.nama_lembaga ORDER BY lembaga.nama_lembaga ASC SEPARATOR ', ') as lembaga")
        ])->groupBy('pengajar.id', 'biodata.nama', 'biodata.niup')->distinct()->paginate(25);
        
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil difilter',
            'data' => $hasil
        ]);
    }
}
