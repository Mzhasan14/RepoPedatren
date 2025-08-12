<?php

namespace Database\Seeders;

use App\Models\Kitab;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KitabSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nama_kitab' => 'Nadzom Aqidatul Awam',       'total_bait' => 57],
            ['nama_kitab' => 'Nadzom Imrithi',             'total_bait' => 254],
            ['nama_kitab' => 'Nadzom Alfiyah Ibnu Malik',  'total_bait' => 1002],
            ['nama_kitab' => 'Nadzom Bina',                'total_bait' => 50],
            ['nama_kitab' => 'Nadzom Tashrif',             'total_bait' => 60],
            ['nama_kitab' => 'Nadzom Tuhfatul Athfal',     'total_bait' => 61],
            ['nama_kitab' => 'Nadzom Jazariyah',           'total_bait' => 109],
        ];

        foreach ($data as $item) {
            Kitab::updateOrCreate(
                ['nama_kitab' => $item['nama_kitab']],
                [
                    'total_bait' => $item['total_bait'],
                    'created_by' => 1,
                ]
            );
        }
    }
}
