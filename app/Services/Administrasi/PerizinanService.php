<?php

namespace App\Services\Administrasi;

use App\Models\Santri;
use App\Models\Perizinan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\BerkasPerizinan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Administrasi\PerizinanRequest;

class PerizinanService
{
    public function basePerizinanQuery($request)
    {
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        return DB::table('perizinan as pr')
            ->join('santri as s', 'pr.santri_id', '=', 's.id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id');
    }

    public function getAllPerizinan(Request $request, $fields = null)
    {
        $query = $this->basePerizinanQuery($request);

        $fields = $fields ?? [
            'pr.id',
            'b.nama as nama_santri',
            'b.jenis_kelamin',
            'pr.alasan_izin',
            'pr.tanggal_mulai',
            'pr.tanggal_akhir',
            // dst...
        ];
        // JOIN default, ex: domisili, pendidikan, users
        $query->leftjoin('domisili_santri as ds', fn($j) => $j->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'))
            ->leftJoin('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
            ->leftJoin('blok AS bl', 'ds.blok_id', '=', 'bl.id')
            ->leftJoin('kamar AS km', 'ds.kamar_id', '=', 'km.id')
            ->leftjoin('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
            ->leftjoin('jurusan as j', 'pd.jurusan_id', '=', 'j.id')
            ->leftjoin('kelas as kls', 'pd.kelas_id', '=', 'kls.id')
            ->leftjoin('rombel as r', 'pd.rombel_id', '=', 'r.id')
            ->leftjoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
            ->leftjoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftjoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
            ->leftjoin('users as biktren', 'pr.biktren_id', '=', 'biktren.id')
            ->leftjoin('users as pengasuh',  'pr.pengasuh_id',  '=', 'pengasuh.id')
            ->leftjoin('users as kamtib',  'pr.kamtib_id',  '=', 'kamtib.id');

        return $query->select($fields);
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
            'approved_by_biktren' => (bool) $item->approved_by_biktren,
            'approved_by_kamtib' => (bool) $item->approved_by_kamtib,
            'approved_by_pengasuh' => (bool) $item->approved_by_pengasuh,
            'keterangan'        => $item->keterangan ?? '-',
            'tgl_input'         => Carbon::parse($item->created_at)
                ->translatedFormat('d F Y H:i:s'),
            'tgl_update'        => Carbon::parse($item->updated_at)
                ->translatedFormat('d F Y H:i:s') ?? '-',
            'foto_profil'       => url($item->foto_profil),
        ]);
    }

    public function index(string $bioId): array
    {
        $perizinan = Perizinan::with('santri.biodata:id')
            ->whereHas('santri.biodata', fn($q) => $q->where('id', $bioId))
            ->latest()
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'alasan_izin' => $item->alasan_izin,
                'alamat_tujuan' => $item->alamat_tujuan,
                'tanggal_mulai' => $item->tanggal_mulai,
                'tanggal_akhir' => $item->tanggal_akhir,
                'tanggal_kembali' => $item->tanggal_kembali,
                'jenis_izin' => $item->jenis_izin,
                'status' => $item->status,
                'keterangan' => $item->keterangan,
                'created_at' => $item->created_at->toDateTimeString(),
            ]);

        return ['status' => true, 'data' => $perizinan];
    }

    public function store(array $data, string $bioId): array
    {
        return DB::transaction(function () use ($data, $bioId) {
            $santri = Santri::where('biodata_id', $bioId)->latest()->first();

            if (!$santri) {
                return ['status' => false, 'message' => 'Santri tidak ditemukan untuk biodata ini'];
            }

            $izin = Perizinan::create([
                'santri_id'        => $santri->id,
                'pengasuh_id'      => $data['pengasuh_id'] ?? null,
                'biktren_id'       => $data['biktren_id'] ?? null,
                'kamtib_id'        => $data['kamtib_id'] ?? null,
                'alasan_izin'      => $data['alasan_izin'],
                'alamat_tujuan'    => $data['alamat_tujuan'],
                'tanggal_mulai'    => $data['tanggal_mulai'],
                'tanggal_akhir'    => $data['tanggal_akhir'],
                'tanggal_kembali'  => $data['tanggal_kembali'] ?? null,
                'jenis_izin'       => $data['jenis_izin'],
                'status'           => $data['status'],
                'keterangan'       => $data['keterangan'] ?? null,
                'created_by'       => Auth::id(),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            return ['status' => true, 'data' => $izin];
        });
    }

    public function show($id): array
    {
        $izin = Perizinan::find($id);

        if (!$izin) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return [
            'status' => true,
            'data' => [
                'id' => $izin->id,
                'biodata_id' => $izin->santri->biodata_id,
                'pengasuh_id' => $izin->pengasuh_id,
                'biktren_id' => $izin->biktren_id,
                'kamtib_id' => $izin->kamtib_id,
                'alasan_izin' => $izin->alasan_izin,
                'alamat_tujuan' => $izin->alamat_tujuan,
                'tanggal_mulai' => $izin->tanggal_mulai,
                'tanggal_akhir' => $izin->tanggal_akhir,
                'tanggal_kembali' => $izin->tanggal_kembali,
                'jenis_izin' => $izin->jenis_izin,
                'status' => $izin->status,
                'keterangan' => $izin->keterangan,
            ],
        ];
    }

    public function update(array $data, string $id): array
    {
        return DB::transaction(function () use ($data, $id) {
            $izin = Perizinan::find($id);

            if (!$izin) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            $izin->update([
                'pengasuh_id'      => $data['pengasuh_id'] ?? null,
                'biktren_id'       => $data['biktren_id'] ?? null,
                'kamtib_id'        => $data['kamtib_id'] ?? null,
                'alasan_izin'      => $data['alasan_izin'],
                'alamat_tujuan'    => $data['alamat_tujuan'],
                'tanggal_mulai'    => $data['tanggal_mulai'],
                'tanggal_akhir'    => $data['tanggal_akhir'],
                'tanggal_kembali'  => $data['tanggal_kembali'] ?? null,
                'jenis_izin'       => $data['jenis_izin'],
                'status'           => $data['status'],
                'keterangan'       => $data['keterangan'] ?? null,
                'updated_by'       => Auth::id(),
                'updated_at'       => now(),
            ]);

            return ['status' => true, 'data' => $izin];
        });
    }

    public function addBerkasPerizinan(array $data, int $id)
    {
        $perizinan = Perizinan::find($id);
        if (!$perizinan) {
            return [
                'status' => false,
                'message' => 'Perizinan tidak ditemukan'
            ];
        }

        $url = Storage::url($data['file_path']->store('berkas_perizinan', 'public'));

        $berkas = BerkasPerizinan::create([
            'perizinan_id' => $id,
            'santri_id' => $perizinan->santri_id,
            'file_path' => $url,
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'status' => true,
            'data' => $berkas
        ];
    }

    public function getExportPerizinanQuery($fields, $request)
    {
        $query = $this->basePerizinanQuery($request);

        // Join dinamis sesuai kebutuhan export
        if (in_array('wilayah', $fields) || in_array('blok', $fields) || in_array('kamar', $fields)) {
            $query->leftJoin('domisili_santri as ds', fn($j) => $j->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'))
                ->leftJoin('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
                ->leftJoin('blok AS bl', 'ds.blok_id', '=', 'bl.id')
                ->leftJoin('kamar AS km', 'ds.kamar_id', '=', 'km.id');
        }
        if (
            in_array('lembaga', $fields) ||
            in_array('jurusan', $fields) ||
            in_array('kelas', $fields) ||
            in_array('rombel', $fields)
        ) {
            $query->leftJoin('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
                ->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
                ->leftJoin('jurusan as j', 'pd.jurusan_id', '=', 'j.id')
                ->leftJoin('kelas as kls', 'pd.kelas_id', '=', 'kls.id')
                ->leftJoin('rombel as r', 'pd.rombel_id', '=', 'r.id');
        }
        if (
            in_array('provinsi', $fields) ||
            in_array('kabupaten', $fields) ||
            in_array('kecamatan', $fields)
        ) {
            $query->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
                ->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
                ->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id');
        }
        if (
            in_array('nama_pengasuh', $fields) ||
            in_array('nama_biktren', $fields) ||
            in_array('nama_kamtib', $fields)
        ) {
            $query->leftJoin('users as biktren', 'pr.biktren_id', '=', 'biktren.id')
                ->leftJoin('users as pengasuh', 'pr.pengasuh_id', '=', 'pengasuh.id')
                ->leftJoin('users as kamtib', 'pr.kamtib_id', '=', 'kamtib.id');
        }
        if (in_array('pembuat', $fields)) {
            $query->leftJoin('users as user_pembuat', 'pr.created_by', '=', 'user_pembuat.id');
        }


        $select = [];
        foreach ($fields as $field) {
            switch ($field) {
                case 'nama_santri':
                    $select[] = 'b.nama as nama_santri';
                    break;
                case 'nis':
                    $select[] = 's.nis';
                    break;
                case 'jenis_kelamin':
                    $select[] = 'b.jenis_kelamin';
                    break;
                case 'wilayah':
                    $select[] = 'w.nama_wilayah';
                    break;
                case 'blok':
                    $select[] = 'bl.nama_blok';
                    break;
                case 'kamar':
                    $select[] = 'km.nama_kamar';
                    break;
                case 'lembaga':
                    $select[] = 'l.nama_lembaga';
                    break;
                case 'jurusan':
                    $select[] = 'j.nama_jurusan';
                    break;
                case 'kelas':
                    $select[] = 'kls.nama_kelas';
                    break;
                case 'rombel':
                    $select[] = 'r.nama_rombel';
                    break;
                case 'provinsi':
                    $select[] = 'pv.nama_provinsi';
                    break;
                case 'kabupaten':
                    $select[] = 'kb.nama_kabupaten';
                    break;
                case 'kecamatan':
                    $select[] = 'kc.nama_kecamatan';
                    break;
                case 'alasan_izin':
                    $select[] = 'pr.alasan_izin';
                    break;
                case 'alamat_tujuan':
                    $select[] = 'pr.alamat_tujuan';
                    break;
                case 'tanggal_mulai':
                    $select[] = 'pr.tanggal_mulai';
                    break;
                case 'tanggal_akhir':
                    $select[] = 'pr.tanggal_akhir';
                    break;
                case 'bermalam':
                    $select[] = DB::raw("CASE WHEN DATE(pr.tanggal_mulai) = DATE(pr.tanggal_akhir) THEN 'Sehari' ELSE 'Bermalam' END as bermalam");
                    break;
                case 'lama_izin':
                    $select[] = DB::raw("
                        CASE
                            WHEN TIMESTAMPDIFF(HOUR, pr.tanggal_mulai, pr.tanggal_akhir) < 24 THEN
                                CONCAT(TIMESTAMPDIFF(HOUR, pr.tanggal_mulai, pr.tanggal_akhir), ' jam')
                            WHEN TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) < 7 THEN
                                CONCAT(TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir), ' hari')
                            WHEN TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) < 30 THEN
                                CONCAT(CEIL(TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir)/7), ' minggu')
                            ELSE
                                CONCAT(CEIL(TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir)/30), ' bulan')
                        END as lama_izin
                    ");
                    break;
                case 'tanggal_kembali':
                    $select[] = 'pr.tanggal_kembali';
                    break;
                case 'jenis_izin':
                    $select[] = 'pr.jenis_izin';
                    break;
                case 'status':
                    $select[] = 'pr.status';
                    break;
                case 'pembuat':
                    $select[] = 'user_pembuat.name as pembuat';
                    break;
                case 'nama_pengasuh':
                    $select[] = 'pengasuh.name as nama_pengasuh';
                    break;
                case 'nama_biktren':
                    $select[] = 'biktren.name as nama_biktren';
                    break;
                case 'nama_kamtib':
                    $select[] = 'kamtib.name as nama_kamtib';
                    break;
                case 'approved_by_biktren':
                    $select[] = 'pr.approved_by_biktren';
                    break;
                case 'approved_by_kamtib':
                    $select[] = 'pr.approved_by_kamtib';
                    break;
                case 'approved_by_pengasuh':
                    $select[] = 'pr.approved_by_pengasuh';
                    break;
                case 'keterangan':
                    $select[] = 'pr.keterangan';
                    break;
                case 'created_at':
                    $select[] = 'pr.created_at';
                    break;
                case 'updated_at':
                    $select[] = 'pr.updated_at';
                    break;
                case 'foto_profil':
                    $select[] = DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil");
                    break;
            }
        }
        return $query->select($select);
    }

    public function formatDataExport($results, $fields, $addNumber = false)
    {
        return collect($results)->values()->map(function ($item, $idx) use ($fields, $addNumber) {
            $data = [];
            if ($addNumber) $data['No'] = $idx + 1;
            $itemArr = (array) $item;
            $i = 0;

            foreach ($fields as $field) {
                switch ($field) {
                    case 'nama_santri':
                        $data['Nama Santri'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'nis':
                        $data['NIS'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'jenis_kelamin':
                        $jk = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Jenis Kelamin'] = $jk === 'l' ? 'Laki-laki' : ($jk === 'p' ? 'Perempuan' : $jk);
                        break;
                    case 'wilayah':
                        $data['Wilayah'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'blok':
                        $data['Blok'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'kamar':
                        $data['Kamar'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'lembaga':
                        $data['Lembaga'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'jurusan':
                        $data['Jurusan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'kelas':
                        $data['Kelas'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'rombel':
                        $data['Rombel'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'provinsi':
                        $data['Provinsi'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'kabupaten':
                        $data['Kabupaten'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'kecamatan':
                        $data['Kecamatan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'alasan_izin':
                        $data['Alasan Izin'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'alamat_tujuan':
                        $data['Alamat Tujuan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'tanggal_mulai':
                        $tgl = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Tanggal Mulai'] = $tgl ? Carbon::parse($tgl)->translatedFormat('d F Y H:i') : '';
                        break;
                    case 'tanggal_akhir':
                        $tgl = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Tanggal Akhir'] = $tgl ? Carbon::parse($tgl)->translatedFormat('d F Y H:i') : '';
                        break;
                    case 'bermalam':
                        $data['Bermalam'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'lama_izin':
                        $data['Lama Izin'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'tanggal_kembali':
                        $tgl = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Tanggal Kembali'] = $tgl ? Carbon::parse($tgl)->translatedFormat('d F Y H:i') : '';
                        break;
                    case 'jenis_izin':
                        $data['Jenis Izin'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'status':
                        $data['Status'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'pembuat':
                        $data['Pembuat'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'nama_pengasuh':
                        $data['Nama Pengasuh'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'nama_biktren':
                        $data['Nama Biktren'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'nama_kamtib':
                        $data['Nama Kamtib'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'approved_by_biktren':
                        $data['Approved By Biktren'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'approved_by_kamtib':
                        $data['Approved By Kamtib'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'approved_by_pengasuh':
                        $data['Approved By Pengasuh'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'keterangan':
                        $data['Keterangan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'created_at':
                        $tgl = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Dibuat'] = $tgl ? Carbon::parse($tgl)->translatedFormat('d F Y H:i') : '';
                        break;
                    case 'updated_at':
                        $tgl = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Update Terakhir'] = $tgl ? Carbon::parse($tgl)->translatedFormat('d F Y H:i') : '';
                        break;
                    case 'foto_profil':
                        $data['Foto Profil'] = url($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                }
            }
            return $data;
        })->values();
    }

    public function getFieldExportHeadings($fields, $addNumber = false)
    {
        $map = [
            'nama_santri'   => 'Nama Santri',
            'nis'           => 'NIS',
            'jenis_kelamin' => 'Jenis Kelamin',
            'wilayah'       => 'Wilayah',
            'blok'          => 'Blok',
            'kamar'         => 'Kamar',
            'lembaga'       => 'Lembaga',
            'jurusan'       => 'Jurusan',
            'kelas'         => 'Kelas',
            'rombel'        => 'Rombel',
            'provinsi'      => 'Provinsi',
            'kabupaten'     => 'Kabupaten',
            'kecamatan'     => 'Kecamatan',
            'alasan_izin'   => 'Alasan Izin',
            'alamat_tujuan' => 'Alamat Tujuan',
            'tanggal_mulai' => 'Tanggal Mulai',
            'tanggal_akhir' => 'Tanggal Akhir',
            'bermalam'      => 'Bermalam',
            'lama_izin'     => 'Lama Izin',
            'tanggal_kembali' => 'Tanggal Kembali',
            'jenis_izin'    => 'Jenis Izin',
            'status'        => 'Status',
            'pembuat'       => 'Pembuat',
            'nama_pengasuh' => 'Nama Pengasuh',
            'nama_biktren'  => 'Nama Biktren',
            'nama_kamtib'   => 'Nama Kamtib',
            'approved_by_biktren' => 'Approved By Biktren',
            'approved_by_kamtib'  => 'Approved By Kamtib',
            'approved_by_pengasuh' => 'Approved By Pengasuh',
            'keterangan'    => 'Keterangan',
            'created_at'    => 'Dibuat',
            'updated_at'    => 'Update Terakhir',
            'foto_profil'   => 'Foto Profil',
        ];
        $headings = [];
        foreach ($fields as $f) $headings[] = $map[$f] ?? $f;
        if ($addNumber) array_unshift($headings, 'No');
        return $headings;
    }
}
