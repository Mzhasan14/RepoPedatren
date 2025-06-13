<?php

namespace App\Services\PesertaDidik\Fitur;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\PresensiSantri;
use Illuminate\Support\Facades\DB;

class PresensiSantriService
{

    public function basePresensiSantriQuery(Request $request)
    {
        $query = DB::table('presensi_santri as ps')
            ->join('santri as s', 'ps.santri_id', 's.id')
            ->join('biodata as b', 's.biodata_id', 'b.id')
            ->join('jenis_presensi as jp', 'ps.jenis_presensi_id', 'jp.id');

        return $query;
    }

    public function getAllPresensiSantri(Request $request, $fields = null)
    {
        $query = $this->basePresensiSantriQuery($request);

        $fields = $fields ?? [
            'ps.id',
            'b.nama as nama_santri',
            'jp.nama as nama_presensi',
            'ps.tanggal',
            'ps.waktu_presensi',
            'ps.status',
            'ps.lokasi',
            'ps.metode',
        ];

        return $query->select($fields);
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'id'             => $item->id,
            'nama_santri'    => $item->nama_santri,
            'nama_presensi'  => $item->nama_presensi,
            'tanggal'        => Carbon::parse($item->tanggal)->translatedFormat('d F Y'), // Misal: 13 Juni 2025
            'waktu_presensi' => $item->waktu_presensi ?? '-',
            'status'         => ucfirst($item->status), // Hadir/Izin/Sakit/Alfa
            'lokasi'         => $item->lokasi ?? '-',
            'metode'         => strtoupper($item->metode ?? '-'),
        ]);
    }

    /**
     * Create new presensi, with unique rule per santri, jenis, tanggal
     */
    public function store(array $data, int $userId)
    {
        // Cek duplikasi
        $cek = PresensiSantri::where('santri_id', $data['santri_id'])
            ->where('jenis_presensi_id', $data['jenis_presensi_id'])
            ->where('tanggal', $data['tanggal'])
            ->first();
        if ($cek) {
            throw new Exception('Presensi untuk santri ini, tanggal ini, dan jenis ini sudah pernah dicatat.');
        }

        $data['created_by'] = $userId;
        // Pastikan waktu_presensi diisi dengan waktu saat ini jika null
        if (empty($data['waktu_presensi'])) {
            $data['waktu_presensi'] = now()->format('H:i:s');
        }
        $data['metode'] = $data['metode'] ?? 'manual';

        return PresensiSantri::create($data);
    }

    /**
     * Update presensi
     */
    public function update(PresensiSantri $presensi, array $data, int $userId)
    {
        // Cek duplikasi kalau ada perubahan tanggal/jenis
        if (
            ($data['tanggal'] !== $presensi->tanggal->format('Y-m-d') || $data['jenis_presensi_id'] != $presensi->jenis_presensi_id)
        ) {
            $cek = PresensiSantri::where('santri_id', $presensi->santri_id)
                ->where('jenis_presensi_id', $data['jenis_presensi_id'])
                ->where('tanggal', $data['tanggal'])
                ->where('id', '<>', $presensi->id)
                ->first();
            if ($cek) {
                throw new Exception('Presensi untuk kombinasi ini sudah pernah dicatat.');
            }
        }
        $data['updated_by'] = $userId;
        $presensi->update($data);
        return $presensi;
    }

    public function delete(PresensiSantri $presensi, int $userId)
    {
        $presensi->update(['deleted_by' => $userId]);
        $presensi->delete();
    }
}
