<?php

namespace App\Services\PesertaDidik\OrangTua;

use Throwable;
use App\Models\User;
use App\Models\Kartu;
use App\Models\Saldo;
use App\Models\Santri;
use App\Models\Biodata;
use App\Models\UserOrtu;
use Illuminate\Support\Str;
use App\Models\TagihanSantri;
use App\Models\TransaksiSaldo;
use App\Models\VirtualAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthOrtuService
{
    public function register(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {

                if (UserOrtu::where('no_kk', $data['no_kk'])->exists()) {
                    return [
                        'success' => false,
                        'message' => 'Nomor KK sudah terdaftar. Silakan login.',
                        'status'  => 422
                    ];
                }

                $validHubungan = DB::table('santri as s')
                    ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                    ->join('keluarga as k', 'b.id', '=', 'k.id_biodata')
                    ->where('s.status', 'aktif')
                    ->where('k.no_kk', $data['no_kk'])
                    ->where('s.nis', $data['nis_anak'])
                    ->exists();

                if (! $validHubungan) {
                    return [
                        'success' => false,
                        'message' => 'NIS anak tidak sesuai dengan nomor KK.',
                        'status'  => 422
                    ];
                }

                $user = UserOrtu::create([
                    'no_kk'    => $data['no_kk'],
                    'no_hp'    => $data['no_hp'] ?? null,
                    'email'    => $data['email'] ?? null,
                    'password' => Hash::make($data['password']),
                ]);

                $user->assignRole('orang_tua');

                activity('auth_ortu')
                    ->event('register')
                    ->performedOn($user)
                    ->causedBy($user)
                    ->withProperties([
                        'user_id'    => $user->id,
                        'no_kk'      => $user->no_kk,
                        'no_hp'      => $user->no_hp,
                        'email'      => $user->email,
                        'ip'         => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->log("Orang tua dengan no_kk '{$user->no_kk}' berhasil registrasi");

                return [
                    'success' => true,
                    'message' => 'Registrasi berhasil. Data virtual account anak juga dibuat.',
                    'data'    => [
                        'user'   => $user,
                        // 'santri' => $santriList,
                    ],
                    'status'  => 201
                ];
            }, 3); // otomatis retry 3x jika deadlock

        } catch (Throwable $e) {
            Log::error('Error register orang tua', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat registrasi. Silakan coba lagi.',
                'status'  => 500
            ];
        }
    }

    public function login(string $no_hp, string $password)
    {
        try {
            $user = UserOrtu::where('no_hp', $no_hp)->first();

            if (! $user || ! Hash::check($password, $user->password)) {
                return [
                    'success' => false,
                    'message' => 'Nomor HP atau Password salah.',
                    'status'  => 401
                ];
            }
            if (! $user->status) {
                return [
                    'success' => false,
                    'message' => 'Akun Anda tidak aktif. Silakan hubungi admin.',
                    'status'  => 403
                ];
            }

            $anak = DB::table('santri as s')
                ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                ->join('keluarga as k', 'b.id', '=', 'k.id_biodata')
                ->where('k.no_kk', $user->no_kk)
                ->where('s.status', 'aktif')
                ->where(fn($q) => $q->whereNull('b.deleted_at')
                    ->whereNull('s.deleted_at'))
                ->select('s.id', 's.nis', 'b.nama')
                ->get();

            // === Pengecekan tagihan jatuh tempo tiap anak ===
            foreach ($anak as $a) {
                DB::transaction(function () use ($a, $user) {
                    $saldo = Saldo::firstOrCreate(
                        ['santri_id' => $a->id],
                        ['saldo' => 0, 'created_by' => $user->id]
                    );

                    $tagihans = TagihanSantri::where('santri_id', $a->id)
                        ->where('status', 'pending')
                        ->whereDate('tanggal_jatuh_tempo', '<=', now())
                        ->orderBy('tanggal_jatuh_tempo', 'asc')
                        ->lockForUpdate()
                        ->get();

                    foreach ($tagihans as $tagihan) {
                        if ($saldo->saldo <= 0) break;

                        if ($saldo->saldo >= $tagihan->total_tagihan) {
                            $saldo->saldo -= $tagihan->total_tagihan;
                            $saldo->save();

                            $tagihan->status        = 'lunas';
                            $tagihan->tanggal_bayar = now();
                            $tagihan->save();

                            $uidKartu = Kartu::where('santri_id', $a->id)
                                ->where('aktif', true)
                                ->value('uid_kartu');

                            $keteranganTotal = number_format($tagihan->total_tagihan, 0, ',', '.');
                            // Catat transaksi pembayaran otomatis
                            TransaksiSaldo::create([
                                'santri_id'      => $a->id,
                                'outlet_id'      => null,
                                'kategori_id'    => null,
                                'user_outlet_id' => null,
                                'uid_kartu'      => $uidKartu,
                                'tipe'           => 'debit',
                                'jumlah'         => $tagihan->total_tagihan,
                                'keterangan'     => "Pembayaran otomatis tagihan {$tagihan->nama_tagihan} sebesar Rp {$keteranganTotal} dari saldo saat login oleh orang tua",
                            ]);
                        }
                    }
                });
            }

            activity('auth_ortu')
                ->event('login')
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties([
                    'user_id'    => $user->id,
                    'no_kk'      => $user->no_kk,
                    'ip'         => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log("Orang tua dengan no_kk '{$user->no_kk}' berhasil login");

            return [
                'success' => true,
                'message' => 'Login berhasil.',
                'data'    => $user,
                'anak'    => $anak ?? null,
                'status'  => 200
            ];
        } catch (\Throwable $e) {
            Log::error('Error login orang tua', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat login.',
                'status'  => 500
            ];
        }
    }

    public function logout($token)
    {
        $user = Auth::user();

        activity('auth_ortu')
            ->event('logout')
            ->when($user instanceof \Illuminate\Database\Eloquent\Model, function ($log) use ($user) {
                return $log->performedOn($user);
            })
            ->causedBy($user)
            ->withProperties([
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log("Pengguna '{$user->email}' berhasil logout");

        $token->delete();
    }

    public function sendResetLink(string $email): string
    {
        return Password::broker('ortu')->sendResetLink(['email' => $email]);
    }

    public function resetPassword(array $data): string
    {
        return Password::broker('ortu')->reset($data, function (UserOrtu $user, string $password) {
            $user->password = Hash::make($password);
            $user->setRememberToken(Str::random(60));
            $user->save();

            $user->tokens()->delete();

            activity('reset_password')
                ->causedBy($user)
                ->performedOn($user)
                ->withProperties([
                    'ip'         => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'status'     => 'success',
                ])
                ->event('success')
                ->log("User {$user->name} berhasil mereset password.");
        });
    }
    public function updatePassword($user, array $data): array
    {
        try {
            if (!Hash::check($data['current_password'], $user->password)) {
                return [
                    'success' => false,
                    'message' => 'Password lama tidak sesuai.',
                    'status'  => 422
                ];
            }

            $user->update([
                'password' => Hash::make($data['new_password']),
            ]);

            $user->tokens()->delete();
            activity('update_password_ortu')
                ->causedBy($user)
                ->performedOn($user)
                ->withProperties([
                    'ip'             => request()->ip(),
                    'user_agent'     => request()->userAgent(),
                    'status'         => 'success',
                ])
                ->event('success')
                ->log("User {$user->name} berhasil memperbarui password dan seluruh sesi login direset.");

            return [
                'success' => true,
                'message' => 'Password berhasil diperbarui. Silakan login kembali.',
                'status'  => 200
            ];
        } catch (Throwable $e) {
            Log::error('Error update password orang tua', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui password.',
                'status'  => 500
            ];
        }
    }
}
