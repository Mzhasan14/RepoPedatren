<?php

namespace App\Services\PesertaDidik\Fitur;

use App\Models\Kartu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class KartuService
{
    public function getAll(int $perPage = 25)
    {
        $paginator = Kartu::with([
            'santri:id,nis,biodata_id',
            'santri.biodata:id,nama'
        ])
            ->latest()
            ->paginate($perPage);

        return [
            'total_data'   => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'total_pages'  => $paginator->lastPage(),
            'data'         => $paginator->getCollection()->map(fn($item) => $this->transform($item))->values(),
        ];
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
