<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Perizinan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PerizinanController extends Controller
{

    public function index()
    {
        $perizinan = Perizinan::all();
        return new PdResource(true,'Data berhasil ditampilkan',$perizinan);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_peserta_didik' => ['required', 'integer', 'exists:peserta_didik,id'],
            'id_wali_asuh' => ['required', 'integer'],
            'pembuat' => ['required', 'string', 'max:255'],
            'biktren' => ['required', 'string', 'max:255'],
            'kamtib' => ['required', 'integer',],
            'alasan_izin' => ['required', 'string', 'max:1000'],
            'alamat_tujuan' => ['required', 'string', 'max:1000'],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_akhir' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'jenis_izin' => ['required', Rule::in(['Personal', 'Rombongan'])],
            'status_izin' => ['required', Rule::in(['sedang proses izin', 'perizinan diterima', 'sudah berada diluar pondok', 'perizinan ditolak', 'dibatalkan'])],
            'status_kembali' => ['nullable', Rule::in(['telat', 'telat(sudah kembali)', 'telat(belum kembali)', 'kembali tepat waktu'])],
            'keterangan' => ['required', 'string', 'max:1000'],
            'created_by' => ['required', 'integer'],
            'updated_by' => ['nullable', 'integer'],
            'status' => ['required', 'boolean'],
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $perizinan = Perizinan::create($validator->validated());
        return new PdResource(true,'Data berhasil ditambahkan', $perizinan);
    }

    public function show(string $id)
    {
        $perizinan = Perizinan::findOrFail($id);
        return new PdResource(true,' Data berhasil ditambahkan',$perizinan);
    }

    public function update(Request $request, string $id)
    {
        $perizinan = Perizinan::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'id_peserta_didik' => [
                'required',
                'integer',
                Rule::exists('peserta_didik', 'id'),
            ],
            'id_wali_asuh' => [
                'required',
                'integer'
            ],
            'pembuat' => ['required', 'string', 'max:255'],
            'biktren' => ['required', 'string', 'max:255'],
            'kamtib' => ['required', 'integer',],
            'alasan_izin' => ['required', 'string', 'max:1000'],
            'alamat_tujuan' => ['required', 'string', 'max:1000'],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_akhir' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'jenis_izin' => ['required', Rule::in(['Personal', 'Rombongan'])],
            'status_izin' => ['required', Rule::in(['sedang proses izin', 'perizinan diterima', 'sudah berada diluar pondok', 'perizinan ditolak', 'dibatalkan'])],
            'status_kembali' => ['nullable', Rule::in(['telat', 'telat(sudah kembali)', 'telat(belum kembali)', 'kembali tepat waktu'])],
            'keterangan' => ['required', 'string', 'max:1000'],
            'created_by' => ['required', 'integer'],
            'updated_by' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['required', 'boolean'],
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $perizinan->update($validator->validated());
        return new PdResource(true, 'Data berhasil di update',$perizinan);

    }


    public function destroy(string $id)
    {
        $perizinan = Perizinan::findOrFail($id);
        $perizinan->delete();
        return new PdResource(true,'Data berhasil dihapus',$perizinan);
    }
}
