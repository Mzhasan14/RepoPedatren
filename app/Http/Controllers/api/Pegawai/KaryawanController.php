<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\JenisBerkas;
use App\Models\Pegawai\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KaryawanController extends Controller
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
        $Karyawan = Karyawan::all();
        return new PdResource(true,'Data berhasil ditampilkan',$Karyawan);
    }


    public function store(Request $request)
    {
        $validator =Validator::make($request->all(),[
            'id_pegawai' => 'required', 'exists:pegawai,id', 'unique:karyawan,id_pegawai',
            'id_golongan' => 'required', 'exists:golongan,id',
            'keterangan' => 'required', 'string',
            'created_by' => 'required', 'integer',
            'status' => 'required', 'boolean',
        ]);
        if ($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }

        $Karyawan = Karyawan::create($validator->validated());
        return new PdResource(true,'Data berhasil diitambahkan',$Karyawan);
    }

    public function show(string $id)
    {
        $Karyawan = Karyawan::findOrFail($id);
        return new PdResource(true,'Data berhasil ditampilkan',$Karyawan);
    }
    public function update(Request $request, string $id)
    {
        $Karyawan = Karyawan::findOrFail($id);
        $validator =Validator::make($request->all(),[
            'id_pegawai' => 'required', 'exists:pegawai,id', 'unique:karyawan,id_pegawai',
            'id_golongan' => 'required', 'exists:golongan,id',
            'keterangan' => 'required', 'string',
            'updated_by' => 'nullable ', 'integer',
            'status' => 'required', 'boolean',
        ]);
        if ($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $Karyawan->update($validator->validated());
        return new PdResource(true,'Data berhasil ditampilkan',$Karyawan);

    }
    public function destroy(string $id)
    {
        $Karyawan = Karyawan::findOrFail($id);
        $Karyawan->delete();
        return new PdResource(true,'Data berhasil ditampilkan',$Karyawan);
    }
    public function dataKaryawan(Request $request)
    {
        $query = Karyawan::Active()
                        ->join('pegawai','pegawai.id','=','karyawan.id_pegawai')
                        ->join('biodata','biodata.id','=','pegawai.id_biodata')
                        ->leftJoin('kabupaten','kabupaten.id','biodata.id_kabupaten')
                        ->leftJoin('golongan','golongan.id','=','karyawan.id_golongan')
                        ->leftJoin('kategori_golongan','kategori_golongan.id','=','golongan.id_kategori_golongan')
                        ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
                        ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
                        ->leftJoin('pengajar','pengajar.id_pegawai','=','pegawai.id')
                        ->leftJoin('lembaga','lembaga.id','=','pegawai.id_lembaga')
                        ->select(
                            'karyawan.id',
                            'biodata.nama',
                            'biodata.niup',
                            'biodata.nik',
                            DB::raw("TIMESTAMPDIFF(YEAR, biodata.tanggal_lahir, CURDATE()) AS umur"),
                            'karyawan.keterangan_jabatan as KeteranganJabatan',
                            'lembaga.nama_lembaga',
                            'karyawan.jabatan',
                            'golongan.nama_golongan',
                            'biodata.nama_pendidikan_terakhir as pendidikanTerakhir',
                            DB::raw("DATE_FORMAT(karyawan.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
                            DB::raw("DATE_FORMAT(karyawan.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
                            DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
                            )->groupBy(
                                'karyawan.id', 
                                'biodata.nama',
                                'biodata.nik',
                                'biodata.niup',
                                'biodata.tanggal_lahir',
                                'karyawan.keterangan_jabatan',
                                'lembaga.nama_lembaga',
                                'karyawan.jabatan',
                                'golongan.nama_golongan',
                                'biodata.nama_pendidikan_terakhir',
                                'karyawan.updated_at',
                                'karyawan.created_at',
                            );
        $query = $this->filterController->applyCommonFilters($query, $request);
                // Filter Search
        if ($request->filled('search')) {
            $search = strtolower($request->search);
    
            $query->where(function ($q) use ($search) {
                $q->where('biodata.nik', 'LIKE', "%$search%")
                    ->orWhere('biodata.no_passport', 'LIKE', "%$search%")
                    ->orWhere('biodata.nama', 'LIKE', "%$search%")
                    ->orWhere('biodata.niup', 'LIKE', "%$search%")
                    ->orWhere('lembaga.nama_lembaga', 'LIKE', "%$search%")
                    ->orWhere('wilayah.nama_wilayah', 'LIKE', "%$search%")
                    ->orWhere('kabupaten.nama_kabupaten', 'LIKE', "%$search%")
                    ->orWhereDate('karyawan.created_at', '=', $search) // Tgl Input
                    ->orWhereDate('karyawan.updated_at', '=', $search); // Tgl Update
                    });
        }
                // Filter Lembaga
        if ($request->filled('lembaga')) {
            $query->where('lembaga.nama_lembaga', strtolower($request->lembaga));
        }
                // Filter Jenis Jabatan
        if ($request->filled('jabatan')){
            $query->where('karyawan.jabatan',strtolower($request->jabatan));
        }
                // Filter Golongan Jabatan
        if ($request->filled('golongan_jabatan')){
            $query->where('kategori_golongan.nama_kategori_golongan',strtolower($request->golongan_jabatan));
        }
                // Filter Warga Pesantren 
         if ($request->filled('warga_pesantren')) {
            if (strtolower($request->warga_pesantren) === 'memiliki niup') {
                // Hanya tampilkan data yang memiliki NIUP
                $query->whereNotNull('biodata.niup')->where('biodata.niup', '!=', '');
            } elseif (strtolower($request->warga_pesantren) === 'tidak memiliki niup') {
                // Hanya tampilkan data yang tidak memiliki NIUP
                $query->whereNull('biodata.niup')->orWhereRaw("TRIM(biodata.niup) = ''");
            }
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
                    "niup" => $item->niup,
                    "nik" => $item->nik,
                    "umur" => $item->umur,
                    "KeteranganJabatan" => $item->KeteranganJabatan,
                    "lembaga" => $item->nama_lembaga,
                    "jenis" => $item->jabatan,
                    "golongan" => $item->nama_golongan,
                    "pendidikanTerakhir" => $item->pendidikanTerakhir,
                    "tgl_update" => $item->tgl_update,
                    "tgl_input" => $item->tgl_input,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);

    }
}
