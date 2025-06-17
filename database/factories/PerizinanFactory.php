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

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $now = Carbon::now();

        // 1. Generate tanggal_mulai acak antara 1 bulan lalu sampai hari ini
        $mulai = Carbon::instance(
            $this->faker->dateTimeBetween('-1 month', 'now')
        );

        // 2. tanggal_akhir antara 1–14 hari setelah tanggal_mulai
        $akhir = $mulai->copy()->addDays(rand(1, 14));

        // 3. Tentukan tanggal_kembali
        if ($this->faker->boolean(70)) {
            $diffDays = $akhir->diffInDays($mulai);
            $kembali = $mulai->copy()->addDays(rand(0, $diffDays));
        } else {
            $kembali = $akhir->copy()->addDays(rand(1, 7));
        }

        // 4. Tentukan status awal
        $status = 'sedang proses izin';

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
            // Telat dan belum kembali, tetap gunakan status 'telat'
            $status = 'telat';
        }

        // 6. Tentukan approval flags berdasarkan status
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

        // Jika status masih "sedang proses izin", random siapa saja yang sudah approve
        if ($status === 'sedang proses izin') {
            $approved_by_biktren = $this->faker->boolean(50);
            $approved_by_kamtib = $this->faker->boolean(50);
            $approved_by_pengasuh = $this->faker->boolean(50);
        }

        return [
            'santri_id' => Santri::inRandomOrder()->first()->id ?? Santri::factory(),
            'biktren_id' => optional(User::role('biktren')->inRandomOrder()->first())->id,
            'pengasuh_id' => optional(User::role('pengasuh')->inRandomOrder()->first())->id,
            'kamtib_id' => optional(User::role('kamtib')->inRandomOrder()->first())->id,
            'alasan_izin' => $this->faker->sentence,
            'alamat_tujuan' => $this->faker->address,
            'tanggal_mulai' => $mulai,
            'tanggal_akhir' => $akhir,
            'tanggal_kembali' => $kembali,
            'jenis_izin' => $this->faker->randomElement(['Personal', 'Rombongan']),
            'status' => $status,
            'approved_by_biktren' => $approved_by_biktren,
            'approved_by_kamtib' => $approved_by_kamtib,
            'approved_by_pengasuh' => $approved_by_pengasuh,
            'keterangan' => $this->faker->sentence,
            'created_by' => optional(User::role('admin')->inRandomOrder()->first())->id,
            'updated_by' => null,
        ];
    }
}
