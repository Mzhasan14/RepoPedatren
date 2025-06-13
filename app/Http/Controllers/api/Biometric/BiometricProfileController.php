<?php

namespace App\Http\Controllers\api\Biometric;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\BiometricFingerprint;
use Illuminate\Support\Facades\Validator;
use App\Models\Biometric\BiometricProfile;

class BiometricProfileController extends Controller
{
    // Membuat profil biometric santri dan fingerprint-nya (sekali proses).
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'santri_id' => 'required|exists:santri,id|unique:biometric_profiles,santri_id',
            'card_uid' => 'nullable|string|unique:biometric_profiles,card_uid',
            'fingerprints' => 'required|array|min:1',
            'fingerprints.*.finger_position' => 'required|string|in:right_thumb,right_index,right_middle,right_ring,right_little,left_thumb,left_index,left_middle,left_ring,left_little',
            'fingerprints.*.template' => 'required|string',
            'fingerprints.*.scan_order' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $profile = BiometricProfile::create([
                'santri_id' => $request->santri_id,
                'card_uid' => $request->card_uid,
            ]);

            foreach ($request->fingerprints as $fp) {
                BiometricFingerprint::create([
                    'biometric_profile_id' => $profile->id,
                    'finger_position'      => $fp['finger_position'],
                    'template'             => $fp['template'],
                    'scan_order'           => $fp['scan_order'] ?? 1,
                ]);
            }
            DB::commit();
            return response()->json(['message' => 'Profil biometric berhasil dibuat', 'data' => $profile->load('fingerprints')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi error', 'error' => $e->getMessage()], 500);
        }
    }

    // Update profil biometric santri (termasuk kartu & fingerprint).
    public function update(Request $request, $id)
    {
        $profile = BiometricProfile::with('fingerprints')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'card_uid' => 'nullable|string|unique:biometric_profiles,card_uid,' . $profile->id,
            'fingerprints' => 'nullable|array',
            'fingerprints.*.id' => 'nullable|exists:biometric_fingerprints,id',
            'fingerprints.*.finger_position' => 'required_with:fingerprints|string|in:right_thumb,right_index,right_middle,right_ring,right_little,left_thumb,left_index,left_middle,left_ring,left_little',
            'fingerprints.*.template' => 'required_with:fingerprints|string',
            'fingerprints.*.scan_order' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $profile->update([
                'card_uid' => $request->card_uid ?? $profile->card_uid,
            ]);

            if ($request->has('fingerprints')) {
                foreach ($request->fingerprints as $fp) {
                    if (isset($fp['id'])) {
                        // Update fingerprint existing
                        $fingerprint = BiometricFingerprint::where('id', $fp['id'])->where('biometric_profile_id', $profile->id)->first();
                        if ($fingerprint) {
                            $fingerprint->update([
                                'finger_position' => $fp['finger_position'],
                                'template' => $fp['template'],
                                'scan_order' => $fp['scan_order'] ?? 1,
                            ]);
                        }
                    } else {
                        // Tambah fingerprint baru
                        BiometricFingerprint::create([
                            'biometric_profile_id' => $profile->id,
                            'finger_position' => $fp['finger_position'],
                            'template' => $fp['template'],
                            'scan_order' => $fp['scan_order'] ?? 1,
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json(['message' => 'Profil biometric berhasil diupdate', 'data' => $profile->fresh('fingerprints')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi error', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $profile = BiometricProfile::findOrFail($id);
        DB::beginTransaction();
        try {
            // Soft delete semua sidik jari yang terkait
            $profile->fingerprints()->delete();
            $profile->delete();

            DB::commit();
            return response()->json(['message' => 'Profil biometric berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi error', 'error' => $e->getMessage()], 500);
        }
    }
}
