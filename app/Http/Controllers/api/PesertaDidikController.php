<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Peserta_didik;
use Illuminate\Validation\Rule;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PesertaDidikController extends Controller
{
    protected $filterController;

    public function __construct(FilterController $filterController)
    {
        $this->filterController = $filterController;
    }

    public function index()
    {
        $pesertaDidik = Peserta_didik::Active()->latest()->paginate(10);
        return new PdResource(true, 'List Peserta Didik', $pesertaDidik);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_domisili' => 'required|integer',
            'id_biodata' => [
                'required',
                'integer',
                Rule::unique('peserta_didik', 'id_biodata')
            ],
            'id_lembaga' => [
                'required',
                'integer',
                Rule::unique('peserta_didik', 'id_lembaga')
            ],
            'id_jurusan' => [
                'required',
                'integer',
                Rule::unique('peserta_didik', 'id_jurusan')
            ],
            'id_kelas' => [
                'required',
                'integer',
                Rule::unique('peserta_didik', 'id_kelas')
            ],
            'id_rombel' => [
                'required',
                'integer',
                Rule::unique('peserta_didik', 'id_rombel')
            ],
            'nis' => [
                'nullable',
                'string',
                'size:11',
                Rule::unique('peserta_didik', 'nis')
            ],
            'tahun_masuk' => 'required|date',
            'tahun_keluar' => 'nullable|date',
            'created_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pesertaDidik = Peserta_didik::create($validator->validated());

        return new PdResource(true, 'Data berhasil ditambah', $pesertaDidik);
    }

    public function show($id)
    {
        $pesertaDidik = Peserta_didik::findOrFail($id);
        return new PdResource(true, 'Detail Peserta Didik', $pesertaDidik);
    }

    public function update(Request $request, $id)
    {

        $pesertaDidik = Peserta_didik::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_domisili' => 'required|integer',
            'id_biodata' => [
                'required',
                'integer',
                Rule::unique('peserta_didik', 'id_biodata')->ignore($id)
            ],
            'id_lembaga' => [
                'required',
                'integer',
                Rule::unique('peserta_didik', 'id_lembaga')->ignore($id)
            ],
            'id_jurusan' => [
                'required',
                'integer',
                Rule::unique('peserta_didik', 'id_jurusan')->ignore($id)
            ],
            'id_kelas' => [
                'required',
                'integer',
                Rule::unique('peserta_didik', 'id_kelas')->ignore($id)
            ],
            'id_rombel' => [
                'required',
                'integer',
                Rule::unique('peserta_didik', 'id_rombel')->ignore($id)
            ],
            'nis' => [
                'nullable',
                'string',
                'size:11',
                Rule::unique('peserta_didik', 'nis')->ignore($id)
            ],
            'no_induk' => 'nullable|string',
            'tahun_masuk' => 'required|date',
            'tahun_keluar' => 'nullable|date',
            'updated_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pesertaDidik->update($validator->validated());

        return new PdResource(true, 'Data berhasil diubah', $pesertaDidik);
    }

    public function destroy($id)
    {
        $pesertaDidik = Peserta_didik::findOrFail($id);

        $pesertaDidik->delete();
        return new PdResource(true, 'Data berhasil dihapus', null);
    }

    public function pesertaDidiksantri()
    {
        $santri = Peserta_didik::Santri()->Active()->latest()->paginate(5);

        return new PdResource(true, 'List Peserta Didik Santri', $santri);
    }

    public function pesertaDidik(Request $request)
    {
        $query = Peserta_didik::Active()
            ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
            ->join('lembaga', 'peserta_didik.id_lembaga', '=', 'lembaga.id');

        // 🔹 Filter Umum (Alamat dan Jenis Kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        // 🔹 Filter Wilayah
        if ($request->filled('wilayah')) {
            $query->join('domisili', 'peserta_didik.id_domisili', '=', 'domisili.id')
                ->join('kamar', 'domisili.id_kamar', '=', 'kamar.id')
                ->join('blok', 'kamar.id_blok', '=', 'blok.id')
                ->join('wilayah', 'blok.id_wilayah', '=', 'wilayah.id')
                ->where('wilayah.nama_wilayah', $request->wilayah);
            if ($request->filled('blok')) {
                $query->where('blok.nama_blok', $request->blok);
                if ($request->filled('kamar')) {
                    $query->where('kamar.nama_kamar', $request->kamar);
                }
            }
        }

        // 🔹 Filter Lembaga
        if ($request->filled('lembaga')) {
            $query->join('jurusan', 'peserta_didik.id_jurusan', '=', 'jurusan.id')
                ->join('kelas', 'peserta_didik.id_kelas', '=', 'kelas.id')
                ->join('rombel', 'peserta_didik.id_rombel', '=', 'rombel.id');
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

        if ($request->filled('warga_pesantren')) {
            if ($request->warga_pesantren === 'iya'){
                $query->whereNotNull('peserta_didik.nis')
                ->where('nis', '!=', '');
            }else if ($request->warga_pesantren === 'tidak') {
                $query->whereNull('peserta_didik.nis')
                ->where('nis', '=', '');
            }
            
        }

        
        $hasil = $query->select('biodata.nama','biodata.niup','lembaga.nama_lembaga')->paginate(10);

        return new PdResource(true, 'list Peserta Didik', $hasil);
    }
    public function alumni()
    {
        $pesertaDidik = Peserta_didik::join('biodata','peserta_didik.id_biodata','=','biodata.id')
            ->join('desa','biodata.id_desa','=','desa.id')
            ->join('kecamatan','desa.id_kecamatan','=','kecamatan.id')
            ->join('kabupaten','kecamatan.id_kabupaten','=','kabupaten.id')
            ->join('provinsi','kabupaten.id_provinsi','=','provinsi.id')
            ->join('negara','provinsi.id_negara','=','negara.id')
            ->whereNotNull('peserta_didik.tahun_keluar')
            ->select(
                'biodata.id as id',
                'biodata.nama as Nama',
                'kabupaten.nama_kabupaten as Alamat',
                'biodata.nama_pendidikan_terakhir as Pendidikan Terakhir',
                'peserta_didik.tahun_keluar as Tahun ')
            ->get();

        return new PdResource(true, 'List data alumni', $pesertaDidik);
        

    }

}
