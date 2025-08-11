<?php

namespace App\Http\Controllers\api\Auth;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $users = User::with('roles')->paginate(10);
            return response()->json($users);
        } catch (\Throwable $e) {
            Log::error('Error fetching users: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to fetch users'], 500);
        }
    }

    public function store(UserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);
            $user->syncRoles([$data['role']]);

            return response()->json([
                'message' => 'User created successfully',
                'data' => $user->load('roles')
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error creating user: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to create user'], 500);
        }
    }

    public function show(User $user): JsonResponse
    {
        try {
            return response()->json($user->load('roles'));
        } catch (\Throwable $e) {
            Log::error("Error showing user {$user->id}: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to retrieve user'], 500);
        }
    }

    public function update(UserRequest $request, User $user): JsonResponse
    {
        try {
            $data = $request->validated();

            // Cegah user menonaktifkan akun miliknya sendiri
            if (
                isset($data['status']) &&
                (bool) $user->status === true &&     // status lama aktif
                (bool) $data['status'] === false &&  // status baru nonaktif
                Auth::id() === $user->id           // user edit dirinya sendiri
            ) {
                return response()->json([
                    'message' => 'Aksi ini tidak dapat dilakukan pada akun yang sedang digunakan.'
                ], 403);
            }

            // Hash password jika ada
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            // Update role jika ada
            if (!empty($data['role'])) {
                $user->syncRoles([]);
                $user->assignRole($data['role']);
            }

            return response()->json([
                'message' => 'User updated successfully',
                'data' => $user->load('roles')
            ]);
        } catch (\Throwable $e) {
            Log::error("Error updating user {$user->id}: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to update user'], 500);
        }
    }


    public function destroy(User $user): JsonResponse
    {
        try {
            $user->delete();
            return response()->json(['message' => 'User deleted successfully']);
        } catch (\Throwable $e) {
            Log::error("Error deleting user {$user->id}: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to delete user'], 500);
        }
    }

    // public function changeStatus(User $user): JsonResponse
    // {
    //     try {
    //         $user->status = !$user->status;
    //         $user->save();

    //         return response()->json([
    //             'message' => 'User status updated successfully',
    //             'data' => $user
    //         ]);
    //     } catch (\Throwable $e) {
    //         Log::error("Error changing status for user {$user->id}: " . $e->getMessage(), ['exception' => $e]);
    //         return response()->json(['message' => 'Failed to change user status'], 500);
    //     }
    // }
}
