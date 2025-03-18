<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\JenisBerkas;
use App\Models\Pegawai\Pengurus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PengurusController extends Controller
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
        $pengurus = Pengurus::all();
        return new PdResource(true,'Data berhasil ditampilkan',$pengurus);
    }
    public function store(Request $request)
    {
        $validator =Validator::make($request->all(),[
            'id_pegawai' => ['required', 'exists:pegawai,id'],
            'id_golongan' => ['required', 'exists:golongan,id'],
            'satuan_kerja' => ['required', 'string', 'max:255'],
            'jabatan' => ['required', 'string', 'max:255'],
            'created_by' => ['required', 'integer'],
            'status' => ['required', 'boolean'],
        ]);
        if ($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }

        $pengurus = Pengurus::create($validator->validated());
        return new PdResource(true,'Data berhasil diitambahkan',$pengurus);
    }


    public function show(string $id)
    {
        $pengurus = Pengurus::findOrFail($id);
        return new PdResource(true,'Data berhasil ditampilkan',$pengurus);
    }

    public function update(Request $request, string $id)
    {
        $pengurus = Pengurus::findOrFail($id);
        $validator =Validator::make($request->all(),[
            'id_pegawai' =>'required', 'exists:pegawai,id',
            'id_golongan' => 'required', 'exists:golongan,id',
            'satuan_kerja' => 'required', 'string', 'max:255',
            'jabatan' => 'required', 'string', 'max:255',
            'updated_by' => 'nullable', 'integer',
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
        $pengurus->update($validator->validated());
        return new PdResource(true,'Data berhasil ditampilkan',$pengurus);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pengurus = Pengurus::findOrFail($id);
        $pengurus->delete();
        return new PdResource(true,'Data berhasil ditampilkan',$pengurus);

    }
    public function dataPengurus(Request $request)
    {
        $query = Pengurus::Active()
                            ->leftJoin('golongan','pengurus.id_golongan','=','golongan.id')
                            ->leftJoin('kategori_golongan','golongan.id_kategori_golongan','=','kategori_golongan.id')
                            ->join('pegawai','pengurus.id_pegawai','pegawai.id')
                            ->join('biodata','pegawai.id_biodata','=','biodata.id')
                            ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
                            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
                            ->select(
                                'pengurus.id',
                                'biodata.nama',
                                'biodata.nik',
                                'pengurus.jabatan',
                                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
                                )->groupBy('pengurus.id', 'biodata.nama', 'biodata.nik', 'pengurus.jabatan');
                                
       $query = $this->filterController->applyCommonFilters($query, $request);
               // Filter Satuan Kerja
        if ($request->filled('satuan_kerja')) {
            $query->where('pengurus.satuan_kerja', strtolower($request->satuan_kerja));
        }    
                // Filter Jabatan
        if ($request->filled('jabatan')) {
            $query->where('pengurus.jabatan', strtolower($request->jabatan));
        }
                // Filter Golongan Jabatn
        if ($request->filled('golongan')) {
            $query->where('golongan.nama_golongan', strtolower($request->golongan));
        }
        if ($request->filled('warga_pesantren')) {
            $query->where('pegawai.warga_pesantren', strtolower($request->warga_pesantren) == 'iya' ? 1 : 0);
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
                    "nik" => $item->nik,
                    "jabatan" => $item->jabatan,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
}
