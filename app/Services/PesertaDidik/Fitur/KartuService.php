<?php

namespace App\Services\PesertaDidik\Fitur;

use App\Models\Kartu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class KartuService
{
    public function getAll($request, int $perPage = 25)
    {
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');
            
        $query = DB::table('kartu as k')
            ->leftJoin('santri as s', 'k.santri_id', '=', 's.id')
            ->leftJoin('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftJoin('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
            ->leftJoin('domisili_santri AS ds', fn($join) => $join->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'))
            ->leftJoin('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
            ->leftjoin('kabupaten AS kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->select(
                'k.*',
                's.id as santri_id',
                's.nis',
                'b.id as biodata_id',
                'b.nama'
            )
            ->orderByDesc('k.created_at');

        return $query;
    }

    public function getById(int $id)
    {
        $kartu = Kartu::with([
            'santri:id,nis,biodata_id',
            'santri.biodata:id,nama'
        ])->findOrFail($id);

        return ['data' => $this->transform($kartu)];
    }

    public function create(array $data)
    {
        if (!empty($data['pin'])) {
            $data['pin'] = Hash::make($data['pin']);
        }

        $data['created_by'] = Auth::id();

        $kartu = Kartu::create($data);
        $kartu->load([
            'santri:id,nis,biodata_id',
            'santri.biodata:id,nama'
        ]);

        activity('kartu')
            ->causedBy(Auth::user())
            ->performedOn(new Kartu(['id' => $kartu->id]))
            ->withProperties([
                'santri_id'  => $kartu->santri_id,
                'nis'        => $kartu->santri->nis ?? null,
                'nama'    => $kartu->santri->biodata->nama ?? null,
                'ip'         => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->event('create')
            ->log("Kartu baru berhasil dibuat");

        return ['data' => $this->transform($kartu)];
    }

    public function update(int $id, array $data)
    {
        $kartu = Kartu::findOrFail($id);

        if (!empty($data['pin'])) {
            $data['pin'] = Hash::make($data['pin']);
        }

        $data['updated_by'] = Auth::id();
        $kartu->update($data);

        $kartu->load([
            'santri:id,nis,biodata_id',
            'santri.biodata:id,nama'
        ]);

        activity('kartu')
            ->causedBy(Auth::user())
            ->performedOn(new Kartu(['id' => $kartu->id]))
            ->withProperties([
                'santri_id'  => $kartu->santri_id,
                'nis'        => $kartu->santri->nis ?? null,
                'nama'    => $kartu->santri->biodata->nama ?? null,
                'ip'         => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->event('update')
            ->log("Kartu berhasil diupdate");

        return ['data' => $this->transform($kartu)];
    }

    public function delete(int $id)
    {
        $kartu = Kartu::findOrFail($id);
        $kartu->deleted_by = Auth::id();
        $kartu->save();

        $kartu->delete();

        activity('kartu')
            ->causedBy(Auth::user())
            ->performedOn(new Kartu(['id' => $kartu->id]))
            ->withProperties([
                'santri_id'  => $kartu->santri_id,
                'nis'        => $kartu->santri->nis ?? null,
                'nama'    => $kartu->santri->biodata->nama ?? null,
                'ip'         => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->event('delete')
            ->log("Kartu berhasil dihapus");

        return ['message' => 'Kartu berhasil dihapus'];
    }

    private function transform($kartu)
    {
        return [
            'id'              => $kartu->id,
            'santri_id'       => $kartu->santri->id ?? null,
            'nis'             => $kartu->santri->nis ?? null,
            'nama'            => $kartu->santri->biodata->nama ?? null,
            'uid_kartu'       => $kartu->uid_kartu,
            'aktif'           => (bool) $kartu->aktif,
            'tanggal_terbit'  => $kartu->tanggal_terbit,
            'tanggal_expired' => $kartu->tanggal_expired,
            'created_at'      => $kartu->created_at,
            'updated_at'      => $kartu->updated_at,
        ];
    }
}
