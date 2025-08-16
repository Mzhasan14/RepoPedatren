<?php

namespace App\Http\Controllers\api\Auth;

use App\Models\User;
use App\Models\Biodata;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;

class UserController extends Controller
{
    /**
     * Tampilkan daftar user beserta role.
     */
    public function index(): JsonResponse
    {
        try {
            $users = User::with('roles')->paginate(10);
            return response()->json($users);
        } catch (\Throwable $e) {
            Log::error('Gagal mengambil data user: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Gagal mengambil data user'], 500);
        }
    }

    /**
     * Simpan user baru dan kaitkan/create biodata dengan aman.
     */
    public function store(UserRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $actor = $request->user();

        // Validasi autentikasi dan hak akses
        // if (!$actor) {
        //     return response()->json(['message' => 'Belum login.'], 401);
        // }
        if (!$actor->hasRole('superadmin')) {
            return response()->json(['message' => 'Tidak berhak menugaskan role'], 403);
        }

        DB::beginTransaction();
        try {
            // Buat user baru
            $user = User::create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'password' => Hash::make($payload['password']),
                'status' => (bool)($payload['status'] ?? true),
            ]);

            // Tetapkan role
            $user->syncRoles([$payload['role']]);

            // Inisialisasi variabel biodata
            $biodataModel = null;
            $note = null;

            // Tangani biodata berdasarkan NIK atau biodata_id
            if (!empty($payload['biodata'])) {
                $b = $payload['biodata'];
                $biodataModel = $this->handleBiodataCreationOrMapping($b, $actor, $note);
            } elseif (!empty($payload['biodata_id'])) {
                $byId = Biodata::find($payload['biodata_id']);
                if ($byId) {
                    $biodataModel = $byId;
                    $note = 'Biodata sudah ada, dipetakan melalui biodata_id.';
                }
            }

            // Simpan mapping user <-> biodata
            if ($biodataModel) {
                DB::table('user_biodata')->insert([
                    'biodata_id' => $biodataModel->id,
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            $resp = [
                'message' => 'User berhasil dibuat',
                'data' => $user->load('roles'),
            ];
            if ($biodataModel) $resp['data']->biodata = $biodataModel;
            if ($note) $resp['note'] = $note;

            return response()->json($resp, 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal membuat user: ' . $e->getMessage(), ['exception' => $e, 'payload' => $payload]);
            return response()->json(['message' => 'Gagal membuat user'], 500);
        }
    }

    /**
     * Tampilkan detail user beserta biodata dan role.
     */
    public function show(User $user): JsonResponse
    {
        try {
            $biodataId = DB::table('user_biodata')->where('user_id', $user->id)->value('biodata_id');
            $biodata = $biodataId ? Biodata::find($biodataId) : null;

            $user = $user->load('roles');
            $user->biodata = $biodata;

            return response()->json($user);
        } catch (\Throwable $e) {
            Log::error("Gagal menampilkan user {$user->id}: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Gagal mengambil data user'], 500);
        }
    }

    /**
     * Update user dan tangani mapping biodata.
     */
    public function update(UserRequest $request, User $user): JsonResponse
    {
        $payload = $request->validated();
        $actor = $request->user();

        if (!$actor) {
            return response()->json(['message' => 'Belum login.'], 401);
        }

        // Hak akses update role
        if (!empty($payload['role']) && ! $actor->hasRole('superadmin')) {
            return response()->json(['message' => 'Tidak berhak mengubah role'], 403);
        }

        // Cegah user menonaktifkan akun sendiri
        if (isset($payload['status']) && (bool)$user->status && !(bool)$payload['status'] && $actor->id === $user->id) {
            return response()->json(['message' => 'Aksi ini tidak dapat dilakukan pada akun yang sedang digunakan.'], 403);
        }

        DB::beginTransaction();
        try {
            // Hash password jika ada
            if (!empty($payload['password'])) {
                $payload['password'] = Hash::make($payload['password']);
            } else {
                unset($payload['password']);
            }

            // Update data user
            $user->update([
                'name' => $payload['name'] ?? $user->name,
                'email' => $payload['email'] ?? $user->email,
                'password' => $payload['password'] ?? $user->password,
                'status' => isset($payload['status']) ? (bool)$payload['status'] : $user->status,
            ]);

            // Update role jika ada
            if (!empty($payload['role'])) {
                $user->syncRoles([]);
                $user->assignRole($payload['role']);
            }

            // Update atau mapping biodata
            $biodataModel = null;
            $note = null;

            if (!empty($payload['biodata'])) {
                $biodataModel = $this->handleBiodataCreationOrMapping($payload['biodata'], $actor, $note, $user->id);
            } elseif (!empty($payload['biodata_id'])) {
                $byId = Biodata::find($payload['biodata_id']);
                if ($byId) {
                    DB::table('user_biodata')->updateOrInsert(
                        ['user_id' => $user->id],
                        ['biodata_id' => $byId->id, 'updated_at' => now(), 'created_at' => now()]
                    );
                    $biodataModel = $byId;
                    $note = 'Biodata sudah ada, dipetakan melalui biodata_id.';
                }
            }

            DB::commit();

            $response = [
                'message' => 'User berhasil diperbarui',
                'data' => $user->load('roles'),
            ];
            if ($biodataModel) $response['data']->biodata = $biodataModel;
            if ($note) $response['note'] = $note;

            return response()->json($response);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal memperbarui user {$user->id}: " . $e->getMessage(), ['exception' => $e, 'payload' => $payload]);
            return response()->json(['message' => 'Gagal memperbarui user'], 500);
        }
    }

    /**
     * Hapus user.
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $user->delete();
            return response()->json(['message' => 'User berhasil dihapus']);
        } catch (\Throwable $e) {
            Log::error("Gagal menghapus user {$user->id}: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Gagal menghapus user'], 500);
        }
    }

    /**
     * Tangani pembuatan atau pemetaan biodata untuk store/update.
     */
    private function handleBiodataCreationOrMapping(array $b, $actor, ?string &$note = null, ?int $userId = null)
    {
        $biodataModel = null;

        if (!empty($b['nik'])) {
            // Cek biodata berdasarkan NIK
            $found = Biodata::where('nik', $b['nik'])->lockForUpdate()->first();

            if ($found) {
                $biodataModel = $found;
                $note = 'Biodata dengan NIK ditemukan, digunakan atau dipetakan.';
            } else {
                // Buat biodata baru
                try {
                    $b['created_by'] = $actor->id;
                    $biodataModel = Biodata::create($b);
                    $note = 'Biodata baru dibuat.';
                } catch (QueryException $qe) {
                    Log::warning('Race condition saat membuat biodata: ' . $qe->getMessage(), ['biodata' => $b]);
                    $biodataModel = Biodata::where('nik', $b['nik'])->first();
                    if (!$biodataModel) throw $qe;
                    $note = 'Biodata concurrent request ditemukan, dipetakan.';
                }
            }
        } else {
            // Tanpa NIK, selalu buat biodata baru
            $b['created_by'] = $actor->id;
            $biodataModel = Biodata::create($b);
            $note = 'Biodata baru dibuat.';
        }

        // Map ke user jika userId diberikan
        if ($userId && $biodataModel) {
            DB::table('user_biodata')->updateOrInsert(
                ['user_id' => $userId],
                ['biodata_id' => $biodataModel->id, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        return $biodataModel;
    }
}