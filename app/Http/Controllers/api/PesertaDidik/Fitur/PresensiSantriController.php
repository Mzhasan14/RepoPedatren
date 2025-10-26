<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\PresensiSantriRequest;
use App\Models\Biometric\BiometricLog;
use App\Models\Biometric\BiometricProfile;
use App\Models\JenisPresensi;
use App\Models\PresensiSantri;
use App\Services\PesertaDidik\Fitur\PresensiSantriService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PresensiSantriController extends Controller
{
    protected $service;

    public function __construct(PresensiSantriService $service)
    {
        $this->service = $service;
    }

    public function getAllPresensiSantri(Request $request)
    {
        try {
            $query = $this->service->getAllPresensiSantri($request);
            $query = $query->latest('ps.id');

            $perPage = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);

            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (Exception $e) {
            Log::error('Error Presensi getAllPresensiSantri', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        if ($results->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data kosong',
                'data' => [],
            ], 200);
        }

        $formatted = $this->service->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
        ]);
    }

    public function store(PresensiSantriRequest $request)
    {
        try {
            $presensi = $this->service->store($request->validated(), Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil dicatat.',
                'data' => $presensi,
            ], 201);
        } catch (Exception $e) {
            Log::error('Error Presensi Santri STORE', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(PresensiSantriRequest $request, PresensiSantri $presensi)
    {
        try {
            $presensi = $this->service->update($presensi, $request->validated(), Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil diupdate.',
                'data' => $presensi,
            ], 200);
        } catch (Exception $e) {
            Log::error('Error Presensi Santri UPDATE', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(PresensiSantri $presensi)
    {
        try {
            $this->service->delete($presensi, Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil dihapus.',
            ], 200);
        } catch (Exception $e) {
            Log::error('Error Presensi Santri DESTROY', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function presensiBiometric(Request $request)
    {
        // Data dari alat
        $finger_id = $request->input('finger_id');
        $card_uid = $request->input('card_uid');
        $device_id = $request->input('device_id');
        $metode = $request->input('metode');
        $waktu = $request->input('waktu', now());
        $lokasi = $request->input('lokasi');
        $jenis_presensi_kode = $request->input('jenis_presensi_kode');

        // Cek dan ambil jenis presensi
        $jenisPresensi = JenisPresensi::where('kode', $jenis_presensi_kode)->first();
        if (! $jenisPresensi) {
            return response()->json(['message' => 'Jenis presensi tidak ditemukan'], 404);
        }

        // Temukan profile santri
        $profile = BiometricProfile::where('card_uid', $card_uid)
            ->orWhereHas('fingerprints', function ($q) use ($finger_id) {
                $q->where('template', $finger_id);
            })->first();

        if (! $profile) {
            return response()->json(['message' => 'Santri tidak ditemukan'], 404);
        }

        // Catat log biometrik
        $log = BiometricLog::create([
            'biometric_profile_id' => $profile->id,
            'device_id' => $device_id,
            'method' => $metode,
            'scanned_at' => $waktu,
            'success' => true,
            'message' => 'Presensi berhasil',
        ]);

        // Catat presensi santri
        $presensi = PresensiSantri::updateOrCreate([
            'santri_id' => $profile->santri_id,
            'jenis_presensi_id' => $jenisPresensi->id,
            'tanggal' => date('Y-m-d', strtotime($waktu)),
        ], [
            'waktu_presensi' => date('H:i:s', strtotime($waktu)),
            'status' => 'hadir',
            'metode' => $metode,
            'biometric_log_id' => $log->id,
            'device_id' => $device_id,
            'lokasi' => $lokasi,
            'created_by' => null,
        ]);

        return response()->json(['message' => 'Presensi tercatat', 'data' => $presensi]);
    }
}
