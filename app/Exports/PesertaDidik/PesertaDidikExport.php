<?php

namespace App\Exports\PesertaDidik;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\AfterSheet;

// class PesertaDidikExport implements
//     WithHeadings,
//     WithStyles,
//     WithEvents,
//     ShouldAutoSize
// {
//     protected array $data;

//     public function __construct(array $data)
//     {
//         $this->data = $data;
//     }

//     public function array(): array
//     {
//         return $this->data;
//     }

//     public function headings(): array
//     {
//         return [
//             'No',
//             'Nama Lengkap',
//             'NIK / Passport',
//             'NIS',
//             'NIUP',
//             'Jenis Kelamin',
//             'Alamat Lengkap',
//             'Tempat, Tanggal Lahir',
//             'Domisili',
//             'Lembaga Pendidikan',
//             'Angkatan Santri',
//             'Angkatan Pelajar',
//         ];
//     }

//     public function styles(Worksheet $sheet)
//     {
//         return [
//             1 => [
//                 'font' => ['bold' => true],
//                 'alignment' => ['horizontal' => 'center'],
//                 'fill' => [
//                     'fillType' => Fill::FILL_SOLID,
//                     'startColor' => ['argb' => 'FFD9D9D9'],
//                 ],
//             ],
//         ];
//     }

//     public function registerEvents(): array
//     {
//         return [
//             AfterSheet::class => function (AfterSheet $event) {
//                 $sheet = $event->sheet->getDelegate();
//                 $highestRow = $sheet->getHighestRow();
//                 $highestColumn = $sheet->getHighestColumn();

//                 // 1. Format kolom NIK dan NIUP sebagai teks agar tidak E+
//                 foreach (range(2, $highestRow) as $row) {
//                     // NIK (kolom C)
//                     $nik = $sheet->getCell("C{$row}")->getValue();
//                     $sheet->setCellValueExplicit("C{$row}", $nik, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

//                     // NIUP (kolom E)
//                     $niup = $sheet->getCell("E{$row}")->getValue();
//                     $sheet->setCellValueExplicit("E{$row}", $niup, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
//                 }

//                 // 2. Rata kiri semua isi data (baris 2 ke bawah)
//                 $sheet->getStyle("A2:{$highestColumn}{$highestRow}")
//                     ->getAlignment()
//                     ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

//                 // 3. Header bold, tengah, abu-abu
//                 $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
//                     'font' => ['bold' => true],
//                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
//                     'fill' => [
//                         'fillType' => Fill::FILL_SOLID,
//                         'startColor' => ['argb' => 'FFD9D9D9'],
//                     ],
//                 ]);

//                 // 4. Border kotak semua
//                 $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
//                     ->getBorders()
//                     ->getAllBorders()
//                     ->setBorderStyle(Border::BORDER_THIN);

//                 // 5. Freeze header
//                 $sheet->freezePane('A2');
//             },
//         ];
//     }
// }
class PesertaDidikExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithEvents,
    ShouldAutoSize
{
    protected $index = 1;

    public function collection()
    {
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        return DB::table('santri AS s')
            ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
            ->leftjoin('riwayat_pendidikan AS rp', fn($j) => $j->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
            ->leftjoin('riwayat_domisili AS rd', fn($join) => $join->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
            ->leftJoin('wilayah as w', 'rd.wilayah_id', '=', 'w.id')
            ->leftJoin('blok as bl', 'rd.blok_id', '=', 'bl.id')
            ->leftJoin('kamar as km', 'rd.kamar_id', '=', 'km.id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
            ->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
            ->where(fn($q) => $q->where('s.status', 'aktif')->orWhere('rp.status', '=', 'aktif'))
            ->where(fn($q) => $q->whereNull('b.deleted_at')->whereNull('s.deleted_at'))
            ->select([
                'b.nama as nama_lengkap',
                DB::raw("COALESCE(b.nik, b.no_passport) AS nik"),
                's.nis',
                'wp.niup',
                DB::raw("CASE b.jenis_kelamin WHEN 'l' THEN 'Laki-laki' WHEN 'p' THEN 'Perempuan' ELSE b.jenis_kelamin END as jenis_kelamin"),
                DB::raw("CONCAT(b.jalan, ', ', kc.nama_kecamatan, ', ', kb.nama_kabupaten, ', ', pv.nama_provinsi) as alamat"),
                DB::raw("CONCAT(b.tempat_lahir, ', ', b.tanggal_lahir) as TTL"),
                DB::raw("CONCAT(km.nama_kamar, ', ', bl.nama_blok, ', ', w.nama_wilayah) as domisili"),
                'l.nama_lembaga as pendidikan',
                DB::raw('YEAR(s.tanggal_masuk) as angkatan_santri'),
                DB::raw('YEAR(rp.tanggal_masuk) as angkatan_pelajar'),
            ])
            ->latest()
            ->get();
    }

    public function map($row): array
    {
        return [
            $this->index++,
            $row->nama_lengkap,
            $row->nik,
            $row->nis,
            $row->niup,
            $row->jenis_kelamin,
            $row->alamat,
            $row->TTL,
            $row->domisili,
            $row->pendidikan,
            $row->angkatan_santri,
            $row->angkatan_pelajar,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'NIK / Passport',
            'NIS',
            'NIUP',
            'Jenis Kelamin',
            'Alamat Lengkap',
            'Tempat, Tanggal Lahir',
            'Domisili',
            'Lembaga Pendidikan',
            'Angkatan Santri',
            'Angkatan Pelajar',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center'],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFD9D9D9'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // 1. Format kolom NIK dan NIUP sebagai teks agar tidak E+
                foreach (range(2, $highestRow) as $row) {
                    // NIK (kolom C)
                    $nik = $sheet->getCell("C{$row}")->getValue();
                    $sheet->setCellValueExplicit("C{$row}", $nik, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                    // NIUP (kolom E)
                    $niup = $sheet->getCell("E{$row}")->getValue();
                    $sheet->setCellValueExplicit("E{$row}", $niup, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }

                // 2. Rata kiri semua isi data (baris 2 ke bawah)
                $sheet->getStyle("A2:{$highestColumn}{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                // 3. Header bold, tengah, abu-abu
                $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFD9D9D9'],
                    ],
                ]);

                // 4. Border kotak semua
                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // 5. Freeze header
                $sheet->freezePane('A2');
            },
        ];
    }
}
