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

class AlumniExport implements
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
        // 1) Sub‐query: tanggal_keluar riwayat_pendidikan alumni terakhir per santri
        $riwayatLast = DB::table('riwayat_pendidikan')
            ->select('santri_id', DB::raw('MAX(tanggal_keluar) AS max_tanggal_keluar'))
            ->where('status', 'alumni')
            ->groupBy('santri_id');

        // 2) Sub‐query: santri alumni terakhir
        $santriLast = DB::table('santri')
            ->select('id', DB::raw('MAX(id) AS last_id'))
            ->where('status', 'alumni')
            ->groupBy('id');

        // 5) Subquery: warga_pesantren terakhir per biodata (status = true)
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        return DB::table('santri as s')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftJoinSub($riwayatLast, 'lr', fn($j) => $j->on('lr.santri_id', '=', 's.id'))
            ->leftjoin('riwayat_pendidikan as rp', fn($j) => $j->on('rp.santri_id', '=', 'lr.santri_id')->on('rp.tanggal_keluar', '=', 'lr.max_tanggal_keluar'))
            ->leftJoin('lembaga as l', 'rp.lembaga_id', '=', 'l.id')
            ->leftJoinSub($santriLast, 'ld', fn($j) => $j->on('ld.id', '=', 's.id'))
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
            ->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
            ->where('s.status', 'alumni')
            // ->where(fn($q) => $q->where('s.status', 'alumni')->orWhere('rp.status', 'alumni'))
            ->select([
                'b.nama as nama_lengkap',
                DB::raw("COALESCE(b.nik, b.no_passport) AS nik"),
                's.nis',
                'wp.niup',
                DB::raw("CASE b.jenis_kelamin WHEN 'l' THEN 'Laki-laki' WHEN 'p' THEN 'Perempuan' ELSE b.jenis_kelamin END as jenis_kelamin"),
                DB::raw("CONCAT(b.jalan, ', ', kc.nama_kecamatan, ', ', kb.nama_kabupaten, ', ', pv.nama_provinsi) as alamat"),
                DB::raw("CONCAT(b.tempat_lahir, ', ', b.tanggal_lahir) as TTL"),
                'l.nama_lembaga as pendidikan',
                DB::raw('YEAR(rp.tanggal_keluar) as tahun_lulus'),
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
            $row->pendidikan,
            $row->tahun_lulus,
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
            'Lembaga Pendidikan',
            'Tahun Lulus',
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
