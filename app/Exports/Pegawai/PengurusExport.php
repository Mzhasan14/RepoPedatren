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
        // 1) Ambil ID untuk jenis berkas "Pas foto"
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        // 2) Subquery: foto terakhir per biodata
        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        // 3) Subquery: warga pesantren terakhir per biodata
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        // 4) Query utama
        return Pengurus::Active()
            ->leftJoin('golongan_jabatan as g', function ($join) {
                $join->on('pengurus.golongan_jabatan_id', '=', 'g.id')
                    ->where('g.status', true);
            })
            ->join('pegawai', function ($join) {
                $join->on('pengurus.pegawai_id', '=', 'pegawai.id')
                    ->where('pegawai.status_aktif', 'aktif')
                    ->whereNull('pegawai.deleted_at');
            })
            ->join('biodata as b', 'pegawai.biodata_id', '=', 'b.id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            // join data wilayah untuk alamat lengkap
            ->leftJoin('kecamatan as kec', 'kec.id', 'b.kecamatan_id')
            ->leftJoin('kabupaten as kab', 'kab.id', 'b.kabupaten_id')
            ->leftJoin('provinsi as prov', 'prov.id', 'b.provinsi_id')
            // no kk
            ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
            ->whereNull('pengurus.deleted_at')
            ->select(
                'b.nama as nama_lengkap',
                DB::raw("COALESCE(b.nik, b.no_passport) AS nik"),
                'k.no_kk',
                DB::raw("COALESCE(wp.niup, '-') AS niup"),
                DB::raw("CASE b.jenis_kelamin WHEN 'l' THEN 'Laki-laki' WHEN 'p' THEN 'Perempuan' ELSE b.jenis_kelamin END as jenis_kelamin"),
                DB::raw("CONCAT_WS(', ', 
                    b.jalan, 
                    COALESCE(kec.nama_kecamatan, b.kecamatan_id), 
                    COALESCE(kab.nama_kabupaten, b.kabupaten_id), 
                    COALESCE(prov.nama_provinsi, b.provinsi_id)
                ) as alamat"),
                DB::raw("CONCAT(
                    b.tempat_lahir, 
                    ', ', 
                    DATE_FORMAT(b.tanggal_lahir, '%d-%m-%Y')
                ) as ttl"),
                'pengurus.satuan_kerja',
                'pengurus.jabatan as jenis_jabatan',
                'pengurus.keterangan_jabatan',
                'g.nama_golongan_jabatan',
                DB::raw("TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur"),
                'b.nama_pendidikan_terakhir'
            )
            ->groupBy(
                'b.nama',
                'b.nik',
                'b.no_passport',
                'k.no_kk',
                'wp.niup',
                'b.jenis_kelamin',
                'b.jalan',
                'kec.nama_kecamatan',
                'b.kecamatan_id',
                'kab.nama_kabupaten',
                'b.kabupaten_id',
                'prov.nama_provinsi',
                'b.provinsi_id',
                'b.tempat_lahir',
                'b.tanggal_lahir',
                'pengurus.satuan_kerja',
                'pengurus.jabatan',
                'pengurus.keterangan_jabatan',
                'g.nama_golongan_jabatan',
                'b.nama_pendidikan_terakhir'
            )
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
            $row->alamat,
            $row->ttl,
            $row->satuan_kerja ?? '-',
            $row->jenis_jabatan ?? '-',
            $row->keterangan_jabatan ?? '-',
            $row->nama_golongan_jabatan ?? '-',
            $row->umur,
            $row->nama_pendidikan_terakhir ?? '-',
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
            'Alamat Lengkap',
            'Tempat, Tanggal Lahir',
            'Satuan Kerja',
            'Jenis Jabatan',
            'Keterangan Jabatan',
            'Golongan Jabatan',
            'Umur',
            'Pendidikan Terakhir',
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