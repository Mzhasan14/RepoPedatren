<?php

namespace App\Http\Controllers\api\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrasi pengguna baru dengan role "pesertadidik".
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Gunakan transaksi untuk memastikan atomicity
        DB::beginTransaction();
        try {
            // Buat pengguna baru
            $user = User::create([
                'name'     => $validatedData['name'],
                'email'    => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            // Tetapkan role "santri" menggunakan Spatie
            $user->assignRole('santri');

            // Buat token autentikasi
            $token = $user->createToken('auth-token')->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'Registrasi berhasil',
                'user'    => $user,
                'token'   => $token,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan pada saat registrasi',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login pengguna dan buat token autentikasi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Temukan pengguna berdasarkan email
        $user = User::where('email', $validatedData['email'])->first();

        // Cek kecocokan password
        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Buat token autentikasi
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    /**
     * Logout pengguna dengan menghapus token yang digunakan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }
}
