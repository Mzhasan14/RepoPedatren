<?php

namespace Database\Factories;

use App\Models\Perizinan;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Perizinan>
 */
class PerizinanFactory extends Factory
{
    protected $model = Perizinan::class;

    public function definition(): array
    {
        $now = Carbon::now();

        // 1. Generate tanggal_mulai acak antara 1 bulan lalu sampai hari ini
        $mulai = Carbon::instance(
            $this->faker->dateTimeBetween('-1 month', 'now')
        );

        // 2. tanggal_akhir antara 1–14 hari setelah tanggal_mulai
        $akhir = $mulai->copy()->addDays(rand(1, 14));

        // 3. Tentukan tanggal_kembali awal
        if ($this->faker->boolean(70)) {
            $diffDays = $akhir->diffInDays($mulai);
            $kembali = $mulai->copy()->addDays(rand(0, $diffDays));
        } else {
            $kembali = $akhir->copy()->addDays(rand(1, 7));
        }

        // 4. Tentukan status awal
        if ($now->lt($mulai)) {
            $status = 'sedang proses izin';
        } elseif ($this->faker->boolean(5)) {
            $status = 'perizinan ditolak';
        } elseif ($this->faker->boolean(5)) {
            $status = 'dibatalkan';
        } elseif ($now->lte($akhir)) {
            $status = 'sudah berada diluar pondok';
        } else {
            $status = 'perizinan diterima';
        }

        // 5. Override status jika sudah kembali
        if ($now->gte($kembali)) {
            if ($kembali->lte($akhir)) {
                $status = 'kembali tepat waktu';
            } elseif ($kembali->gt($akhir)) {
                $status = 'telat';
            }
        } elseif ($now->gt($akhir)) {
            $status = 'telat';
        }

        // 6. Kalau status "sudah berada diluar pondok", kosongkan tanggal_kembali
        if ($status === 'sudah berada diluar pondok') {
            $kembali = null;
        }

        // 7. Tentukan approval flags
        $approved_by_biktren = false;
        $approved_by_kamtib = false;
        $approved_by_pengasuh = false;

        $approvedStatuses = [
            'perizinan diterima',
            'sudah berada diluar pondok',
            'telat',
            'kembali tepat waktu',
        ];

        if (in_array($status, $approvedStatuses)) {
            $approved_by_biktren = true;
            $approved_by_kamtib = true;
            $approved_by_pengasuh = true;
        }

        if ($status === 'sedang proses izin') {
            $approved_by_biktren = $this->faker->boolean(50);
            $approved_by_kamtib = $this->faker->boolean(50);
            $approved_by_pengasuh = $this->faker->boolean(50);
        }

        // 8. Array alasan izin umum di pesantren
        $alasanIzinList = [
            'Menjenguk orang tua yang sakit',
            'Menghadiri pernikahan saudara',
            'Mengurus dokumen kependudukan',
            'Menghadiri pemakaman keluarga',
            'Mengikuti lomba di luar pesantren',
            'Menghadiri wisuda saudara',
            'Kontrol kesehatan di rumah sakit',
            'Menghadiri acara keluarga besar',
            'Menemani orang tua ke rumah sakit',
            'Mengikuti kegiatan organisasi di luar',
        ];

        // 9. Array keterangan izin realistis
        $keteranganIzinList = [
            'Santri berangkat bersama keluarga pada pagi hari',
            'Wali santri menjemput langsung di gerbang utama',
            'Santri membawa surat izin resmi dari pengurus',
            'Akan kembali sebelum batas waktu yang ditentukan',
            'Santri dijemput oleh saudara kandung sesuai data wali',
            'Perjalanan dilakukan dengan transportasi umum',
            'Sudah mendapatkan izin lisan dari pengasuh',
            'Santri dijemput menggunakan kendaraan pondok',
            'Kegiatan di luar bersifat mendesak dan penting',
            'Wali santri sudah memberikan nomor kontak darurat',
        ];

        return [
            'santri_id' => Santri::inRandomOrder()->first()->id ?? Santri::factory(),
            'biktren_id' => optional(User::role('biktren')->inRandomOrder()->first())->id,
            'pengasuh_id' => optional(User::role('pengasuh')->inRandomOrder()->first())->id,
            'kamtib_id' => optional(User::role('kamtib')->inRandomOrder()->first())->id,
            'alasan_izin' => $this->faker->randomElement($alasanIzinList),
            'alamat_tujuan' => $this->faker->address,
            'tanggal_mulai' => $mulai,
            'tanggal_akhir' => $akhir,
            'tanggal_kembali' => $kembali,
            'jenis_izin' => $this->faker->randomElement(['Personal', 'Rombongan']),
            'status' => $status,
            'approved_by_biktren' => $approved_by_biktren,
            'approved_by_kamtib' => $approved_by_kamtib,
            'approved_by_pengasuh' => $approved_by_pengasuh,
            'keterangan' => $this->faker->randomElement($keteranganIzinList),
            'created_by' => optional(User::role('admin')->inRandomOrder()->first())->id,
            'updated_by' => null,
        ];
    }
}
