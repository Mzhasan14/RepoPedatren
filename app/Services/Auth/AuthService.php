<?php
// app/Services/AuthService.php
namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Str;
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

        // Cek apakah yang login adalah admin atau superadmin
        if (!($authUser instanceof User) || !$authUser->hasAnyRole(['admin', 'superadmin'])) {
            throw new AuthorizationException('Anda tidak memiliki akses untuk mendaftarkan pengguna.');
        }

        // Validasi role yang ingin diberikan
        $role = $data['role'] ?? 'santri'; // default ke 'santri' jika tidak ada
        if (!Role::where('name', $role)->exists()) {
            throw new \InvalidArgumentException("Role '{$role}' tidak ditemukan.");
        }

        // Buat user baru
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Berikan role sesuai input
        $user->assignRole($role);

        // Log aktivitas
        activity('auth')
            ->performedOn($user)
            ->causedBy($authUser)
            ->withProperties([
                'new_user_data' => $user->only(['id', 'name', 'email']),
                'created_by'    => $authUser->only(['id', 'name', 'email']),
                'assigned_role' => $role,
            ])
            ->log('Pengguna baru didaftarkan oleh admin');

        return $user;
    }

    public function login(string $email, string $password): User
    {
        $user = User::where('email', $email)->firstOrFail();

        if (! Hash::check($password, $user->password)) {
            abort(422, 'Credentials mismatch.');
        }

        // Menambahkan log aktivitas login
        activity('auth')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Pengguna berhasil login');

        return $user;
    }

    public function logout($token)
    {
        // Menambahkan log aktivitas logout
        activity('auth')
            ->causedBy(Auth::id())
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Pengguna logout');

        $token->delete();
    }

    public function sendResetLink(string $email): string
    {
        // Menambahkan log aktivitas untuk pengiriman reset link
        // activity('auth')
        //     ->withProperties([
        //         'email' => $email,
        //     ])
        //     ->log('Link reset password dikirim ke email');

        return Password::sendResetLink(['email' => $email]);
    }

    public function resetPassword(array $data): string
    {
        return Password::reset($data, function (User $user, string $pass) {
            $user->password = Hash::make($pass);
            $user->setRememberToken(Str::random(60));
            $user->tokens()->delete();
            $user->save();

            // Menambahkan log aktivitas untuk reset password
            activity('auth')
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log('Password berhasil direset');
        });
    }

    public function updateProfile(User $user, array $data): User
    {
        // Simpan perubahan profil
        $user->update($data);

        // Menambahkan log aktivitas untuk pembaruan profil
        activity('auth')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'updated_profile_data' => $data,
            ])
            ->log('Profil pengguna diperbarui');

        return $user;
    }

    public function changePassword(User $user, string $current, string $new): void
    {
        // Validasi password lama
        if (! Hash::check($current, $user->password)) {
            abort(422, 'Current password is incorrect.');
        }

        // Perbarui password
        $user->password = Hash::make($new);
        $user->setRememberToken(Str::random(60));
        $user->tokens()->delete();
        $user->save();

        // Menambahkan log aktivitas untuk perubahan password
        activity('auth')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Password pengguna berhasil diubah');
    }
}
