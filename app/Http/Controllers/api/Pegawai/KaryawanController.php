<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
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
        $query = Karyawan::join('pegawai','pegawai.id','=','karyawan.id_pegawai')
                        ->join('biodata','biodata.id','=','pegawai.id_biodata')
                        ->join('golongan','golongan.id','=','karyawan.id_golongan')
                        ->join('kategori_golongan','kategori_golongan.id','=','golongan.id_kategori_golongan')
                        ->leftJoin('berkas','biodata.id','=','berkas.id_biodata')
                        ->leftJoin('jenis_berkas','jenis_berkas.id','=','berkas.id_jenis_berkas');
        $query = $this->filterController->applyCommonFilters($query, $request);
        if ($request->filled('Jenis_Jabatan')){
            $query->where('golongan.nama_golongan',$request->Jenis_Jabatan);
        }
        if ($request->filled('golongan_jabatan')){
            $query->where('kategori_golongan.nama_kategori_golongan',$request->golongan_jabatan);
        }
        if ($request->filled('warga_pesantren')) {
            $query->where('pegawai.warga_pesantren', $request->warga_pesantren == 'iyaa' ? 1 : 0);
        }
                // untuk ini saya sendiri masih bimbang karena colom tampilannya di front ent kurang jelas
        if ($request->filled('pemberkasan')) {
            if ($request->pemberkasan == 'lengkap') {
                $query->whereNotNull('berkas.id'); // Jika ada berkas
            } elseif ($request->pemberkasan == 'tidak lengkap') {
                $query->whereNull('berkas.id'); // Jika tidak ada berkas
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
        $hasil = $query->select(
            'karyawan.id as id',
            'biodata.nama',
            'biodata.niup',
            'kategori_golongan.nama_kategori_golongan as KategoriJabatan',
            'karyawan.keterangan as Keterangan'
        )->paginate(25);

        return new PdResource(true,'list data berhasil di tampilkan',$hasil);
    }
}
