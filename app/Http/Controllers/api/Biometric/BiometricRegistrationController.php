<?php

namespace App\Http\Controllers\api\Biometric;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Biometric\BiometricProfile;
use App\Models\Biometric\BiometricFingerPosition;
use App\Models\Biometric\BiometricFingerprintTemplate;

class BiometricRegistrationController extends Controller
{
    /**
     * Registrasi profil biometric baru untuk santri.
     */
    public function registerProfile(Request $request)
    {
        $validated = $request->validate([
            'santri_id' => 'required|exists:santri,id',
            'card_uid' => 'nullable|string',
        ]);

        $profile = BiometricProfile::create([
            'id' => Str::uuid(),
            'santri_id' => $validated['santri_id'],
            'card_uid' => $validated['card_uid'] ?? null,
        ]);

        return response()->json(['message' => 'Profile created', 'profile' => $profile], 201);
    }

    /**
     * Tambah jari baru (finger position) untuk profil tertentu.
     */
    public function addFingerPosition(Request $request)
    {
        $validated = $request->validate([
            'biometric_profile_id' => 'required|exists:biometric_profiles,id',
            'finger_position' => 'required|string', // e.g., right_thumb
        ]);

        $position = BiometricFingerPosition::create([
            'id' => Str::uuid(),
            'biometric_profile_id' => $validated['biometric_profile_id'],
            'finger_position' => $validated['finger_position'],
        ]);

        return response()->json(['message' => 'Finger position added', 'position' => $position], 201);
    }

    /**
     * Simpan template hasil scan untuk satu jari (multi-scan).
     */
    public function storeFingerprintTemplates(Request $request)
    {
        $validated = $request->validate([
            'finger_position_id' => 'required|exists:biometric_finger_positions,id',
            'templates' => 'required|array|min:1',
            'templates.*' => 'required|string', // base64 atau format template fingerprint
        ]);

        $templates = [];
        foreach ($validated['templates'] as $index => $template) {
            $templates[] = [
                'id' => Str::uuid(),
                'finger_position_id' => $validated['finger_position_id'],
                'template' => $template,
                'scan_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        BiometricFingerprintTemplate::insert($templates);

        return response()->json(['message' => 'Templates stored', 'count' => count($templates)], 201);
    }

    /**
     * Ambil semua template fingerprint untuk santri tertentu.
     */
    public function getTemplatesBySantri($santri_id)
    {
        $profile = BiometricProfile::with('fingerPositions.fingerprintTemplates')
            ->where('santri_id', $santri_id)
            ->first();

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        return response()->json($profile);
    }
}
