<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
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
        $query = Pegawai::join('biodata','biodata.id','pegawai.id_biodata')
                        ->leftJoin('pengajar','pengajar.id_pegawai','=','pegawai.id')
                        ->leftJoin('wali_kelas','wali_kelas.id_pengajar','=','pengajar.id')
                        ->leftJoin('rombel','wali_kelas.id_rombel','=','rombel.id')
                        ->leftJoin('kelas','rombel.id_kelas','=','kelas.id')
                        ->leftJoin('jurusan','kelas.id_jurusan','=','jurusan.id')
                        ->leftJoin('lembaga','jurusan.id_lembaga','=','lembaga.id')
                        ->leftJoin('karyawan','karyawan.id_pegawai','=','pegawai.id')
                        ->leftJoin('pengurus','pengurus.id_pegawai','=','pegawai.id')
                        ->leftJoin('entitas_pegawai','entitas_pegawai.id_pegawai','=','pegawai.id');

        // 🔹 Terapkan filter umum (lokasi & jenis kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);
        if ($request->filled('lembaga')) {
             $query->where('lembaga.nama_lembaga', $request->lembaga);
             if ($request->filled('jurusan')) {
                 $query->where('jurusan.nama_jurusan', $request->jurusan);
                 if ($request->filled('kelas')) {
                     $query->where('kelas.nama_kelas', $request->kelas);
                     if ($request->filled('rombel')) {
                         $query->where('rombel.nama_rombel', $request->rombel);
                    }
                }
            }
        }
        if ($request->filled('entitas')){
            $query->where('entitas_pegawai.id',$request->entitas);
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
        $hasil = $query->select(
            'pegawai.id as id',
            'biodata.nama as nama',
            DB::raw("
                TRIM(BOTH ', ' FROM GROUP_CONCAT(
                    CASE 
                        WHEN pengajar.id IS NOT NULL THEN 'Pengajar'
                        WHEN karyawan.id IS NOT NULL THEN 'Karyawan'
                        WHEN pengurus.id IS NOT NULL THEN 'Pengurus'
                    END
                    SEPARATOR ', '
                )) as status
            ")
        )
        ->groupBy('pegawai.id', 'biodata.nama')
        ->paginate(25);
        return new PdResource(true,'list data berhasil di tampilkan',$hasil);
    }
}
