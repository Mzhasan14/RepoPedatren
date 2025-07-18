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

    public function getExportGrupWaliasuhQuery(array $fields, Request $request)
    {
        $query = $this->getAllGrupWaliasuh($request);

        // Dynamic joins
        if (in_array('no_kk', $fields)) {
            $query->leftJoin('keluarga as k', 'k.id_biodata', '=', 'b.id');
        }

        if (in_array('niup', $fields)) {
            $query->leftJoin('warga_pesantren as wp', 'wp.biodata_id', '=', 'b.id');
        }

        if (in_array('angkatan_santri', $fields)) {
            $query->leftJoin('angkatan as as', 's.angkatan_id', '=', 'as.id');
        }

        // Select fields
    $select = [];

    foreach ($fields as $field) {
        switch ($field) {
            case 'id':
                $select[] = 'gs.id';
                break;
            case 'nama_grup':
                $select[] = 'gs.nama_grup';
                break;
            case 'nama_wilayah':
                $select[] = 'w.nama_wilayah';
                break;
            case 'nis':
                $select[] = 's.nis';
                break;
            case 'nama_wali_asuh':
                $select[] = 'b.nama as nama_wali_asuh';
                break;
            case 'no_kk':
                $select[] = 'k.no_kk as no_kk';
                break;
            case 'nik':
                $select[] = DB::raw('COALESCE(b.nik, b.no_passport) as nik');
                break;
            case 'niup':
                $select[] = 'wp.niup as niup';
                break;
            case 'jenis_kelamin_wali_asuh':
                $select[] = 'b.jenis_kelamin as jenis_kelamin_wali_asuh';
                break;
            case 'angkatan_santri':
                $select[] = 'as.angkatan as angkatan_santri';
                break;
            case 'jumlah_anak_asuh':
                $select[] = DB::raw("COUNT(CASE WHEN ks.status = true THEN aa.id ELSE NULL END) as jumlah_anak_asuh");
                break;
            case 'created_at':
                $select[] = 'gs.created_at';
                break;
            case 'updated_at':
                $select[] = 'gs.updated_at';
                break;
            case 'status':
                $select[] = 'gs.status';
                break;
            }
        }

        $groupBy = [
            'gs.id',
            'gs.nama_grup',
            'w.nama_wilayah',
            's.nis',
            'b.nama',
            'b.jenis_kelamin',
            'gs.created_at',
            'gs.updated_at',
            'gs.status'
        ];

        if (in_array('no_kk', $fields)) {
            $groupBy[] = 'k.no_kk';
        }

        if (in_array('nik', $fields)) {
            $groupBy[] = DB::raw('COALESCE(b.nik, b.no_passport)');
        }

        if (in_array('niup', $fields)) {
            $groupBy[] = 'wp.niup';
        }

        if (in_array('angkatan_santri', $fields)) {
            $groupBy[] = 'as.angkatan';
        }

        $query->groupBy(...$groupBy);

        $query->select($select);

        return $query;
    }


    public function formatDataExportGrupWaliasuh($results, array $fields, $addNumber = false)
    {
        return collect($results)->map(function ($item, $idx) use ($fields, $addNumber) {
            $row = [];

            if ($addNumber) {
                $row['No'] = $idx + 1;
            }

            foreach ($fields as $field) {
                switch ($field) {
                    case 'nama_grup':
                        $row['Nama Grup'] = $item->nama_grup ?? '-';
                        break;
                    case 'nis':
                        $row['NIS Wali Asuh'] = $item->nis ?? '-';
                        break;
                    case 'nama_wali_asuh':
                        $row['Nama Wali Asuh'] = $item->nama_wali_asuh ?? '-';
                        break;
                    case 'nama_wilayah':
                        $row['Wilayah'] = $item->nama_wilayah ?? '-';
                        break;
                    case 'jumlah_anak_asuh':
                        $row['Jumlah Anak Asuh'] = $item->jumlah_anak_asuh ?? 0;
                        break;
                    case 'jenis_kelamin_wali_asuh':
                        $jk = strtolower($item->jenis_kelamin_wali_asuh ?? '');
                        $row['Jenis Kelamin Wali Asuh'] = $jk === 'l' ? 'Laki-laki' : ($jk === 'p' ? 'Perempuan' : '');
                        break;;
                    case 'no_kk':
                        $row['No KK Wali Asuh'] = ' ' . $item->no_kk ?? '';
                        break;
                    case 'nik':
                        $row['NIK Wali Asuh'] = ' ' . ($item->nik ?? $item->no_passport ?? '');
                        break;
                    case 'niup':
                        $row['NIUP Wali Asuh'] = ' ' . $item->niup ?? '';
                        break;
                    case 'angkatan_santri':
                        $row['Angkatan Santri'] = ' ' . $item->angkatan_santri ?? '';
                        break;
                    case 'created_at':
                        $row['Tanggal Input'] = $item->created_at
                            ? Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s')
                            : '-';
                        break;
                    case 'updated_at':
                        $row['Tanggal Update'] = $item->updated_at
                            ? Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s')
                            : '-';
                        break;
                    case 'status':
                        $row['Status'] = $item->status ? 'Aktif' : 'Nonaktif';
                        break;
                }
            }

            return $row;
        })->values();
    }

    public function getFieldExportGrupWaliasuhHeadings(array $fields, bool $addNumber = false): array
    {
        $map = [
            'nama_grup' => 'Nama Grup',
            'nama_wilayah' => 'Wilayah',
            'jumlah_anak_asuh' => 'Jumlah Anak Asuh',
            'no_kk' => 'No KK Wali Asuh',
            'nik' => 'NIK Wali Asuh',
            'niup' => 'NIUP Wali Asuh',
            'nis' => 'NIS Wali Asuh',
            'nama_wali_asuh' => 'Nama Wali Asuh',
            'jenis_kelamin_wali_asuh' => 'Jenis Kelamin Wali Asuh',
            'angkatan_santri' => 'Angkatan Santri',
            'created_at' => 'Tanggal Input',
            'updated_at' => 'Tanggal Update',
            'status' => 'Status',
        ];
        $headings = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $map)) {
                $mapped = $map[$field];
                if (is_array($mapped)) {
                    foreach ($mapped as $h) {
                        $headings[] = $h;
                    }
                } else {
                    $headings[] = $mapped;
                }
            } else {
                $headings[] = $field; // fallback kalau field tidak ada di map
            }
        }
        if ($addNumber) {
            array_unshift($headings, 'No');
        }

        return $headings;

    }

}
