<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\WargaPesantren;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WargaPesantrenService
{
    public function index(string $biodataId): array
    {
        $wp = WargaPesantren::where('biodata_id', $biodataId)
            ->get();

        if ($wp->isEmpty()) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan.',
            ];
        }

        $data = $wp->map(fn (WargaPesantren $wp) => [
            'id' => $wp->id,
            'niup' => $wp->niup,
            'status' => (bool) $wp->status,
        ])->toArray();

        return [
            'status' => true,
            'data' => $data,
        ];
    }

    public function store(array $input, string $biodataId): array
    {
        return DB::transaction(function () use ($input, $biodataId) {
            // Cek apakah sudah ada yang aktif
            $exists = WargaPesantren::where('biodata_id', $biodataId)
                ->where('status', true)
                ->exists();

            if ($exists) {
                return [
                    'status' => false,
                    'message' => 'Biodata sudah memiliki NIUP aktif.',
                ];
            }

            // Validasi input minimal
            if (empty($input['niup'])) {
                return [
                    'status' => false,
                    'message' => 'NIUP wajib diisi.',
                ];
            }

            $wp = WargaPesantren::create([
                'biodata_id' => $biodataId,
                'niup' => $input['niup'],
                'status' => (bool) ($input['status'] ?? true),
                'created_by' => Auth::id(),
            ]);

            return [
                'status' => true,
                'data' => $wp,
            ];
        });
    }

    public function show(int $id): array
    {
        $wp = WargaPesantren::find($id);

        if (! $wp) {
            return [
                'status' => false,
                'message' => "Data WargaPesantren ID #{$id} tidak ditemukan.",
            ];
        }

        return [
            'status' => true,
            'data' => [
                'id' => $wp->id,
                'niup' => $wp->niup,
                'status' => (bool) $wp->status,
            ],
        ];
    }

    public function update(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $wp = WargaPesantren::find($id);
            if (! $wp) {
                return [
                    'status' => false,
                    'message' => 'Data tidak ditemukan.',
                ];
            }

            // NIUP tidak boleh diubah
            if (isset($input['niup']) && $input['niup'] !== $wp->niup) {
                return [
                    'status' => false,
                    'message' => 'NIUP tidak dapat diubah.',
                ];
            }

            // Pastikan ada perubahan status
            $newStatus = (bool) ($input['status'] ?? $wp->status);
            if ($newStatus === (bool) $wp->status) {
                return [
                    'status' => false,
                    'message' => 'Tidak ada perubahan status.',
                ];
            }

            $userId = Auth::id();
            if (! $userId) {
                return [
                    'status' => false,
                    'message' => 'Pengguna tidak terautentikasi.',
                ];
            }

            $wp->status = $newStatus;
            $wp->updated_by = $userId;
            $wp->save();

            return [
                'status' => true,
                'data' => $wp,
            ];
        });
    }

    public function delete(int $id): array
    {
        $wp = WargaPesantren::find($id);
        if (! $wp) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan.',
            ];
        }

        $wp->delete();

        return [
            'status' => true,
            'message' => 'Data berhasil dihapus.',
        ];
    }
}
