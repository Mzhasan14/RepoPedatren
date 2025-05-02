<?php

namespace App\Http\Controllers\api\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Auth\AuthService;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function register(RegisterRequest $req): JsonResponse
    {
        $user = $this->authService->register($req->validated());
        $token = $user->createToken('auth-token')->plainTextToken;
        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $req): JsonResponse
    {
        $user = $this->authService->login($req->email, $req->password);
        $token = $user->createToken('auth-token')->plainTextToken;
        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout(request()->user()->currentAccessToken());
        return response()->json(null, 204);
    }

    public function forgotPassword(ForgotPasswordRequest $req): JsonResponse
    {
        $status = $this->authService->sendResetLink($req->email);
        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Reset link terkirim.'])
            : response()->json(['message' => __($status)], 500);
    }

    public function resetPassword(ResetPasswordRequest $req): JsonResponse
    {
        $status = $this->authService->resetPassword($req->validated());
        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password berhasil direset.'])
            : response()->json(['message' => __($status)], 500);
    }

    public function updateProfile(UpdateProfileRequest $req): JsonResponse
    {
        $user = $this->authService->updateProfile($req->user(), $req->validated());
        return response()->json(['user' => new UserResource($user)]);
    }

    public function changePassword(ChangePasswordRequest $req): JsonResponse
    {
        $this->authService->changePassword(
            $req->user(),
            $req->current_password,
            $req->new_password
        );
        return response()->json(['message' => 'Password telah diubah. Silakan login ulang.']);
    }
}
// {
//     // Registrasi
//     public function register(Request $req)
//     {
//         $data = $req->validate([
//             'name'                  => 'required|string|max:255',
//             'email'                 => 'required|email|unique:users,email',
//             'password'              => 'required|string|min:8|confirmed',
//         ]);

//         $user = User::create([
//             'name'     => $data['name'],
//             'email'    => $data['email'],
//             'password' => Hash::make($data['password']),
//         ]);

//         $user->assignRole('santri'); // Spatie

//         return response()->json([
//             'user'  => $user,
//             'token' => $user->createToken('auth-token')->plainTextToken,
//         ], 201);
//     }

//     // Login
//     public function login(Request $req)
//     {
//         $data = $req->validate([
//             'email'    => 'required|email',
//             'password' => 'required|string',
//         ]);

//         $user = User::where('email', $data['email'])->first();
//         if (! $user || ! Hash::check($data['password'], $user->password)) {
//             throw ValidationException::withMessages([
//                 'email' => ['Email atau password salah.'],
//             ]);
//         }

//         return response()->json([
//             'user'  => $user,
//             'token' => $user->createToken('auth-token')->plainTextToken,
//         ]);
//     }

//     // Logout
//     public function logout(Request $req)
//     {
//         $req->user()->currentAccessToken()->delete();
//         return response()->json(['message' => 'Logout berhasil.']);
//     }

//     // Kirim link reset password
//     public function forgotPassword(Request $req)
//     {
//         $req->validate(['email' => 'required|email|exists:users,email']);

//         $status = Password::sendResetLink($req->only('email'));

//         return $status === Password::RESET_LINK_SENT
//             ? response()->json(['message' => 'Reset link terkirim.'])
//             : response()->json(['message' => __($status)], 500);
//     }

//     // Reset password via token
//     public function resetPassword(Request $req)
//     {
//         $data = $req->validate([
//             'email'                 => 'required|email|exists:users,email',
//             'token'                 => 'required',
//             'password'              => 'required|string|min:8|confirmed',
//         ]);

//         $status = Password::reset(
//             $data,
//             function (User $user, string $pass) {
//                 $user->password = Hash::make($pass);
//                 $user->setRememberToken(Str::random(60));
//                 $user->tokens()->delete(); // logout semua
//                 $user->save();
//             }
//         );

//         return $status === Password::PASSWORD_RESET
//             ? response()->json(['message' => 'Password berhasil direset.'])
//             : response()->json(['message' => __($status)], 500);
//     }

//     // Update profil (name/email)
//     public function updateProfile(Request $req)
//     {
//         $user = $req->user();
//         $data = $req->validate([
//             'name'  => 'sometimes|required|string|max:255',
//             'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
//         ]);

//         $user->update($data);

//         return response()->json(['user' => $user]);
//     }

//     // Ganti password user login
//     public function changePassword(Request $req)
//     {
//         $data = $req->validate([
//             'current_password' => 'required|string',
//             'new_password'     => 'required|string|min:8|confirmed',
//         ]);

//         $user = $req->user();
//         if (! Hash::check($data['current_password'], $user->password)) {
//             throw ValidationException::withMessages([
//                 'current_password' => ['Password lama tidak sesuai.'],
//             ]);
//         }

//         $user->password = Hash::make($data['new_password']);
//         $user->setRememberToken(Str::random(60));
//         $user->tokens()->delete(); // logout semua
//         $user->save();

//         return response()->json(['message' => 'Password telah diubah. Silakan login ulang.']);
//     }
// }
