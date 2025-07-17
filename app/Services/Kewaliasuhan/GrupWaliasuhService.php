<?php

namespace App\Services\Kewaliasuhan;

use App\Models\Kewaliasuhan\Grup_WaliAsuh;
use App\Models\Kewaliasuhan\Wali_asuh;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GrupWaliasuhService
{
    public function getAllGrupWaliasuh(Request $request)
    {
        return DB::table('grup_wali_asuh AS gs')
            ->leftjoin('wali_asuh as ws', 'gs.id', '=', 'ws.id_grup_wali_asuh')
            ->leftjoin('kewaliasuhan as ks', 'ks.id_wali_asuh', '=', 'ws.id')
            ->leftjoin('anak_asuh AS aa', 'ks.id_anak_asuh', '=', 'aa.id')
            ->leftjoin('santri AS s', 'ws.id_santri', '=', 's.id')
            ->leftjoin('biodata AS b', 's.biodata_id', '=', 'b.id')
            ->leftJoin('wilayah AS w', 'gs.id_wilayah', '=', 'w.id')
            ->select([
                'gs.id',
                'gs.nama_grup as group',
                's.nis',
                'b.nama',
                'w.nama_wilayah',
                DB::raw("COUNT(CASE WHEN ks.status = true THEN aa.id ELSE NULL END) as jumlah_anak_asuh"),
                'gs.updated_at',
                'gs.created_at',
                'gs.status'
            ])
            ->groupBy(
                'gs.id',
                'gs.nama_grup',
                's.nis',
                'b.nama',
                'w.nama_wilayah',
                'gs.updated_at',
                'gs.created_at',
                'gs.status'
            )
            ->orderBy('gs.id');
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn ($item) => [
            'id' => $item->id,
            'group' => $item->group,
            'nis_wali_asuh' => $item->nis,
            'nama_wali_asuh' => $item->nama,
            'wilayah' => $item->nama_wilayah,
            'jumlah_anak_asuh' => $item->jumlah_anak_asuh,
            'tgl_update' => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            'tgl_input' => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            'status' =>$item->status
        ]);
    }

    public function index(): array
    {
        $data = Grup_WaliAsuh::with(['wilayah'])->orderBy('id', 'asc')->get();

        return [
            'status' => true,
            'data' => $data->map(fn ($item) => [
                'id' => $item->id,
                'nama_grup' => $item->nama_status,
                'wilayah' => $item->wilayah->nama_wilayah,
                'jenis_kelamin' => $item->jenis_kelamin,
                'status' => $item->status,
                'created_by' => $item->created_by,
                'created_at' => $item->created_at,
                'updated_by' => $item->updated_by,
                'updated_at' => $item->updated_at,
                'deleted_by' => $item->deleted_by,
                'deleted_at' => $item->deleted_at,
            ]),
        ];
    }

    public function show(int $id)
    {
        $hubungan = Grup_WaliAsuh::find($id);

        if (! $hubungan) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        return [
            'status' => true,
            'data' => $hubungan,
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            if (! Auth::id()) {
                return [
                    'status' => false,
                    'message' => 'Pengguna tidak terautentikasi',
                    'data' => null,
                ];
            }

            // Buat grup wali asuh baru
            $grup = Grup_WaliAsuh::create([
                'id_wilayah' => $data['id_wilayah'],
                'nama_grup' => $data['nama_grup'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'created_by' => Auth::id(),
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Log activity
            activity('grup_wali_asuh_create')
                ->performedOn($grup)
                ->withProperties([
                    'new_attributes' => $grup->getAttributes(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->event('create_grup_wali_asuh')
                ->log('Grup wali asuh baru berhasil dibuat');

            return [
                'status' => true,
                'message' => 'Grup wali asuh berhasil dibuat',
                'data' => $grup,
            ];
        });
    }

    public function update(array $data, string $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $grup = Grup_WaliAsuh::find($id);

            if (!$grup) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            $jenisKelaminBaru = $data['jenis_kelamin'];

            // Validasi: pastikan semua wali dan anak dalam grup memiliki jenis kelamin sesuai
            $anggotaTidakSesuai = DB::table('wali_asuh')
                ->join('santri', 'wali_asuh.id_santri', '=', 'santri.id')
                ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
                ->where('wali_asuh.id_grup_wali_asuh', $id)
                ->where('biodata.jenis_kelamin', '!=', $jenisKelaminBaru)
                ->exists();

            $anakTidakSesuai = DB::table('anak_asuh')
                ->join('kewaliasuhan', 'anak_asuh.id', '=', 'kewaliasuhan.id_anak_asuh')
                ->join('santri', 'anak_asuh.id_santri', '=', 'santri.id')
                ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
                ->whereIn('kewaliasuhan.id_wali_asuh', function ($query) use ($id) {
                    $query->select('id')
                        ->from('wali_asuh')
                        ->where('id_grup_wali_asuh', $id);
                })
                ->where('biodata.jenis_kelamin', '!=', $jenisKelaminBaru)
                ->exists();

            // Jika ada anggota tidak sesuai jenis kelamin baru, gagalkan
            if ($anggotaTidakSesuai || $anakTidakSesuai) {
                return [
                    'status' => false,
                    'message' => 'Tidak bisa mengubah jenis kelamin grup karena ada anggota yang tidak sesuai.'
                ];
            }

            $updateData = [
                'id_wilayah' => $data['id_wilayah'],
                'nama_grup' => $data['nama_grup'],
                'jenis_kelamin' => $jenisKelaminBaru,
                'updated_by' => Auth::id(),
                'status' => $data['status'] ?? true,
                'updated_at' => now(),
            ];

            $before = $grup->getOriginal();

            $grup->fill($updateData);

            if (!$grup->isDirty()) {
                return ['status' => false, 'message' => 'Tidak ada perubahan'];
            }

            $grup->save();

            $batchUuid = Str::uuid();

            activity('grup_update')
                ->performedOn($grup)
                ->withProperties(['before' => $before, 'after' => $grup->getChanges()])
                ->tap(fn($activity) => $activity->batch_uuid = $batchUuid)
                ->event('update_grup')
                ->log('Data Grup waliasuh diperbarui');

            return ['status' => true, 'data' => $grup];
        });
    }


    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            if (!Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengguna tidak terautentikasi',
                ], 401);
            }

            $grup = Grup_WaliAsuh::withTrashed()->find($id);

            if (!$grup) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data grup wali asuh tidak ditemukan',
                ], 404);
            }

            if ($grup->trashed()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data grup sudah dihapus sebelumnya',
                ], 410);
            }

            // Cek apakah grup masih memiliki anggota aktif
            $hasActiveMembers = Wali_asuh::where('id_grup_wali_asuh', $id)
                ->where('status', true)
                ->exists();

            if ($hasActiveMembers) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak dapat menghapus grup yang masih memiliki anggota aktif',
                ], 400);
            }

            // Ubah status menjadi non aktif, isi kolom deleted_by dan deleted_at
            $grup->status = false;
            $grup->deleted_by = Auth::id();
            $grup->deleted_at = now();
            $grup->save();

            // Log activity
            activity('grup_wali_asuh_nonaktifkan')
                ->performedOn($grup)
                ->withProperties([
                    'deleted_at' => $grup->deleted_at,
                    'deleted_by' => $grup->deleted_by,
                ])
                ->event('nonaktif_grup_wali_asuh')
                ->log('Grup wali asuh dinonaktifkan tanpa dihapus (soft update)');

            return response()->json([
                'status' => true,
                'message' => 'Grup wali asuh berhasil dinonaktifkan',
                'data' => [
                    'deleted_at' => $grup->deleted_at,
                ],
            ]);
        });
    }

    public function activate($id)
    {
        return DB::transaction(function () use ($id) {
            if (!Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengguna tidak terautentikasi',
                ], 401);
            }

            $grup = Grup_WaliAsuh::withTrashed()->find($id);

            if (!$grup) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data grup wali asuh tidak ditemukan',
                ], 404);
            }

            // Jika status sudah aktif
            if ($grup->status) {
                return response()->json([
                    'status' => false,
                    'message' => 'Grup wali asuh sudah dalam keadaan aktif',
                ], 400);
            }

            // Aktifkan kembali
            $grup->status = true;
            $grup->deleted_by = null;
            $grup->deleted_at = null;
            $grup->updated_by = Auth::id();
            $grup->updated_at = now();
            $grup->save();

            // Log activity
            activity('grup_wali_asuh_restore')
                ->performedOn($grup)
                ->event('restore_grup_wali_asuh')
                ->log('Grup wali asuh berhasil diaktifkan kembali');

            return response()->json([
                'status' => true,
                'message' => 'Grup wali asuh berhasil diaktifkan kembali',
            ]);
        });
    }

    public function getExportGrupWaliasuhQuery(Request $request)
    {
        $query = DB::table('grup_wali_asuh AS gs')
            ->leftJoin('wali_asuh as ws', 'gs.id', '=', 'ws.id_grup_wali_asuh')
            ->leftJoin('kewaliasuhan as ks', 'ks.id_wali_asuh', '=', 'ws.id')
            ->leftJoin('anak_asuh AS aa', 'ks.id_anak_asuh', '=', 'aa.id')
            ->leftJoin('santri AS s', 'ws.id_santri', '=', 's.id')
            ->leftJoin('biodata AS b', 's.biodata_id', '=', 'b.id')
            ->leftJoin('wilayah AS w', 'gs.id_wilayah', '=', 'w.id')
            ->select([
                'gs.id',
                'gs.nama_grup',
                's.nis',
                'b.nama as nama_wali_asuh',
                'w.nama_wilayah',
                DB::raw("COUNT(CASE WHEN ks.status = true THEN aa.id ELSE NULL END) as jumlah_anak_asuh"),
                'gs.created_at',
                'gs.updated_at',
                'gs.status',
            ])
            ->groupBy(
                'gs.id',
                'gs.nama_grup',
                's.nis',
                'b.nama',
                'w.nama_wilayah',
                'gs.created_at',
                'gs.updated_at',
                'gs.status'
            )
            ->orderBy('gs.id');

        return $query;
    }

    public function formatDataExportGrupWaliasuh($results, $addNumber = false)
    {
        return collect($results)->map(function ($item, $idx) use ($addNumber) {
            $data = [];
            if ($addNumber) {
                $data['No'] = $idx + 1;
            }

            $data['Nama Grup'] = $item->nama_grup;
            $data['NIS Wali Asuh'] = $item->nis;
            $data['Nama Wali Asuh'] = $item->nama_wali_asuh;
            $data['Wilayah'] = $item->nama_wilayah;
            $data['Jumlah Anak Asuh'] = $item->jumlah_anak_asuh;
            $data['Tanggal Input'] = $item->created_at ? Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s') : '-';
            $data['Tanggal Update'] = $item->updated_at ? Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') : '-';
            $data['Status'] = $item->status ? 'Aktif' : 'Nonaktif';

            return $data;
        })->values();
    }

    public function getGrupWaliasuhHeadings($addNumber = false)
    {
        $headings = [
            'Nama Grup',
            'NIS Wali Asuh',
            'Nama Wali Asuh',
            'Wilayah',
            'Jumlah Anak Asuh',
            'Tanggal Input',
            'Tanggal Update',
            'Status',
        ];

        if ($addNumber) {
            array_unshift($headings, 'No');
        }

        return $headings;
    }

}
