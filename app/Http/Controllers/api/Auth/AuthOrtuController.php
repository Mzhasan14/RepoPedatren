<?php

namespace App\Http\Controllers\api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\OrangTua\LoginRequest;
use App\Http\Requests\PesertaDidik\OrangTua\RegisterRequest;
use App\Services\PesertaDidik\OrangTua\AuthOrtuService;
use Illuminate\Http\Request;

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
            $token = $user->createToken('auth-token')->plainTextToken;

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
}

