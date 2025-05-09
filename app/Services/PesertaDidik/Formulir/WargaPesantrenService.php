<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\WargaPesantren;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WargaPesantrenService
{
    public function store(array $data, $bioId)
    {
        return DB::transaction(function () use ($data, $bioId) {
            if (WargaPesantren::where('biodata_id', $bioId)->exists()) {
                return ['status' => false, 'message' => 'Biodata sudah memiliki NIUP'];
            }

            $warga = new WargaPesantren();
            $warga->biodata_id = $bioId;
            $warga->niup = $data['niup'];
            $warga->status = $data['status'];
            $warga->created_by = Auth::id();
            $warga->save();

            activity('warga_pesantren')
                ->causedBy(Auth::user())
                ->performedOn($warga)
                ->withProperties([
                    'new_attributes' => $warga->toArray(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->event('create')
                ->log('Menambah data warga pesantren baru dengan NIUP: ' . $data['niup']);

            return ['status' => true, 'data' => $warga];
        });
    }

    public function edit(string $bioId)
    {
        $wp = WargaPesantren::where('biodata_id', $bioId)
            ->latest()
            ->first(['id', 'niup', 'status']);

        return $wp
            ? ['status' => true, 'data' => $wp]
            : ['status' => false, 'data' => []];
    }

    public function update(array $data, string $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $wp = WargaPesantren::find($id);

            if (!$wp) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }

            if (!$wp->status) {
                return ['status' => false, 'message' => 'Data riwayat tidak boleh diubah'];
            }

            $before = $wp->toArray();
            $isNiupChanged = $wp->niup !== $data['niup'];
            $isStatusChanged = $wp->status !== $data['status'];

            if (!$isNiupChanged && !$isStatusChanged) {
                return ['status' => false, 'message' => 'Tidak ada perubahan data'];
            }

            // Jika hanya perubahan status ke false → update langsung
            if (!$data['status']) {
                $wp->status = false;
                $wp->updated_by = Auth::id();
                $wp->save();

                activity('warga_pesantren')
                    ->causedBy(Auth::user())
                    ->performedOn($wp)
                    ->withProperties([
                        'before' => $before,
                        'after' => $wp->toArray(),
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->event('update')
                    ->log('Menonaktifkan data warga pesantren dengan ID: ' . $wp->id);

                return ['status' => true, 'data' => $wp];
            }

            // Jika ada perubahan NIUP atau perubahan status ke true → buat record baru
            $wp->status = false;
            $wp->updated_by = Auth::id();
            $wp->save();

            activity('warga_pesantren')
                ->causedBy(Auth::user())
                ->performedOn($wp)
                ->withProperties([
                    'before' => $before,
                    'after' => $wp->toArray(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->event('update')
                ->log('Menonaktifkan data lama warga pesantren ID: ' . $wp->id);

            $newWp = new WargaPesantren();
            $newWp->biodata_id = $wp->biodata_id;
            $newWp->niup = $data['niup'];
            $newWp->status = $data['status'];
            $newWp->created_by = Auth::id();
            $newWp->save();

            activity('warga_pesantren')
                ->causedBy(Auth::user())
                ->performedOn($newWp)
                ->withProperties([
                    'new_attributes' => $newWp->toArray(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->event('create')
                ->log('Menambah data warga pesantren baru dengan NIUP: ' . $data['niup']);

            return ['status' => true, 'data' => $newWp];
        });
    }
}
