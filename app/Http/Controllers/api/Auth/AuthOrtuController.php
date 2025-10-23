<?php

namespace App\Http\Controllers\api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPassOrtuRequest;
use App\Http\Requests\PesertaDidik\OrangTua\LoginRequest;
use App\Http\Requests\PesertaDidik\OrangTua\RegisterRequest;
use App\Http\Requests\ResetPassOrtuRequest;
use App\Http\Requests\UpdatePassOrtuRequest;
use App\Services\PesertaDidik\OrangTua\AuthOrtuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class AuthOrtuController extends Controller
{
    private $service;

    public function __construct(AuthOrtuService $service)
    {
        $this->service = $service;
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->service->register($request->validated());

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data'    => $result['data'] ?? null
        ], $result['status']);
    }

    public function login(LoginRequest $req)
    {
        $result = $this->service->login($req->no_hp, $req->password);

        if ($result['success']) {
            $user  = $result['data'];
            $anak = $result['anak'] ?? null;
            // $token = $user->createToken('auth-token')->plainTextToken;
            $token = $user->createToken('auth_token', [$user->getRoleNames()->first()])->plainTextToken;

            // Set expired token manual (misal 8 jam)
            $user->tokens()->latest()->first()->forceFill([
                'expires_at' => now()->addHours(8),
            ])->save();

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'token'   => $token,
                'data'    => $user,
                'anak'    => $anak
            ], $result['status']);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], $result['status']);
    }

    public function logout(): JsonResponse
    {
        $this->service->logout(request()->user()->currentAccessToken());

        return response()->json(null, 204);
    }

    public function forgotPassword(ForgotPassOrtuRequest $req): JsonResponse
    {
        $status = $this->service->sendResetLink($req->email);

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Link reset password telah dikirim ke email.'])
            : response()->json(['message' => __($status)], 500);
    }

    public function resetPassword(ResetPassOrtuRequest $req): JsonResponse
    {
        $status = $this->service->resetPassword($req->validated());

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password berhasil direset.'])
            : response()->json(['message' => __($status)], 500);
    }
    public function updatePassword(UpdatePassOrtuRequest $req): JsonResponse
    {
        $result = $this->service->updatePassword($req->user(), $req->validated());

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['status']);
    }
}
