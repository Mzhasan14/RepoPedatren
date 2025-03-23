<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Pegawai\WaliKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $query = WaliKelas::Active()
                            ->join('pengajar','pengajar.id','=','wali_kelas.id_pengajar')
                            ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
                            ->join('biodata','biodata.id','=','pegawai.id_biodata')
                            ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
                            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
                            ->leftJoin('rombel','rombel.id','=','pegawai.id_rombel')
                            ->leftJoin('kelas','kelas.id','=','pegawai.id_kelas')
                            ->leftJoin('jurusan','jurusan.id','=','pegawai.id_jurusan')
                            ->leftJoin('lembaga','lembaga.id','=','pegawai.id_lembaga')
                            ->select(
                                'wali_kelas.id as id',
                                'biodata.nama',
                                'biodata.niup',
                                DB::raw("COALESCE(biodata.nik, biodata.no_passport) as identitas"),
                                'biodata.jenis_kelamin',
                                'lembaga.nama_lembaga',
                                'kelas.nama_kelas',
                                'rombel.gender_rombel',
                                DB::raw("CONCAT(wali_kelas.jumlah_murid, ' pelajar') as jumlah_murid"),
                                'rombel.nama_rombel',
                                DB::raw("DATE_FORMAT(wali_kelas.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
                                DB::raw("DATE_FORMAT(wali_kelas.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
                                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
                            )->groupBy(
                                'wali_kelas.id', 
                                'biodata.nama', 
                                'biodata.niup', 
                                'lembaga.nama_lembaga', 
                                'kelas.nama_kelas', 
                                'rombel.nama_rombel',
                                'biodata.nik',
                                'biodata.no_passport',
                                'rombel.gender_rombel',
                                'biodata.jenis_kelamin',
                                'wali_kelas.jumlah_murid',
                                'wali_kelas.updated_at',
                                'wali_kelas.created_at',
                            );
                                
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
        // Filter Gender Rombel
        if ($request->filled('gender_rombel')) {
            if (strtolower($request->gender_rombel) === 'putra') {
                $query->where('rombel.gender_rombel', 'putra');
            } elseif (strtolower($request->gender_rombel) === 'putri') {
                $query->where('rombel.gender_rombel', 'putri');
            }
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
                    "niup" => $item->niup,
                    "NIK/No.Passport" => $item->identitas,
                    "JenisKelamin" => $item->jenis_kelamin,
                    "lembaga" => $item->nama_lembaga,
                    "kelas" => $item->nama_kelas,
                    "GenderRombel" => $item->gender_rombel,
                    "JumlahMurid" => $item->jumlah_murid,
                    "rombel" => $item->nama_rombel,
                    "tgl_update" => $item->tgl_update,
                    "tgl_input" => $item->tgl_input,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
}
