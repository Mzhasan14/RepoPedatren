<?php

namespace App\Services\Kewaliasuhan;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Kewaliasuhan\Grup_WaliAsuh;

class GrupWaliasuhService
{
    public function getAllGrupWaliasuh(Request $request)
    {
        return DB::table('grup_wali_asuh AS gs')
            ->join('wali_asuh as ws', 'gs.id', '=', 'ws.id_grup_wali_asuh')
            ->join('kewaliasuhan as ks', 'ks.id_wali_asuh', '=', 'ws.id')
            ->join('anak_asuh AS aa', 'ks.id_anak_asuh', '=', 'aa.id')
            ->join('santri AS s', 'ws.id_santri', '=', 's.id')
            ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
            ->leftJoin('wilayah AS w', 'gs.id_wilayah', '=', 'w.id')
            ->where('gs.status', true)
            ->select([
                'gs.id',
                'gs.nama_grup as group',
                's.nis',
                'b.nama',
                'w.nama_wilayah',
                DB::raw("COUNT(aa.id) as jumlah_anak_asuh"),
                'gs.updated_at',
                'gs.created_at',
            ])
            ->groupBy(
                'gs.id',
                'gs.nama_grup',
                's.nis',
                'b.nama',
                'w.nama_wilayah',
                'gs.updated_at',
                'gs.created_at'
            )
            ->orderBy('gs.id');
        }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            "id" => $item->id,
            "group" => $item->group,
            "nis_wali_asuh" => $item->nis,
            "nama_wali_asuh" => $item->nama,
            "wilayah" => $item->nama_wilayah,
            "jumlah_anak_asuh" => $item->jumlah_anak_asuh,
            "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
        ]);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            if (!Auth::id()) {
                return [
                    'status' => false,
                    'message' => 'Pengguna tidak terautentikasi',
                    'data' => null
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
                'updated_at' => now()
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
                'data' => $grup
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

            $updateData = [
                'id_wilayah' => $data['id_wilayah'],
                'nama_grup' => $data['nama_grup'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'updated_by' => Auth::id(),
                'status' => $data['status'] ?? true,
                'updated_at' => now()
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
}
