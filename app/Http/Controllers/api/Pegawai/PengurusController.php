<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
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
                            ->join('golongan','pengurus.id_golongan','=','golongan.id')
                            ->join('kategori_golongan','golongan.id_kategori_golongan','=','kategori_golongan.id')
                            ->join('pegawai','pengurus.id_pegawai','pegawai.id')
                            ->join('biodata','pegawai.id_biodata','=','biodata.id')
                            ->leftJoin('berkas','biodata.id','=','berkas.id_biodata')
                            ->leftJoin('jenis_berkas','jenis_berkas.id','=','berkas.id_jenis_berkas');
       $query = $this->filterController->applyCommonFilters($query, $request);
       if ($request->filled('satuan_kerja')) {
        $query->where('pengurus.satuan_kerja', $request->satuan_kerja);
    }    
        if ($request->filled('jabatan')) {
            $query->where('pengurus.jabatan', $request->jabatan);
        }
        if ($request->filled('golongan')) {
            $query->where('golongan.nama_golongan', $request->golongan);
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
        if ($request->filled('no_telepon')){
            $query->where('biodata.no_telepon',$request->no_telepon);
        }

        $hasil = $query                           
        ->select(
            'pengurus.id as id',
            'biodata.nama as Nama',
            'biodata.nik as NIK',
            'golongan.nama_golongan as Jabatan',
            'kategori_golongan.nama_kategori_golongan as Golongan Jabatan'
        )->paginate(25);
        return new PdResource(true,'list data berhasil di tampilkan',$hasil);
                            
    }
}
