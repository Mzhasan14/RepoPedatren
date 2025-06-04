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
        $rpLast = DB::table('riwayat_pendidikan')
            ->select('biodata_id', DB::raw('MAX(tanggal_keluar) AS max_tanggal_keluar'))
            ->where('status', 'lulus')
            ->groupBy('biodata_id');

        $rdLast = DB::table('riwayat_domisili')
            ->select('santri_id', DB::raw('MAX(tanggal_keluar) AS max_tanggal_keluar'))
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

        return DB::table('biodata as b')
            ->leftjoin('santri as s', 's.biodata_id', '=', 'b.id')
            ->leftJoinSub($rdLast, 'lrd', fn($j) => $j->on('lrd.santri_id', '=', 'b.id'))
            ->leftjoin('riwayat_domisili as rd', fn($j) => $j->on('rd.santri_id', '=', 'lrd.santri_id')->on('rd.tanggal_keluar', '=', 'lrd.max_tanggal_keluar'))
            ->leftjoin('kamar AS km', 'rd.kamar_id', '=', 'km.id')
            ->leftjoin('blok AS bl', 'rd.blok_id', '=', 'bl.id')
            ->leftjoin('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
            ->leftjoin('angkatan AS as', 's.angkatan_id', '=', 'as.id')
            ->leftJoinSub($rpLast, 'lr', fn($j) => $j->on('lr.biodata_id', '=', 'b.id'))
            ->leftjoin('riwayat_pendidikan as rp', fn($j) => $j->on('rp.biodata_id', '=', 'lr.biodata_id')->on('rp.tanggal_keluar', '=', 'lr.max_tanggal_keluar'))
            ->leftjoin('angkatan AS ap', 'rp.angkatan_id', '=', 'ap.id')
            ->leftJoin('lembaga as l', 'rp.lembaga_id', '=', 'l.id')
            ->leftjoin('jurusan AS j', 'rp.jurusan_id', '=', 'j.id')
            ->leftjoin('kelas AS kls', 'rp.kelas_id', '=', 'kls.id')
            ->leftjoin('rombel AS r', 'rp.rombel_id', '=', 'r.id')
            ->leftJoinSub($santriLast, 'ld', fn($j) => $j->on('ld.id', '=', 's.id'))
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
            ->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
            ->leftJoin('negara as n', 'b.negara_id', '=', 'n.id')
            ->where(fn($q) => $q->where('s.status', 'alumni')
                ->orWhere('rp.status', 'lulus'))
            ->where(fn($q) => $q->whereNull('b.deleted_at')
                ->whereNull('s.deleted_at')
                ->whereNull('rp.deleted_at'))
            ->select([
                'b.nama as nama_lengkap',
                DB::raw("COALESCE(b.nik, b.no_passport) AS nik"),
                's.nis',
                'rp.no_induk',
                'wp.niup',
                DB::raw("CASE b.jenis_kelamin WHEN 'l' THEN 'Laki-laki' WHEN 'p' THEN 'Perempuan' ELSE b.jenis_kelamin END as jenis_kelamin"),
                'b.jalan',
                'kc.nama_kecamatan',
                'kb.nama_kabupaten',
                'pv.nama_provinsi',
                'n.nama_negara',
                DB::raw("CONCAT(b.tempat_lahir, ', ', b.tanggal_lahir) as TTL"),
                'km.nama_kamar',
                'bl.nama_blok',
                'w.nama_wilayah',
                'l.nama_lembaga',
                'j.nama_jurusan',
                'kls.nama_kelas',
                'r.nama_rombel',
                'as.angkatan as angkatan_santri',
                'ap.angkatan as angkatan_pelajar',
                DB::raw('YEAR(rp.tanggal_keluar) as tahun_lulus'),
            ])
            ->orderby('b.created_at', 'desc')
            ->get();
    }

    public function map($row): array
    {
        return [
            $this->index++,
            $row->nama_lengkap,
            $row->nik,
            $row->nis,
            $row->no_induk,
            $row->niup,
            $row->jenis_kelamin,
            $row->jalan,
            $row->nama_kecamatan,
            $row->nama_kabupaten,
            $row->nama_provinsi,
            $row->nama_negara,
            $row->TTL,
            $row->nama_kamar,
            $row->nama_blok,
            $row->nama_wilayah,
            $row->nama_lembaga,
            $row->nama_jurusan,
            $row->nama_kelas,
            $row->nama_rombel,
            $row->angkatan_santri,
            $row->angkatan_pelajar,
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
            'No Induk',
            'NIUP',
            'Jenis Kelamin',
            'Jalan',
            'Kecamatan',
            'Kabupaten',
            'Provinsi',
            'Negara',
            'Tempat, Tanggal Lahir',
            'Kamar',
            'Blok',
            'Wilayah',
            'Lembaga',
            'Jurusan',
            'Kelas',
            'Rombel',
            'Angkatan Santri',
            'Angkatan Pelajar',
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
