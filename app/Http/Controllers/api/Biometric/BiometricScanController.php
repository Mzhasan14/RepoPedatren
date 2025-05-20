<?php

namespace App\Http\Controllers\api\Biometric;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Biometric\BiometricLog;
use App\Models\Biometric\BiometricProfile;
use App\Models\Biometric\BiometricFingerprintTemplate;

class BiometricScanController extends Controller
{
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:biometric_devices,id',
            'method' => 'required|in:fingerprint,card',
            'identifier' => 'required|string', // bisa card_uid atau fingerprint template (hash misalnya)
        ]);

        $profile = null;
        $success = false;
        $message = 'Not found';

        if ($validated['method'] === 'card') {
            $profile = BiometricProfile::where('card_uid', $validated['identifier'])->first();
            if ($profile) {
                $success = true;
                $message = 'Card matched';
            }
        } elseif ($validated['method'] === 'fingerprint') {
            // Simplified matching by template string or hash
            $template = BiometricFingerprintTemplate::where('template', $validated['identifier'])->first();
            if ($template) {
                $position = $template->fingerPosition;
                $profile = $position->profile ?? null;
                if ($profile) {
                    $success = true;
                    $message = 'Fingerprint matched';
                }
            }
        }

        // Simpan ke log
        BiometricLog::create([
            'id' => Str::uuid(),
            'biometric_profile_id' => $profile?->id,
            'device_id' => $validated['device_id'],
            'method' => $validated['method'],
            'scanned_at' => now(),
            'success' => $success,
            'message' => $message,
        ]);

        return response()->json([
            'success' => $success,
            'message' => $message,
            'profile' => $profile,
        ]);
    }
}
