<?php

namespace App\Http\Controllers\api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function register(RegisterRequest $req): JsonResponse
    {
        $user = $this->authService->register($req->validated());
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $req): JsonResponse
    {
        $result = $this->authService->login($req->email, $req->password);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['status']);
        }

        $user = $result['data'];
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'user' => new UserResource($user),
            'token' => $token,
        ], $result['status']);
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
