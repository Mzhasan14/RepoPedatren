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
            'nis' => [
                'nullable',
                'string',
                'size:11',
                Rule::unique('peserta_didik', 'nis')
            ],
            'anak_keberapa' => 'required|numeric|min:1',
            'dari_saudara' => 'required|numeric|min:1|gte:anak_keberapa',
            'tinggal_bersama' => 'required|string|max:50',
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
            'nis' => [
                'nullable',
                'string',
                'size:11',
                Rule::unique('peserta_didik', 'nis')->ignore($id)
            ],
            'anak_keberapa' => 'required|numeric|min:1',
            'dari_saudara' => 'required|numeric|min:1|gte:anak_keberapa',
            'tinggal_bersama' => 'required|string|max:50',
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
            ->join('rencana_pendidikan', 'peserta_didik.id', '=', 'rencana_pendidikan.id_peserta_didik')
            ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
            ->join('lembaga', 'rencana_pendidikan.id_lembaga', '=', 'lembaga.id');

        // 🔹 Filter Umum (Alamat dan Jenis Kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        // 🔹 Filter Wilayah
        if ($request->filled('id_wilayah')) {
            $query->join('domisili', 'peserta_didik.id_domisili', '=', 'domisili.id')
                ->join('kamar', 'domisili.id_kamar', '=', 'kamar.id')
                ->join('blok', 'kamar.id_blok', '=', 'blok.id')
                ->join('wilayah', 'blok.id_wilayah', '=', 'wilayah.id')
                ->where('wilayah.id', $request->id_wilayah);
            if ($request->filled('id_blok')) {
                $query->where('blok.id', $request->id_blok);
                if ($request->filled('id_kamar')) {
                    $query->where('kamar.id', $request->id_kamar);
                }
            }
        }

        // 🔹 Filter Lembaga
        if ($request->filled('id_lembaga')) {
            $query->join('jurusan', 'rencana_pendidikan.id_jurusan', '=', 'jurusan.id')
                ->join('kelas', 'rencana_pendidikan.id_kelas', '=', 'kelas.id')
                ->join('rombel', 'rencana_pendidikan.id_rombel', '=', 'rombel.id');
            $query->where('rencana_pendidikan.id_lembaga', $request->id_lembaga);
            if ($request->filled('id_jurusan')) {
                $query->where('rencana_pendidikan.id_jurusan', $request->id_jurusan);
                if ($request->filled('id_kelas')) {
                    $query->where('rencana_pendidikan.id_kelas', $request->id_kelas);
                    if ($request->filled('id_rombel')) {
                        $query->where('rencana_pendidikan.id_rombel', $request->id_rombel);
                    }
                }
            }
        }

        
        $hasil = $query->select('biodata.nama','biodata.niup','lembaga.nama_lembaga')->paginate(10);

        return new PdResource(true, 'list Peserta Didik', $hasil);
    }
}
