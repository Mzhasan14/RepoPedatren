<?php

namespace App\Services\Kewaliasuhan;

use App\Models\Kewaliasuhan\Anak_asuh;
use App\Models\Kewaliasuhan\Grup_WaliAsuh;
use App\Models\Kewaliasuhan\Kewaliasuhan;
use App\Models\Kewaliasuhan\Wali_asuh;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KewaliasuhanService
{
    public function createGrup(array $data)
    {
        $userId = Auth::id();

        return DB::transaction(function () use ($data, $userId) {
            $now = Carbon::now();

            $anakIds = array_column($data['anak_asuh'], 'id_santri');
            $waliId = $data['wali_asuh']['id_santri'];

            // ===== Validasi dasar =====
            if (in_array($waliId, $anakIds)) {
                throw new \Exception("Santri yang dipilih sebagai wali asuh tidak boleh ada dalam daftar anak asuh.");
            }

            // ===== Validasi wali asuh: tidak boleh sudah anak asuh aktif =====
            $waliExistAsAnak = DB::table('anak_asuh')
                ->where('id_santri', $waliId)
                ->where('status', true)
                ->exists();

            if ($waliExistAsAnak) {
                throw new \Exception("Santri yang dipilih sebagai wali asuh sudah menjadi anak asuh aktif.");
            }

            // ===== Validasi wali asuh: tidak boleh sudah wali asuh aktif =====
            $waliExistAsWali = DB::table('wali_asuh')
                ->where('id_santri', $waliId)
                ->where('status', true)
                ->exists();

            if ($waliExistAsWali) {
                throw new \Exception("Santri yang dipilih sebagai wali asuh sudah menjadi wali asuh aktif.");
            }

            // ===== Validasi anak asuh: tidak boleh sudah anak asuh aktif =====
            $anakExistAsAnak = DB::table('anak_asuh as a')
                ->join('santri as s', 's.id', '=', 'a.id_santri')
                ->join('biodata as b', 'b.id', '=', 's.biodata_id')
                ->whereIn('a.id_santri', $anakIds)
                ->where('a.status', true)
                ->pluck('b.nama')
                ->toArray();

            if (count($anakExistAsAnak) > 0) {
                throw new \Exception("Santri berikut sudah menjadi anak asuh aktif: " . implode(', ', $anakExistAsAnak) . ".");
            }

            // ===== Validasi anak asuh: tidak boleh sudah wali asuh aktif =====
            $anakExistAsWali = DB::table('wali_asuh as w')
                ->join('santri as s', 's.id', '=', 'w.id_santri')
                ->join('biodata as b', 'b.id', '=', 's.biodata_id')
                ->whereIn('w.id_santri', $anakIds)
                ->where('w.status', true)
                ->pluck('b.nama')
                ->toArray();

            if (count($anakExistAsWali) > 0) {
                throw new \Exception("Santri berikut sudah menjadi wali asuh aktif: " . implode(', ', $anakExistAsWali) . ".");
            }


            // ===== Ambil data wali asuh (nama + gender) =====
            $waliSantri = DB::table('santri as s')
                ->join('biodata as b', 'b.id', '=', 's.biodata_id')
                ->where('s.id', $waliId)
                ->select('s.id as santri_id', 'b.nama as nama_lengkap', 'b.jenis_kelamin')
                ->first();

            if (!$waliSantri) {
                throw new \Exception("Data wali asuh tidak ditemukan.");
            }

            $waliGender = $waliSantri->jenis_kelamin;

            // ===== Ambil semua anak asuh (nama + gender) =====
            $anakList = DB::table('santri as s')
                ->join('biodata as b', 'b.id', '=', 's.biodata_id')
                ->whereIn('s.id', $anakIds)
                ->get(['s.id as santri_id', 'b.nama as nama_lengkap', 'b.jenis_kelamin']);

            // ===== Validasi gender anak asuh vs wali =====
            foreach ($anakList as $anak) {
                if ($anak->jenis_kelamin !== $waliGender) {
                    throw new \Exception("Santri {$anak->nama_lengkap} (ID {$anak->santri_id}) jenis kelaminnya tidak sama dengan wali asuh {$waliSantri->nama_lengkap}.");
                }
            }

            // ===== Validasi gender grup vs wali =====
            if ($waliGender !== $data['jenis_kelamin']) {
                throw new \Exception("Jenis kelamin grup tidak sesuai dengan wali asuh {$waliSantri->nama_lengkap}.");
            }

            // ===== 1. Buat grup =====
            $grup = Grup_WaliAsuh::create([
                'id_wilayah'    => $data['id_wilayah'],
                'nama_grup'     => $data['nama_grup'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'created_by'    => $userId,
            ]);

            // ===== 2. Buat wali asuh =====
            $waliAsuh = Wali_Asuh::create([
                'id_santri'           => $waliSantri->santri_id,
                'id_grup_wali_asuh'   => $grup->id,
                'tanggal_mulai'       => $now,
                'created_by'          => $userId,
            ]);

            $anakAsuhIds = [];

            // ===== 3. Buat anak asuh & relasi kewaliasuhan =====
            foreach ($anakList as $anak) {
                $anakAsuh = Anak_Asuh::create([
                    'id_santri'  => $anak->santri_id,
                    'created_by' => $userId,
                ]);

                $anakAsuhIds[] = $anakAsuh->id;

                Kewaliasuhan::create([
                    'id_wali_asuh'   => $waliAsuh->id,
                    'id_anak_asuh'   => $anakAsuh->id,
                    'tanggal_mulai'  => $now,
                    'created_by'     => $userId,
                ]);
            }

            return [
                'status'    => true,
                'message'   => 'Grup wali asuh berhasil dibuat',
                'grup'      => $grup,
                'wali_asuh' => $waliAsuh,
                'anak_asuh' => $anakAsuhIds,
            ];
        });
    }
    public function update(array $data)
    {
        $userId = Auth::id();
        $relasi = Kewaliasuhan::findOrFail($data['id']);

        DB::beginTransaction();
        try {
            $relasi->update([
                'tanggal_berakhir' => $data['tanggal_berakhir'] ?? $relasi->tanggal_berakhir,
                'status' => $data['status'] ?? $relasi->status,
                'updated_by' => $userId,
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Relasi anak asuh berhasil diperbarui.',
                'data' => $relasi,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Gagal memperbarui relasi anak asuh.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function delete($id)
    {
        $userId = Auth::id();
        $relasi = Kewaliasuhan::findOrFail($id);

        DB::beginTransaction();
        try {
            $relasi->update([
                'deleted_by' => $userId,
            ]);
            $relasi->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Relasi anak asuh berhasil dihapus.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Gagal menghapus relasi anak asuh.',
                'error' => $e->getMessage(),
            ];
        }
    }
}
