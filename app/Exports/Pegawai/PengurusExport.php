<?php

namespace App\Exports\Pegawai;

use App\Models\Pegawai\Pengurus;
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

class PengurusExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, ShouldAutoSize
{
    protected $index = 1;

    public function collection()
    {
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        return Pengurus::active()
            ->leftJoin('golongan_jabatan as g', function ($join) {
                $join->on('pengurus.golongan_jabatan_id', '=', 'g.id')->where('g.status', true);
            })
            ->join('pegawai', function ($join) {
                $join->on('pengurus.pegawai_id', '=', 'pegawai.id')
                    ->where('pegawai.status_aktif', 'aktif')
                    ->whereNull('pegawai.deleted_at');
            })
            ->join('biodata as b', 'pegawai.biodata_id', '=', 'b.id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren as wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas as br', 'br.id', '=', 'fl.last_id')
            ->leftJoin('kecamatan as kec', 'kec.id', 'b.kecamatan_id')
            ->leftJoin('kabupaten as kab', 'kab.id', 'b.kabupaten_id')
            ->leftJoin('provinsi as prov', 'prov.id', 'b.provinsi_id')
            ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
            ->select(
                'b.nama as nama_lengkap',
                DB::raw("COALESCE(b.nik, b.no_passport) AS nik"),
                'k.no_kk',
                DB::raw("COALESCE(wp.niup, '-') AS niup"),
                DB::raw("CASE b.jenis_kelamin WHEN 'l' THEN 'Laki-laki' WHEN 'p' THEN 'Perempuan' ELSE '-' END as jenis_kelamin"),
                'b.jalan',
                'kec.nama_kecamatan',
                'kab.nama_kabupaten',
                'prov.nama_provinsi',
                'b.tempat_lahir',
                DB::raw("DATE_FORMAT(b.tanggal_lahir, '%d-%m-%Y') as tanggal_lahir"),
                'b.jenjang_pendidikan_terakhir',
                'b.email',
                'b.no_telepon as no_hp',
                'pengurus.satuan_kerja',
                'g.nama_golongan_jabatan',
                'pengurus.jabatan',
                'pengurus.keterangan_jabatan',
                DB::raw("DATE_FORMAT(pengurus.tanggal_mulai, '%d-%m-%Y') as tanggal_mulai"),
                DB::raw("DATE_FORMAT(pengurus.tanggal_akhir, '%d-%m-%Y') as tanggal_selesai"),
                DB::raw("IF(pengurus.status_aktif = 1, 'Aktif', 'Nonaktif') as status_aktif")
            )
            ->whereNull('pengurus.deleted_at')
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
            $row->jenjang_pendidikan_terakhir ?? '-',
            $row->email ?? '-',
            $row->no_hp ?? '-',
            $row->satuan_kerja ?? '-',
            $row->nama_golongan_jabatan ?? '-',
            $row->jabatan ?? '-',
            $row->keterangan_jabatan ?? '-',
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
            'Satuan Kerja',
            'Golongan Jabatan',
            'Jabatan',
            'Keterangan Jabatan',
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