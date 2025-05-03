<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Santri;
use App\Models\Perizinan;
use Illuminate\Support\Carbon;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\OrangTuaWali;
use Illuminate\Database\Eloquent\Factories\Factory;
use Database\Factories\Kewaliasuhan\Wali_asuhFactory;

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
        // 1. Generate tanggal_mulai acak antara 1 bulan lalu sampai hari ini
        $mulai = Carbon::instance(
            $this->faker->dateTimeBetween('-1 month', 'now')
        );

        // 2. tanggal_akhir antara 1–14 hari setelah tanggal_mulai
        $akhir = $mulai->copy()->addDays(rand(1, 14));

        // 3. Hitung lama izin (jumlah hari, inklusif)
        $lamaIzin = $akhir->diffInDays($mulai) + 1;

        // 4. Tentukan tanggal_kembali:
        //    – 70% on time: kembali antara tanggal_mulai dan tanggal_akhir
        //    – 30% late: kembali 1–7 hari setelah tanggal_akhir
        if ($this->faker->boolean(70)) {
            $diffDays = $akhir->diffInDays($mulai);
            $kembali  = $mulai->copy()->addDays(rand(0, $diffDays));
        } else {
            $kembali = $akhir->copy()->addDays(rand(1, 7));
        }

        // 5. Tentukan status gabungan sesuai enum di table 'status':
        //    ['sedang proses izin', 'perizinan diterima', 'sudah berada diluar pondok',
        //     'perizinan ditolak', 'dibatalkan',
        //     'telat', 'telat(sudah kembali)', 'telat(belum kembali)', 'kembali tepat waktu']
        $now = Carbon::now();

        // Pilih status izin dasar (termasuk kemungkinan ditolak atau dibatalkan)
        if ($now->lt($mulai)) {
            $status = 'sedang proses izin';
        } elseif ($this->faker->boolean(5)) {
            // 5% kemungkinan izin ditolak
            $status = 'perizinan ditolak';
        } elseif ($this->faker->boolean(5)) {
            // 5% kemungkinan izin dibatalkan
            $status = 'dibatalkan';
        } elseif ($now->lte($akhir)) {
            $status = 'sudah berada diluar pondok';
        } else {
            $status = 'perizinan diterima';
        }

        // Jika sudah melewati tanggal kembali, override dengan status kembali
        if ($now->gte($kembali)) {
            if ($kembali->lte($akhir)) {
                $status = 'kembali tepat waktu';
            } else {
                $status = 'telat(sudah kembali)';
            }
        } elseif ($now->gt($akhir)) {
            // Sudah lewat akhir tapi belum kembali
            $status = 'telat(belum kembali)';
        }

        return [
            'santri_id'       => Santri::inRandomOrder()->first()->id ?? Santri::factory(),
            'biktren_id'      => optional(User::role('biktren')->inRandomOrder()->first())->id,
            'pengasuh_id'     => optional(User::role('pengasuh')->inRandomOrder()->first())->id,
            'kamtib_id'       => optional(User::role('kamtib')->inRandomOrder()->first())->id,
            'pengantar_id'    => optional(OrangTuaWali::inRandomOrder()->first())->id,
            'alasan_izin'     => $this->faker->sentence,
            'alamat_tujuan'   => $this->faker->address,
            'tanggal_mulai'   => $mulai,
            'tanggal_akhir'   => $akhir,
            'lama_izin'       => $lamaIzin,
            'tanggal_kembali' => $kembali,
            'jenis_izin'      => $this->faker->randomElement(['Personal', 'Rombongan']),
            'status'          => $status,
            'keterangan'      => $this->faker->sentence,
            'created_by'      => optional(User::role('admin')->inRandomOrder()->first())->id,
            'updated_by'      => null,
        ];
    }
}

