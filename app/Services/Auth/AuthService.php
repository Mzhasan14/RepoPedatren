<?php

// app/Services/AuthService.php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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

        $user = User::where('email', $email)->first();

        if (! $user) {
            return [
                'success' => false,
                'message' => 'User not found.',
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
