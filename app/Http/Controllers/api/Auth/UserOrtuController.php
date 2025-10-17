<?php

namespace App\Http\Controllers\api\Auth;


use App\Http\Controllers\Controller;
use App\Models\UserOrtu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserOrtuController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Ambil parameter page & per_page (default = 1 & 20)
            $perPage = (int) ($request->input('per_page', 20));
            $page = (int) ($request->input('page', 1));

            // Batasi per_page agar tidak terlalu besar (maksimal 100)
            $perPage = $perPage > 100 ? 100 : $perPage;

            // Ambil data dengan pagination manual (jika perlu filtering, bisa ditambah di sini)
            $data = UserOrtu::query()
                ->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Data user_ortu berhasil diambil.',
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'last_page' => $data->lastPage(),
                ],
                'data' => $data->items()
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Gagal mengambil data user_ortu: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'no_kk' => 'required|string|unique:user_ortu,no_kk',
                'no_hp' => 'nullable|string|unique:user_ortu,no_hp',
                'email' => 'nullable|email|unique:user_ortu,email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            DB::beginTransaction();

            $user = UserOrtu::create([
                'no_kk' => $validated['no_kk'],
                'no_hp' => $validated['no_hp'] ?? null,
                'email' => $validated['email'] ?? null,
                'password' => Hash::make($validated['password']),
            ]);

            // Tambahkan role orang_tua
            if (Role::where('name', 'orang_tua')->exists()) {
                $user->assignRole('orang_tua');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User orang tua berhasil dibuat.',
                'data' => $user
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal membuat user orang tua: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = UserOrtu::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $user
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Gagal menampilkan user_ortu: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = UserOrtu::findOrFail($id);

            $validated = $request->validate([
                'no_kk' => 'required|string|unique:user_ortu,no_kk,' . $user->id,
                'no_hp' => 'nullable|string|unique:user_ortu,no_hp,' . $user->id,
                'email' => 'nullable|email|unique:user_ortu,email,' . $user->id,
                'password' => 'nullable|string|min:8|confirmed',
                'status' => 'boolean'
            ]);

            DB::beginTransaction();

            $user->update([
                'no_kk' => $validated['no_kk'],
                'no_hp' => $validated['no_hp'] ?? $user->no_hp,
                'email' => $validated['email'] ?? $user->email,
                'status' => $validated['status'] ?? $user->status,
                'password' => !empty($validated['password'])
                    ? Hash::make($validated['password'])
                    : $user->password,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data user orang tua berhasil diperbarui.',
                'data' => $user
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui user_ortu: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = UserOrtu::findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User orang tua berhasil dihapus.'
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus user_ortu: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data.'
            ], 500);
        }
    }
}
