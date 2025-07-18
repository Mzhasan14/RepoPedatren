<?php

namespace App\Http\Controllers\api\Auth;

use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(10);
        return response()->json($users);
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $user->syncRoles([$data['role']]);

        return response()->json(['message' => 'User created successfully', 'data' => $user->load('roles')], 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load('roles'));
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        if (!empty($data['role'])) {
            $user->syncRoles([]); 
            $user->assignRole($data['role']);
        }

        return response()->json(['message' => 'User updated successfully', 'data' => $user->load('roles')]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
