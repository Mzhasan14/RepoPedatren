<?php

namespace App\Services\Administrasi;

use App\Models\Perizinan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Administrasi\PerizinanRequest;

class PerizinanService
{
    public function getAllPerizinan(Request $request)
    {
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        $perizinanLast = DB::table('perizinan')
            ->select('santri_id', DB::raw('MAX(id) AS last_pr_id'))
            ->groupBy('santri_id');

        return DB::table('perizinan as pr')
            ->joinSub($perizinanLast, 'pl', function ($join) {
                $join->on('pr.santri_id', '=', 'pl.santri_id')
                    ->on('pr.id', '=', 'pl.last_pr_id');
            })
            ->join('santri as s', 'pr.santri_id', '=', 's.id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftjoin('riwayat_domisili as rd', fn($j) => $j->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
            ->leftJoin('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
            ->leftJoin('blok AS bl', 'rd.blok_id', '=', 'bl.id')
            ->leftJoin('kamar AS km', 'rd.kamar_id', '=', 'km.id')
            ->leftjoin('riwayat_pendidikan AS rp', fn($j) => $j->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
            ->leftjoin('jurusan as j', 'rp.jurusan_id', '=', 'j.id')
            ->leftjoin('kelas as kls', 'rp.kelas_id', '=', 'kls.id')
            ->leftjoin('rombel as r', 'rp.rombel_id', '=', 'r.id')
            ->leftjoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
            ->leftjoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftjoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
            ->leftjoin('users as biktren', 'pr.biktren_id', '=', 'biktren.id')
            ->leftjoin('users as pengasuh',  'pr.pengasuh_id',  '=', 'pengasuh.id')
            ->leftjoin('users as kamtib',  'pr.kamtib_id',  '=', 'kamtib.id')
            ->join('users as creator', 'pr.created_by', '=', 'creator.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->select([
                'pr.id',
                'b.nama as nama_santri',
                'b.jenis_kelamin',
                'w.nama_wilayah',
                'bl.nama_blok',
                'km.nama_kamar',
                'l.nama_lembaga',
                'j.nama_jurusan',
                'kls.nama_kelas',
                'r.nama_rombel',
                'pv.nama_provinsi',
                'kb.nama_kabupaten',
                'kc.nama_kecamatan',
                'pr.alasan_izin',
                'pr.alamat_tujuan',
                'pr.tanggal_mulai',
                'pr.tanggal_akhir',
                // kolom bermalam: kalau tanggal mulai dan tanggal akhir berbeda → bermalam,
                // kalau sama tanggalnya → sehari
                DB::raw("
                  CASE
                  WHEN DATE(pr.tanggal_mulai) = DATE(pr.tanggal_akhir) THEN 'sehari'
                  ELSE 'bermalam'
                  END AS bermalam
              "),
                DB::raw("
                  CASE
                      WHEN TIMESTAMPDIFF(HOUR, pr.tanggal_mulai, pr.tanggal_akhir) < 24 THEN
                      CONCAT(TIMESTAMPDIFF(HOUR, pr.tanggal_mulai, pr.tanggal_akhir), ' jam')
                      WHEN TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) < 7 THEN
                      CONCAT(TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir), ' hari')
                      WHEN TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) < 30 THEN
                      CONCAT(CEIL(TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) / 7), ' minggu')
                      ELSE
                      CONCAT(CEIL(TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) / 30), ' bulan')
                  END
                  AS lama_izin
                  "),
                'pr.tanggal_kembali',
                'pr.jenis_izin',
                'pr.status',
                'creator.name as pembuat',
                'pengasuh.name as nama_pengasuh',
                'biktren.name as nama_biktren',
                'kamtib.name as nama_kamtib',
                'pr.keterangan',
                'pr.created_at',
                'pr.updated_at',
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ])
            ->orderBy('pr.id', 'desc');
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'id'                => $item->id,
            'nama_santri'       => $item->nama_santri,
            'jenis_kelamin'     => $item->jenis_kelamin,
            'wilayah'           => $item->nama_wilayah ?? '-',
            'blok'         => $item->nama_blok ?? '-',
            'kamar'        => $item->nama_kamar ?? '-',
            'lembaga'      => $item->nama_lembaga ?? '-',
            'jurusan'      => $item->nama_jurusan ?? '-',
            'kelas'        => $item->nama_kelas ?? '-',
            'rombel'       => $item->nama_rombel ?? '-',
            'provinsi'     => $item->nama_provinsi ?? '-',
            'kabupaten'    => $item->nama_kabupaten ?? '-',
            'kecamatan'    => $item->nama_kecamatan ?? '-',
            'alasan_izin'       => $item->alasan_izin,
            'alamat_tujuan'     => $item->alamat_tujuan,
            'tanggal_mulai'     => Carbon::parse($item->tanggal_mulai)
                ->translatedFormat('d F Y H:i:s'),
            'tanggal_akhir'     => Carbon::parse($item->tanggal_akhir)
                ->translatedFormat('d F Y H:i:s'),
            'bermalam'          => $item->bermalam,
            'lama_izin'         => $item->lama_izin,
            'tanggal_kembali'   => Carbon::parse($item->tanggal_kembali)
                ->translatedFormat('d F Y H:i:s') ?? '-',
            'jenis_izin'        => $item->jenis_izin,
            'status'       => $item->status,
            'pembuat'           => $item->pembuat,
            'nama_pengasuh'    => $item->nama_pengasuh ?? '-',
            'nama_biktren'      => $item->nama_biktren ?? '-',
            'nama_kamtib'       => $item->nama_kamtib ?? '-',
            'keterangan'        => $item->keterangan ?? '-',
            'tgl_input'         => Carbon::parse($item->created_at)
                ->translatedFormat('d F Y H:i:s'),
            'tgl_update'        => Carbon::parse($item->updated_at)
                ->translatedFormat('d F Y H:i:s') ?? '-',
            'foto_profil'       => url($item->foto_profil),
        ]);
    }

    public function store(PerizinanRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $data['created_by'] = Auth::id();

            Perizinan::create($data);

            DB::commit();
            return response()->json([
                'message' => 'Data perizinan berhasil disimpan',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Data perizinan gagal disimpan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
