<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Pegawai\WaliKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalikelasController extends Controller
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
        $walikelas = WaliKelas::all();
        return new PdResource(true,'Data berhasil ditampilkan',$walikelas);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_pengajar'  => 'required|integer',
            'id_rombel'    => 'required|integer',
            'jumlah_murid' => 'required|string|min:1',
            'created_by'   => 'required|integer',
            'status'       => 'required|boolean',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data Gagal ditambahkan',
                'data'=> $validator->errors()
            ]);
        }
        $walikelas = WaliKelas::create($validator->validated());
        return new PdResource(true,'Data berhasil ditambahkan',$walikelas);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $walikelas = WaliKelas::findOrFail($id);
        return new PdResource(true,'Data berhasil ditampilkan',$walikelas);
    }
    public function update(Request $request, string $id)
    {
        $walikelas = WaliKelas::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'id_pengajar'  => 'required|integer',
            'id_rombel'    => 'required|integer',
            'jumlah_murid' => 'required|string|min:1',
            'updated_by'   => 'nullable|integer',
            'status'       => 'required|boolean',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data Gagal ditambahkan',
                'data'=> $validator->errors()
            ]);
        }
        $walikelas->update($validator->validated());
        return new PdResource(true,'Data berhasil diupdate',$walikelas);
    }


    public function destroy(string $id)
    {
        $walikelas = WaliKelas::findOrFail($id);
        $walikelas->delete();
        return new PdResource(true,'Data berhasil dihapus',$walikelas);
    }
    public function dataWalikelas(Request $request)
    {
        $query = WaliKelas::join('pengajar','pengajar.id','=','wali_kelas.id_pengajar')
                            ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
                            ->join('biodata','biodata.id','=','pegawai.id_biodata')
                            ->join('rombel','rombel.id','=','wali_kelas.id_rombel')
                            ->join('kelas','kelas.id','=','rombel.id_kelas')
                            ->join('jurusan','jurusan.id','=','kelas.id_jurusan')
                            ->join('lembaga','lembaga.id','=','jurusan.id_lembaga');
        $query = $this->filterController->applyCommonFilters($query, $request);

        if ($request->filled('gender_rombel')){
            $query->where('biodata.jenis_kelamin',$request->gender_rombel);
        }
        if ($request->filled('no_telepon')) {
            $query->where('biodata.no_telepon', 'LIKE', "%{$request->no_telepon}%");
        }
        $hasil = $query->select(
            'wali_kelas.id as id',
            'biodata.nama as Nama',
            'biodata.niup',
            'lembaga.nama_lembaga as Lembaga',
            'kelas.nama_kelas as Kelas',
            'rombel.nama_rombel as Rombel'
            )->paginate(25);

        return new PdResource(true,'list data berhasil di tampilkan',$hasil);

    }
}
