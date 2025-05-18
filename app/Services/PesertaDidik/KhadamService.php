<?php

namespace App\Services\PesertaDidik;

use App\Models\Khadam;
use App\Models\Santri;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class KhadamService
{
    public function getAllKhadam(Request $request)
    {
        // 1) Ambil ID jenis berkas 'Pas foto'
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        // Subqueries: ID terakhir berkas pas foto
        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        // Subqueries: ID terakhir warga pesantren yang aktif
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        return DB::table('khadam as kh')
            ->join('biodata as b', 'kh.biodata_id', '=', 'b.id')
            ->leftjoin('santri as s', 's.biodata_id', '=', 'b.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->where('kh.status', true)
            ->where(fn($q) => $q->whereNull('b.deleted_at')
                ->whereNull('s.deleted_at')
                ->whereNull('kh.deleted_at'))
            ->select(
                'b.id as biodata_id',
                'kh.id',
                'wp.niup',
                DB::raw("COALESCE(b.nik, b.no_passport) as identitas"),
                'b.nama',
                'kh.keterangan',
                'kh.created_at',
                'kh.updated_at',
                DB::raw("COALESCE(br.file_path, 'default.jpg') as foto_profil")
            )
            ->latest();
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            "biodata_id" => $item->biodata_id,
            "id_khadam" => $item->id,
            "niup" => $item->niup ?? '-',
            "nik" => $item->identitas ?? '-',
            "nama" => $item->nama,
            "keterangan" => $item->keterangan,
            "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            "foto_profil" => url($item->foto_profil)
        ]);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();
            $now    = now();

            // Biodata
            $nik = $data['nik'] ?? null;
            $existingBiodata = $nik ? DB::table('biodata')->where('nik', $nik)->first() : null;

            if ($existingBiodata) {
                // Cek apakah sudah ada khadam aktif
                $khadamAktif = DB::table('khadam')
                    ->where('biodata_id', $existingBiodata->id)
                    ->where('status', true)
                    ->whereNull('deleted_at')
                    ->first();

                if ($khadamAktif) {
                    throw ValidationException::withMessages([
                        'khadam' => ['Biodata ini sudah memiliki status khadam yang aktif.'],
                    ]);
                }
            }

            // Data biodata
            $biodataData = [
                'nama'                         => $data['nama'],
                'negara_id'                    => $data['negara_id'],
                'provinsi_id'                  => $data['provinsi_id'] ?? null,
                'kabupaten_id'                 => $data['kabupaten_id'] ?? null,
                'kecamatan_id'                 => $data['kecamatan_id'] ?? null,
                'jalan'                        => $data['jalan'] ?? null,
                'kode_pos'                     => $data['kode_pos'] ?? null,
                'no_passport'                  => $data['no_passport'] ?? null,
                'jenis_kelamin'                => $data['jenis_kelamin'],
                'tanggal_lahir'                => $data['tanggal_lahir'],
                'tempat_lahir'                 => $data['tempat_lahir'],
                'nik'                          => $nik,
                'no_telepon'                   => $data['no_telepon'],
                'no_telepon_2'                 => $data['no_telepon_2'] ?? null,
                'email'                        => $data['email'],
                'jenjang_pendidikan_terakhir'  => $data['jenjang_pendidikan_terakhir'] ?? null,
                'nama_pendidikan_terakhir'     => $data['nama_pendidikan_terakhir'] ?? null,
                'anak_keberapa'                => $data['anak_keberapa'] ?? null,
                'dari_saudara'                 => $data['dari_saudara'] ?? null,
                'tinggal_bersama'              => $data['tinggal_bersama'] ?? null,
                'updated_by'                   => $userId,
                'updated_at'                   => $now,
            ];

            if ($existingBiodata) {
                DB::table('biodata')->where('id', $existingBiodata->id)->update($biodataData);
                $biodataId = $existingBiodata->id;
            } else {
                do {
                    $smartcard = 'SC-' . strtoupper(Str::random(10));
                } while (DB::table('biodata')->where('smartcard', $smartcard)->exists());

                do {
                    $biodataId = Str::uuid()->toString();
                } while (DB::table('biodata')->where('id', $biodataId)->exists());

                DB::table('biodata')->insert(array_merge($biodataData, [
                    'id'         => $biodataId,
                    'smartcard'  => $smartcard,
                    'status'     => true,
                    'created_by' => $userId,
                    'created_at' => $now,
                ]));
            }

            // Insert ke tabel khadam
            $khadamId = DB::table('khadam')->insertGetId([
                'biodata_id'    => $biodataId,
                'keterangan'    => $data['keterangan'],
                'tanggal_mulai' => $data['tanggal_mulai'],
                'status'        => true,
                'created_by'    => $userId,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            // Upload Berkas (jika ada)
            if (!empty($data['berkas']) && is_array($data['berkas'])) {
                foreach ($data['berkas'] as $item) {
                    if (!($item['file_path'] instanceof UploadedFile)) {
                        throw new \Exception('Berkas tidak valid');
                    }

                    $url = Storage::url($item['file_path']->store('Khadam', 'public'));
                    DB::table('berkas')->insert([
                        'biodata_id'      => $biodataId,
                        'jenis_berkas_id' => (int) $item['jenis_berkas_id'],
                        'file_path'       => $url,
                        'status'          => true,
                        'created_by'      => $userId,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ]);
                }
            }

            activity('khadam_registration')
                ->causedBy(Auth::user())
                ->performedOn(Khadam::find($khadamId))
                ->withProperties([
                    'khadam_id'     => $khadamId,
                    'biodata_id'    => $biodataId,
                    'berkas'        => collect($data['berkas'] ?? [])->pluck('jenis_berkas_id'),
                    'ip'            => request()->ip(),
                    'user_agent'    => request()->userAgent(),
                ])
                ->event('create_khadam')
                ->log('Pendaftaran khadam baru berhasil disimpan.');

            return [
                'khadam_id'     => $khadamId,
                'biodata_id'    => $biodataId,
            ];
        });
    }
}
