<?php

namespace App\Http\Controllers\api\Biometric;

use Illuminate\Support\Str;
use App\Models\BiometricLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\BiometricDevice;
use App\Models\BiometricProfile;
use App\Http\Controllers\Controller;

class BiometricScanController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'santri_id' => 'required|uuid|exists:santri,id',
            'fingerprint_template' => 'nullable|string',
            'card_uid' => 'nullable|string',
        ]);

        $profile = BiometricProfile::updateOrCreate(
            ['santri_id' => $request->santri_id],
            [
                'id' => Str::uuid(),
                'fingerprint_template' => $request->fingerprint_template,
                'card_uid' => $request->card_uid,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Biometric profile berhasil disimpan',
            'data' => $profile,
        ]);
    }

    public function scan(Request $request)
    {
        $request->validate([
            'device_id' => 'required|uuid|exists:biometric_devices,id',
            'method' => 'required|in:fingerprint,card',
            'data' => 'required|string',
        ]);

        $device = BiometricDevice::find($request->device_id);
        $profile = null;

        if ($request->method === 'fingerprint') {
            $profile = BiometricProfile::where('fingerprint_template', $request->data)->first();
        } else {
            $profile = BiometricProfile::where('card_uid', $request->data)->first();
        }

        $log = BiometricLog::create([
            'id' => Str::uuid(),
            'biometric_profile_id' => $profile?->id,
            'device_id' => $device->id,
            'method' => $request->method,
            'scanned_at' => Carbon::now(),
            'success' => (bool) $profile,
            'message' => $profile ? 'Scan berhasil' : 'Data tidak dikenali',
        ]);

        return response()->json([
            'status' => $profile ? 'success' : 'fail',
            'message' => $log->message,
            'santri_id' => $profile?->santri_id,
            'log_id' => $log->id,
        ]);
    }
}
