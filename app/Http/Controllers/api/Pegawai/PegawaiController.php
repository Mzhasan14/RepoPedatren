<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\JenisBerkas;
use App\Models\Pegawai\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PegawaiController extends Controller
{
    protected $filterController;

    public function __construct(FilterController $filterController)
    {
        $this->filterController = $filterController;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pegawai = Pegawai::all();
        return new PdResource(true,'Data berhasil ditampilkan', $pegawai);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_biodata' => 'required|integer',
            'created_by' => 'required|integer',
            'status'     => 'required|boolean',
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal buat',
                'data' => $validator->errors()
            ]);
        }

        $pegawai = Pegawai::create($validator->validated());
        return new PdResource(true, 'Data berhasil ditambahkan', $pegawai);
    }

    public function show(string $id)
    {
        $pegawai = Pegawai::findOrFail($id);
        return new PdResource(true,'Data berhasil ditampilkan',$pegawai);
    }


    public function update(Request $request, string $id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'id_biodata' => 'required|integer',
            'updated_by' => 'nullable|integer',
            'status'     => 'required|boolean',
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal buat',
                'data' => $validator->errors()
            ]);
        }
        $pegawai->update($validator->validated());
        return new PdResource(true,'Data berhasil diupdate',$pegawai);
        
    }

    public function destroy(string $id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->delete();
        return new PdResource(true,'Data berhasil dihapus',$pegawai);
    }

    public function dataPegawai(Request $request)
    {
        $query = Pegawai::Active()
                        ->join('biodata','biodata.id','pegawai.id_biodata')
                        ->leftJoin('pengajar','pengajar.id_pegawai','=','pegawai.id')
                        ->leftJoin('pengurus','pengurus.id_pegawai','=','pegawai.id')
                        ->leftJoin('karyawan','karyawan.id_pegawai','=','pegawai.id')
                        ->leftJoin('rombel','pegawai.id_rombel','=','rombel.id')
                        ->leftJoin('kelas','pegawai.id_kelas','=','kelas.id')
                        ->leftJoin('jurusan','pegawai.id_jurusan','=','jurusan.id')
                        ->leftJoin('lembaga','pegawai.id_lembaga','=','lembaga.id')
                        ->leftJoin('entitas_pegawai','entitas_pegawai.id_pegawai','=','pegawai.id')
                        ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
                        ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
                        ->select(
                            'pegawai.id as id',
                            'biodata.nama as nama',
                            DB::raw("TRIM(BOTH ', ' FROM CONCAT_WS(', ', 
                            GROUP_CONCAT(DISTINCT CASE WHEN pengajar.id IS NOT NULL THEN 'Pengajar' END SEPARATOR ', '),
                            GROUP_CONCAT(DISTINCT CASE WHEN karyawan.id IS NOT NULL THEN 'Karyawan' END SEPARATOR ', '),
                            GROUP_CONCAT(DISTINCT CASE WHEN pengurus.id IS NOT NULL THEN 'Pengurus' END SEPARATOR ', ')
                        )) as status"),
                            DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
                            )->groupBy('pegawai.id', 'biodata.nama');


        $query = $this->filterController->applyCommonFilters($query, $request);
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
                // Filter Entitas 
            if ($request->filled('entitas')) {
            $entitas = strtolower($request->entitas); // Ubah ke huruf kecil untuk konsistensi
        
            if ($entitas == 'pengajar') {
                $query->whereNotNull('pengajar.id'); 
            } elseif ($entitas == 'pengurus') {
                $query->whereNotNull('pengurus.id');
            } elseif ($entitas == 'karyawan') {
                $query->whereNotNull('karyawan.id');
            }
        }
                // Filter Warga Pesantren 
            if ($request->filled('warga_pesantren')) {
            $query->where('pegawai.warga_pesantren', strtolower($request->warga_pesantren == 'iya' ? 1 : 0));
        }
            // Filter Pemberkasan (Lengkap / Tidak Lengkap)
        if ($request->filled('pemberkasan')) {
            $jumlahBerkasWajib = JenisBerkas::where('wajib', 1)->count();
            $pemberkasan = strtolower($request->pemberkasan);
            if ($pemberkasan == 'lengkap') {
                $query->havingRaw('COUNT(DISTINCT berkas.id) >= ?', [$jumlahBerkasWajib]);
            } elseif ($pemberkasan == 'tidak lengkap') {
                $query->havingRaw('COUNT(DISTINCT berkas.id) < ?', [$jumlahBerkasWajib]);
            }
        }
                // Filter Umur 
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

        $onePage = $request->input('limit', 25);

        $currentPage =  $request->input('page', 1);

        $hasil = $query->paginate($onePage, ['*'], 'page', $currentPage);


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
                    "status" => $item->status,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
}
