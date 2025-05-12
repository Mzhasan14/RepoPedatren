<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\WargaPesantren;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WargaPesantrenService
{
    public function index($bioId)
    {
        $santri = WargaPesantren::where('biodata_id', $bioId)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'niup' => $item->niup,
                    'status' => $item->status
                ];
            });

        if (!$santri) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $santri];
    }

    public function store(array $data, $id)
    {
        return DB::transaction(function () use ($data, $id) {
            if (WargaPesantren::where('biodata_id', $id)->where('status', true)) {
                return ['status' => false, 'message' => 'Biodata sudah memiliki NIUP aktif'];
            }

            $warga = new WargaPesantren();
            $warga->biodata_id = $id;
            $warga->niup = $data['niup'];
            $warga->status = $data['status'];
            $warga->created_by = Auth::id();
            $warga->save();

            return ['status' => true, 'data' => $warga];
        });
    }

    public function edit(string $id)
    {
        $wp = WargaPesantren::where('id', $id)
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

            // Tidak boleh ubah niup
            if ($wp->niup !== $data['niup']) {
                return ['status' => false, 'message' => 'NIUP tidak boleh diubah'];
            }

            // Jika tidak ada perubahan status, tolak
            if ($wp->status === $data['status']) {
                return ['status' => false, 'message' => 'Tidak ada perubahan status'];
            }

            $wp->status = $data['status'];
            $wp->updated_by = Auth::id();
            $wp->save();

            return ['status' => true, 'data' => $wp];
        });
    }
}
