<?php

namespace App\Services\PesertaDidik\Fitur;

use Exception;
use App\Models\Kartu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
            ->whereNull('k.deleted_by')
            ->whereNull('k.deleted_at')
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
        // 🔒 Cek apakah santri masih punya kartu aktif
        if (!empty($data['santri_id']) && !empty($data['aktif']) && $data['aktif']) {
            $sudahAktif = Kartu::where('santri_id', $data['santri_id'])
                ->where('aktif', true)
                ->exists();

            if ($sudahAktif) {
                throw ValidationException::withMessages([
                    'santri_id' => 'Santri ini sudah memiliki kartu aktif. Nonaktifkan kartu sebelumnya terlebih dahulu.'
                ]);
            }
        }

        if (!empty($data['pin'])) {
            $data['pin'] = Hash::make($data['pin']);
        }

        $data['created_by'] = Auth::id();

        $kartu = Kartu::create($data);
        $kartu->load(['santri:id,nis,biodata_id', 'santri.biodata:id,nama']);

        // 🧾 Log aktivitas
        activity('kartu')
            ->causedBy(Auth::user())
            ->performedOn($kartu)
            ->withProperties([
                'santri_id'  => $kartu->santri_id,
                'nis'        => $kartu->santri->nis ?? null,
                'nama'       => $kartu->santri->biodata->nama ?? null,
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

        // 🔒 Jika akan mengubah jadi aktif, pastikan tidak ada kartu aktif lain untuk santri ini
        if (!empty($data['aktif']) && $data['aktif'] && $kartu->santri_id) {
            $sudahAktif = Kartu::where('santri_id', $kartu->santri_id)
                ->where('id', '!=', $id)
                ->where('aktif', true)
                ->exists();

            if ($sudahAktif) {
                throw ValidationException::withMessages([
                    'aktif' => 'Santri ini sudah memiliki kartu aktif lain. Nonaktifkan kartu sebelumnya terlebih dahulu.'
                ]);
            }
        }

        if (!empty($data['pin'])) {
            $data['pin'] = Hash::make($data['pin']);
        }

        $data['updated_by'] = Auth::id();
        $kartu->update($data);

        $kartu->load(['santri:id,nis,biodata_id', 'santri.biodata:id,nama']);

        activity('kartu')
            ->causedBy(Auth::user())
            ->performedOn($kartu)
            ->withProperties([
                'santri_id'  => $kartu->santri_id,
                'nis'        => $kartu->santri->nis ?? null,
                'nama'       => $kartu->santri->biodata->nama ?? null,
                'ip'         => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->event('update')
            ->log("Kartu berhasil diupdate");

        return ['data' => $this->transform($kartu)];
    }

    public function nonactive(int $id)
    {
        $kartu = Kartu::findOrFail($id);

        $kartu->aktif = false;
        $kartu->updated_by = Auth::id();
        $kartu->updated_at = now();
        $kartu->save();

        activity('kartu')
            ->causedBy(Auth::user())
            ->performedOn($kartu)
            ->withProperties([
                'santri_id'  => $kartu->santri_id,
                'nis'        => $kartu->santri->nis ?? null,
                'nama'       => $kartu->santri->biodata->nama ?? null,
                'ip'         => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->event('deactivate')
            ->log("Kartu berhasil dinonaktifkan");

        return ['message' => 'Kartu berhasil dinonaktifkan'];
    }

    public function activate(int $id)
    {
        $kartu = Kartu::findOrFail($id);

        // 🔒 Pastikan tidak ada kartu aktif lain untuk santri ini
        $sudahAktif = Kartu::where('santri_id', $kartu->santri_id)
            ->where('id', '!=', $id)
            ->where('aktif', true)
            ->exists();

        if ($sudahAktif) {
            throw ValidationException::withMessages([
                'aktif' => 'Santri ini sudah memiliki kartu aktif lain. Nonaktifkan kartu sebelumnya terlebih dahulu.'
            ]);
        }

        $kartu->aktif = true;
        $kartu->updated_by = Auth::id();
        $kartu->updated_at = now();
        $kartu->save();

        activity('kartu')
            ->causedBy(Auth::user())
            ->performedOn($kartu)
            ->withProperties([
                'santri_id'  => $kartu->santri_id,
                'nis'        => $kartu->santri->nis ?? null,
                'nama'       => $kartu->santri->biodata->nama ?? null,
                'ip'         => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->event('activate')
            ->log("Kartu berhasil diaktifkan kembali");

        return ['message' => 'Kartu berhasil diaktifkan kembali'];
    }


    public function destroy(int $id)
    {
        $kartu = Kartu::find($id);

        if (! $kartu) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $kartu->aktif      = false;
        $kartu->deleted_by = Auth::id();
        $kartu->save();

        $kartu->delete();

        activity('kartu')
            ->causedBy(Auth::user())
            ->performedOn($kartu)
            ->withProperties([
                'santri_id'  => $kartu->santri_id,
                'nis'        => $kartu->santri->nis ?? null,
                'nama'       => $kartu->santri->biodata->nama ?? null,
                'ip'         => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->event('soft_delete')
            ->log("Kartu berhasil dihapus (soft delete)");

        return ['message' => 'Kartu berhasil dihapus'];
    }

    public function RiwayatKartu(int $id): array
    {
        try {
            $kartu = DB::table('kartu')->find($id);

            if (!$kartu) {
                return [
                    'status' => false,
                    'code' => 404,
                    'message' => 'Data kartu dengan ID ' . $id . ' tidak ditemukan.',
                    'data' => null
                ];
            }

            $uid_kartu = $kartu->uid_kartu;


            $riwayat = DB::table('transaksi_saldo as t')
                ->join('santri as s', 's.id', '=', 't.santri_id')
                ->leftJoin('outlets as o', 'o.id', '=', 't.outlet_id')
                ->leftJoin('kategori as kk', 'kk.id', '=', 't.kategori_id')
                ->where('t.uid_kartu', $uid_kartu)
                ->select([
                    's.biodata_id',
                    't.id',
                    'o.nama_outlet',
                    'kk.nama_kategori',
                    't.tipe',
                    't.keterangan',
                    't.jumlah'
                ])
                ->orderBy('t.created_at', 'desc')
                ->get();

            return [
                'status' => true,
                'code' => 200,
                'message' => 'Data riwayat spesifik kartu berhasil ditampilkan.',
                'data' => $riwayat
            ];
        } catch (\Throwable $e) {
            Log::error('Error di Service RiwayatKartu: ' . $e->getMessage());
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Terjadi kesalahan pada server.',
            ];
        }
    }

    public function setLimitSaldo(int $santriId, ?float $limitSaldo, bool $takTerbatas): array
    {
        try {
            return DB::transaction(function () use ($santriId, $limitSaldo, $takTerbatas) {

                $user = Auth::user();

                // 🔹 Ambil kartu santri dengan Eloquent
                $kartu = Kartu::where('santri_id', $santriId)->where('aktif', true)->first();
                if (!$kartu) {
                    throw new Exception('Kartu aktif santri tidak ditemukan.');
                }

                // 🔹 Simpan limit lama untuk log
                $limitLama = $kartu->limit_saldo;

                // 🔹 Tentukan nilai limit baru
                $limitFinal = $takTerbatas ? null : $limitSaldo;

                // 🔹 Update kartu
                $kartu->update([
                    'limit_saldo' => $limitFinal,
                    'updated_by'  => $user->id,
                    'updated_at'  => now(),
                ]);

                activity('kartu')
                    ->causedBy($user)
                    ->performedOn($kartu)
                    ->withProperties([
                        'santri_id'     => $santriId,
                        'tipe'          => 'limit_update',
                        'limit_sebelum' => $limitLama,
                        'limit_sesudah' => $limitFinal,
                        'tak_terbatas'  => $takTerbatas,
                        'ip'            => request()->ip(),
                        'user_agent'    => request()->userAgent(),
                    ])
                    ->event('update_limit')
                    ->log(
                        $takTerbatas
                            ? "Limit saldo santri ID {$santriId} diatur menjadi tak terbatas."
                            : "Limit saldo santri ID {$santriId} diperbarui dari " .
                            ($limitLama ?? '∞') . " ke {$limitFinal}."
                    );

                return [
                    'success' => true,
                    'message' => $takTerbatas
                        ? 'Limit saldo diatur menjadi tak terbatas.'
                        : 'Limit saldo berhasil diperbarui.',
                    'limit_saldo' => $limitFinal,
                ];
            });
        } catch (Exception $e) {
            Log::error('Gagal memperbarui limit saldo.', [
                'santri_id' => $santriId,
                'error'     => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui limit saldo: ' . $e->getMessage(),
            ];
        }
    }

    private function transform($kartu)
    {
        return [
            'id'              => $kartu->id,
            'biodata_id'      => $kartu->santri->biodata_id,
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
