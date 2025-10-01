<?php

namespace App\Services\PesertaDidik\OrangTua;

use App\Models\User;
use App\Models\Saldo;
use App\Models\Biodata;
use App\Models\UserOrtu;
use App\Models\TagihanSantri;
use App\Models\TransaksiSaldo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class AuthOrtuService
{
    public function register($data)
    {
        try {
            if (UserOrtu::where('no_kk', $data['no_kk'])->exists()) {
                return [
                    'success' => false,
                    'message' => 'Nomor KK sudah terdaftar. silakan login.',
                    'status'  => 422
                ];
            }

            $cekHubungan = DB::table('santri as s')
                ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                ->join('keluarga as k', 'b.id', '=', 'k.id_biodata')
                ->where('k.no_kk', $data['no_kk'])
                ->where('s.nis', $data['nis_anak'])
                ->exists();

            if (!$cekHubungan) {
                return [
                    'success' => false,
                    'message' => 'NIS anak tidak sesuai dengan nomor KK.',
                    'status'  => 422
                ];
            }

            if (UserOrtu::where('no_kk', $data['no_kk'])->exists()) {
                return [
                    'success' => false,
                    'message' => 'Nomor KK sudah terdaftar. silakan login.',
                    'status'  => 422
                ];
            }

            $user = UserOrtu::create([
                'no_kk' => $data['no_kk'],
                'no_hp' => $data['no_hp'] ?? null,
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('orang_tua');

            // if ($bioOrtu === null) {
            //     return [
            //         'success' => false,
            //         'message' => 'Data orang tua/wali tidak ditemukan. Pastikan NIK atau nama sesuai dengan data santri.',
            //         'status'  => 404
            //     ];
            // }

            // $result = DB::transaction(function () use ($bioOrtu, $data) {
            //     $biodata = Biodata::findOrFail($bioOrtu->biodata_id);

            //     if (empty($biodata->nik)) {
            //         $biodata->nik = $data['nik_ortu'];
            //         $biodata->save();
            //     }

            //     $user = UserOrtu::create([
            //         'biodata_id' => $biodata->id,
            //         'password' => Hash::make($data['password']),
            //     ]);

            //     $user->assignRole('orang_tua');

            //     return $user;
            // });

            return [
                'success' => true,
                'message' => 'Registrasi berhasil. Silakan login.',
                'data'    => $user,
                'status'  => 201
            ];
        } catch (\Throwable $e) {
            Log::error('Error register orang tua', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $data
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

                            // Catat transaksi pembayaran otomatis
                            TransaksiSaldo::create([
                                'santri_id'      => $a->id,
                                'outlet_id'      => null, 
                                'kategori_id'    => null,
                                'user_outlet_id' => null,
                                'tipe'           => 'debit',
                                'jumlah'         => $tagihan->total_tagihan,
                                'keterangan'     => "Pembayaran otomatis tagihan #{$tagihan->id} sebesar Rp{$tagihan->total_tagihan} dari saldo saat login oleh orang tua",
                            ]);
                        }
                    }
                });
            }

            activity('auth')
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


    // public function login(string $no_hp, string $password)
    // {
    //     try {

    //         $user = UserOrtu::where('no_hp', $no_hp)->first();

    //         if (! $user || ! Hash::check($password, $user->password)) {
    //             return [
    //                 'success' => false,
    //                 'message' => 'Nomor HP atau Password salah.',
    //                 'status'  => 401
    //             ];
    //         }
    //         if (! $user->status) {
    //             return [
    //                 'success' => false,
    //                 'message' => 'Akun Anda tidak aktif. Silakan hubungi admin.',
    //                 'status'  => 403
    //             ];
    //         }

    //         $anak = DB::table('santri as s')
    //             ->join('biodata as b', 's.biodata_id', '=', 'b.id')
    //             ->join('keluarga as k', 'b.id', '=', 'k.id_biodata')
    //             ->where('k.no_kk', $user->no_kk)
    //             ->select('s.id', 's.nis', 'b.nama')
    //             ->get();

    //         activity('auth')
    //             ->event('login')
    //             ->performedOn($user)
    //             ->causedBy($user)
    //             ->withProperties([
    //                 'user_id'    => $user->id,
    //                 'no_kk'   => $user->no_kk,
    //                 'ip'         => request()->ip(),
    //                 'user_agent' => request()->userAgent(),
    //             ])
    //             ->log("Orang tua dengan no_kk '{$user->no_kk}' berhasil login");


    //         return [
    //             'success' => true,
    //             'message' => 'Login berhasil.',
    //             'data'   => $user,  
    //             'anak'    => $anak ?? null,
    //             'status'  => 200
    //         ];

    //     } catch (\Throwable $e) {
    //         Log::error('Error login orang tua', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //         ]);

    //         return [
    //             'success' => false,
    //             'message' => 'Terjadi kesalahan saat login.',
    //             'status'  => 500
    //         ];
    //     }
    // }
    // public function register($data)
    // {
    //     try {
    //         $noKk = DB::table('santri as s')
    //             ->join('biodata as b', 's.biodata_id', '=', 'b.id')
    //             ->join('keluarga as k', 'b.id', '=', 'k.id_biodata')
    //             ->where('s.nis', $data['nis_anak'])
    //             ->value('k.no_kk');

    //         $bioOrtu = DB::table('keluarga as k')
    //             ->join('biodata as b', 'k.id_biodata', '=', 'b.id')
    //             ->join('orang_tua_wali as otw', 'k.id_biodata', '=', 'otw.id_biodata')
    //             ->where(function ($j) use ($data) {
    //                 $j->where('b.nik', $data['nik_ortu'])
    //                     ->orWhere(DB::raw('LOWER(b.nama)'), strtolower($data['nama_ortu']));
    //             })
    //             ->where('k.no_kk', $noKk)
    //             ->select('k.id_biodata as biodata_id')
    //             ->first();

    //         if ($bioOrtu === null) {
    //             return [
    //                 'success' => false,
    //                 'message' => 'Data orang tua/wali tidak ditemukan. Pastikan NIK atau nama sesuai dengan data santri.',
    //                 'status'  => 404
    //             ];
    //         }

    //         $result = DB::transaction(function () use ($bioOrtu, $data) {
    //             $biodata = Biodata::findOrFail($bioOrtu->biodata_id);

    //             if (empty($biodata->nik)) {
    //                 $biodata->nik = $data['nik_ortu'];
    //                 $biodata->save();
    //             }

    //             $user = UserOrtu::create([
    //                 'biodata_id' => $biodata->id,
    //                 'password' => Hash::make($data['password']),
    //             ]);

    //             $user->assignRole('orang_tua');

    //             return $user;
    //         });

    //         return [
    //             'success' => true,
    //             'message' => 'Registrasi berhasil. Silakan login.',
    //             'data'    => $result,
    //             'status'  => 201
    //         ];

    //     } catch (\Throwable $e) {
    //         Log::error('Error register orang tua', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'input' => $data
    //         ]);

    //         return [
    //             'success' => false,
    //             'message' => 'Terjadi kesalahan saat registrasi. Silakan coba lagi.',
    //             'status'  => 500
    //         ];
    //     }
    // }

    // public function login($data)
    // {
    //     try {
    //         $noKk = DB::table('santri as s')
    //             ->join('biodata as b', 's.biodata_id', '=', 'b.id')
    //             ->join('keluarga as k', 'b.id', '=', 'k.id_biodata')
    //             ->where('s.nis', $data['nis_anak'])
    //             ->value('k.no_kk');

    //         $bioOrtu = DB::table('keluarga as k')
    //             ->leftJoin('biodata as b_anak', 'k.id_biodata', '=', 'b_anak.id')
    //             ->leftJoin('santri as s', 'b_anak.id', '=', 's.biodata_id')
    //             ->whereNull('s.id')
    //             ->where('k.no_kk', $noKk)
    //             ->select('k.id_biodata as biodata_id')
    //             ->get();

    //         if ($bioOrtu->isEmpty()) {
    //             return [
    //                 'success' => false,
    //                 'message' => 'NIS atau Password salah.',
    //                 'status'  => 401
    //             ];
    //         }

    //         foreach ($bioOrtu as $ortu) {
    //             // Ambil biodata beserta semua user dan roles
    //             $biodata = Biodata::with(['user' => function ($q) {
    //                 $q->whereHas('roles', function ($r) {
    //                     $r->where('name', 'orang_tua');
    //                 })->with('roles'); 
    //             }])->find($ortu->biodata_id);
    //             if (!$biodata || $biodata->user->isEmpty()) {
    //                 continue;
    //             }

    //             foreach ($biodata->user as $user) {
    //                 if (
    //                     $user->hasRole('orang_tua') &&
    //                     Hash::check($data['password'], $user->password)
    //                 ) {
    //                     return [
    //                         'success' => true,
    //                         'message' => 'Login berhasil.',
    //                         'data'    => $user,
    //                         'status'  => 200
    //                     ];
    //                 }
    //             }
    //         }

    //         return [
    //             'success' => false,
    //             'message' => 'NIS atau Password salah.',
    //             'status'  => 401
    //         ];
    //     } catch (\Throwable $e) {
    //         Log::error('Error login orang tua', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'input' => $data
    //         ]);

    //         return [
    //             'success' => false,
    //             'message' => 'Terjadi kesalahan saat login.',
    //             'status'  => 500
    //         ];
    //     }
    // }
}
