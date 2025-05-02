<?php
// app/Services/AuthService.php
namespace App\Services\Auth;    

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthService
{
    public function register(array $data): User
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->assignRole('santri');
        return $user;
    }

    public function login(string $email, string $password): User
    {
        $user = User::where('email', $email)->firstOrFail();
        if (! Hash::check($password, $user->password)) {
            abort(422, 'Credentials mismatch.');
        }
        return $user;
    }

    public function logout($token)
    {
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
    }
}
