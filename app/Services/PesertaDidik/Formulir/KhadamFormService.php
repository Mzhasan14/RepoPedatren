<?php

namespace App\Services\PesertaDidik\Formulir;

use App\Models\Biodata;
use App\Models\Khadam;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KhadamFormService
{
    public function index(string $bioId): array
    {
        $list = Khadam::where('biodata_id', $bioId)->orderByDesc('tanggal_mulai')->get();

        return [
            'status' => true,
            'data' => $list->map(fn ($item) => [
                'id' => $item->id,
                'keterangan' => $item->keterangan,
                'tanggal_mulai' => $item->tanggal_mulai,
                'tanggal_akhir' => $item->tanggal_akhir,
                'status' => $item->status,
            ]),
        ];
    }

    public function store(array $input, string $bioId): array
    {
        return DB::transaction(function () use ($input, $bioId) {
            if (! Biodata::find($bioId)) {
                return ['status' => false, 'message' => 'Biodata tidak ditemukan.'];
            }

            $activeExists = Khadam::where('biodata_id', $bioId)
                ->where('status', true)
                ->exists();

            if ($activeExists) {
                return ['status' => false, 'message' => 'Masih ada khadam aktif untuk santri ini.'];
            }

            $kh = Khadam::create([
                'biodata_id' => $bioId,
                'keterangan' => $input['keterangan'],
                'tanggal_mulai' => Carbon::parse($input['tanggal_mulai']),
                'status' => true,
                'created_by' => Auth::id(),
            ]);

            return ['status' => true, 'data' => $kh];
        });
    }

    public function show(int $id): array
    {
        $kh = Khadam::find($id);

        if (! $kh) {
            return ['status' => false, 'message' => 'Data tidak ditemukan.'];
        }

        return ['status' => true, 'data' => $kh];
    }

    public function update(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $kh = Khadam::find($id);
            if (! $kh) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            // Jika data sudah memiliki tanggal keluar sebelumnya, larang perubahan
            if (! is_null($kh->tanggal_keluar)) {
                return [
                    'status' => false,
                    'message' => 'Data riwayat ini telah memiliki tanggal akhir dan tidak dapat diubah lagi demi menjaga keakuratan histori.',
                ];
            }

            $kh->update([
                'keterangan' => $input['keterangan'],
                'tanggal_mulai' => Carbon::parse($input['tanggal_mulai']),
                'status' => $input['status'] ?? true,
                'updated_at' => now(),
                'updated_by' => Auth::id(),
            ]);

            return ['status' => true, 'data' => $kh];
        });
    }

    public function pindahKhadam(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $old = Khadam::find($id);
            if (! $old) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            if ($old->tanggal_akhir) {
                return ['status' => false, 'message' => 'Khadam sudah ditandai selesai.'];
            }

            $today = Carbon::now();
            $tanggalBaru = Carbon::parse($input['tanggal_mulai']);
            $tanggalLama = Carbon::parse($old->tanggal_mulai);

            if ($tanggalBaru->lt($tanggalLama)) {
                return [
                    'status' => false,
                    'message' => 'Tanggal masuk baru tidak boleh lebih awal dari tanggal masuk sebelumnya ('.$tanggalLama->format('Y-m-d').'). Silakan periksa kembali tanggal yang Anda input.',
                ];
            }
            $old->update([
                'status' => false,
                'tanggal_akhir' => $today,
                'updated_by' => Auth::id(),
            ]);

            $new = Khadam::create([
                'biodata_id' => $old->biodata_id,
                'keterangan' => $input['keterangan'],
                'tanggal_mulai' => $tanggalBaru,
                'status' => true,
                'created_by' => Auth::id(),
            ]);

            return ['status' => true, 'data' => $new];
        });
    }

    public function keluarKhadam(array $input, int $id): array
    {
        return DB::transaction(function () use ($input, $id) {
            $kh = Khadam::find($id);
            if (! $kh) {
                return ['status' => false, 'message' => 'Data tidak ditemukan.'];
            }

            $tglKeluar = Carbon::parse($input['tanggal_akhir'] ?? '');

            if ($tglKeluar->lt(Carbon::parse($kh->tanggal_mulai))) {
                return ['status' => false, 'message' => 'Tanggal akhir tidak boleh sebelum tanggal mulai.'];
            }

            $kh->update([
                'tanggal_akhir' => $tglKeluar,
                'status' => false,
                'updated_by' => Auth::id(),
            ]);

            return ['status' => true, 'data' => $kh];
        });
    }
}
