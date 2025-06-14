<?php

namespace App\Exports\Pegawai;

use App\Models\Pegawai\Pengajar;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PengajarExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithStyles
{
    protected $index = 1;

    public function collection()
    {
        return Pengajar::active()
            ->join('pegawai', function ($join) {
                $join->on('pegawai.id', '=', 'pengajar.pegawai_id')
                    ->where('pegawai.status_aktif', 'aktif')
                    ->whereNull('pegawai.deleted_at');
            })
            ->join('biodata as b', 'pegawai.biodata_id', '=', 'b.id')
            ->leftJoin('warga_pesantren as wp', function ($join) {
                $join->on('wp.biodata_id', '=', 'b.id')->where('wp.status', true);
            })
            ->leftJoin('lembaga as l', 'pengajar.lembaga_id', '=', 'l.id')
            ->leftJoin('golongan as g', 'pengajar.golongan_id', '=', 'g.id')
            ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
            ->leftJoin('kecamatan as kec', 'kec.id', '=', 'b.kecamatan_id')
            ->leftJoin('kabupaten as kab', 'kab.id', '=', 'b.kabupaten_id')
            ->leftJoin('provinsi as prov', 'prov.id', '=', 'b.provinsi_id')
            ->select(
                'b.nama as nama_lengkap',
                DB::raw('COALESCE(b.nik, b.no_passport) as nik'),
                'k.no_kk',
                DB::raw('COALESCE(wp.niup, "-") as niup'),
                DB::raw("CASE b.jenis_kelamin WHEN 'l' THEN 'Laki-laki' WHEN 'p' THEN 'Perempuan' ELSE '-' END AS jenis_kelamin"),
                'b.jalan',
                'kec.nama_kecamatan',
                'kab.nama_kabupaten',
                'prov.nama_provinsi',
                'b.tempat_lahir',
                DB::raw('DATE_FORMAT(b.tanggal_lahir, "%d-%m-%Y") as tanggal_lahir'),
                'b.jenjang_pendidikan_terakhir as pendidikan_terakhir',
                'b.email',
                'b.no_telepon as no_hp',
                'l.nama_lembaga',
                'g.nama_golongan',
                'pengajar.jabatan',
                DB::raw('DATE_FORMAT(pengajar.tahun_masuk, "%d-%m-%Y") as tanggal_mulai'),
                DB::raw('DATE_FORMAT(pengajar.tahun_akhir, "%d-%m-%Y") as tanggal_selesai'),
                DB::raw("IF(pengajar.status_aktif = 1, 'Aktif', 'Nonaktif') as status_aktif")
            )
            ->whereNull('pengajar.deleted_at')
            ->get();
    }

    public function map($row): array
    {
        return [
            $this->index++,
            $row->nama_lengkap,
            $row->nik,
            $row->no_kk ?? '-',
            $row->niup,
            $row->jenis_kelamin,
            $row->jalan ?? '-',
            $row->nama_kecamatan ?? '-',
            $row->nama_kabupaten ?? '-',
            $row->nama_provinsi ?? '-',
            $row->tempat_lahir ?? '-',
            $row->tanggal_lahir ?? '-',
            $row->pendidikan_terakhir ?? '-',
            $row->email ?? '-',
            $row->no_hp ?? '-',
            $row->nama_lembaga ?? '-',
            $row->nama_golongan ?? '-',
            $row->jabatan ?? '-',
            $row->tanggal_mulai ?? '-',
            $row->tanggal_selesai ?? '-',
            $row->status_aktif ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'NIK / Passport',
            'No KK',
            'NIUP',
            'Jenis Kelamin',
            'Jalan',
            'Kecamatan',
            'Kabupaten',
            'Provinsi',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Pendidikan Terakhir',
            'Email',
            'No Telepon',
            'Lembaga',
            'Golongan',
            'Jabatan',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Status Aktif',
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

                foreach (range(2, $highestRow) as $row) {
                    $sheet->setCellValueExplicit("C{$row}", $sheet->getCell("C{$row}")->getValue(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("D{$row}", $sheet->getCell("D{$row}")->getValue(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("E{$row}", $sheet->getCell("E{$row}")->getValue(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }

                $sheet->getStyle("A2:{$highestColumn}{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFD9D9D9'],
                    ],
                ]);

                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->freezePane('A2');
            },
        ];
    }
}
