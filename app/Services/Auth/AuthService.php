<?php

// app/Services/AuthService.php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Access\AuthorizationException;

class AuthService
{
    public function register(array $data): User
    {
        $authUser = Auth::user();

        if (! ($authUser instanceof User) || ! $authUser->hasAnyRole(['admin', 'superadmin'])) {
            throw new AuthorizationException('Anda tidak memiliki akses untuk mendaftarkan pengguna.');
        }

        $role = $data['role'] ?? 'santri';
        if (! Role::where('name', $role)->exists()) {
            throw new \InvalidArgumentException("Role '{$role}' tidak ditemukan.");
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($role);

        return $user;
    }

    public function login(string $email, string $password): array
    {
        $user = User::with('detail_user_outlet', 'biodata')->where('email', $email)->first();

        if (! $user) {
            return [
                'success' => false,
                'message' => 'User not found.',
                'data' => null,
                'status' => 200,
            ];
        }

        if (! $user->status) {
            return [
                'success' => false,
                'message' => 'User is inactive.',
                'data' => null,
                'status' => 200,
            ];
        }

        if (! Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'message' => 'Incorrect password.',
                'data' => null,
                'status' => 200,
            ];
        }

        if ($user->hasRole('orang_tua')) {
            $biodataId = $user->biodata->id;
            $noKk = DB::table('keluarga as k')
                ->where('k.id_biodata', $biodataId)
                ->value('no_kk');

            if (!$noKk) {
                return [
                    'success' => false,
                    'message' => 'Data keluarga tidak ditemukan.',
                    'data' => null,
                    'status' => 404,
                ];
            }

            $anak = DB::table('keluarga as k')
                ->join('biodata as b', 'k.id_biodata', '=', 'b.id')
                ->join('santri as s', 'b.id', '=', 's.biodata_id')
                ->leftjoin('orang_tua_wali as otw', 'b.id', '=', 'otw.id_biodata')
                ->select('b.id as biodata_id', 's.id as santri_id', 'b.nama')
                ->whereNull('otw.id_biodata')
                ->where('k.no_kk', $noKk)
                ->where('k.id_biodata', '!=', $biodataId)->get();

            if ($anak->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada data anak yang ditemukan.',
                    'data' => null,
                    'status' => 404,
                ];
            }

            $anakData = $anak->map(function ($item) {
                return [
                    'biodata_id' => $item->biodata_id,
                    'santri_id' => $item->santri_id,
                    'nama' => $item->nama,
                ];
            });
        }

        activity('auth')
            ->event('login')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log("Pengguna '{$user->email}' berhasil login");

        return [
            'success' => true,
            'message' => 'Login successful.',
            'data' => $user,
            'status' => 200,
            'outlet_id' => $user->detail_user_outlet?->outlet_id, // null jika tidak ada
            'anak' => $anakData ?? null,
        ];
    }


    public function logout($token)
    {
        $user = Auth::user();

        activity('auth')
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
        return Password::sendResetLink(['email' => $email]);
    }

    public function resetPassword(array $data): string
    {
        return Password::reset($data, function (User $user, string $pass) {
            $user->password = Hash::make($pass);
            $user->setRememberToken(Str::random(60));
            $user->tokens()->delete();
            $user->save();
        });
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->update($data);

        activity('auth')
            ->event('updated')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'user_id' => $user->id,
                'email' => $user->email,
                'updated_profile_data' => $data,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log("Profil pengguna '{$user->email}' diperbarui");

        return $user;
    }

    public function changePassword(User $user, string $current, string $new): void
    {
        if (! Hash::check($current, $user->password)) {
            abort(422, 'Current password is incorrect.');
        }

        $user->password = Hash::make($new);
        $user->setRememberToken(Str::random(60));
        $user->tokens()->delete();
        $user->save();

        activity('auth')
            ->event('password_changed')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log("Password pengguna '{$user->email}' berhasil diubah");
    }
}
