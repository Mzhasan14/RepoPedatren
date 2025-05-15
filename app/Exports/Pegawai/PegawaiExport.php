<?php

namespace App\Exports\Pegawai;

use App\Models\Pegawai\Pegawai;
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

class PegawaiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, ShouldAutoSize
{
    protected $index = 1;

    public function collection()
    {
        // Tingkatkan limit GROUP_CONCAT
        DB::statement("SET SESSION group_concat_max_len = 1000000;");

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

        return Pegawai::Active()
            ->join('biodata as b', 'b.id', 'pegawai.biodata_id')
            // join warga pesantren terakhir true (NIUP)
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id') 
            // join pengajar yang hanya berstatus aktif                    
            ->leftJoin('pengajar', function($join) {
                $join->on('pengajar.pegawai_id', '=', 'pegawai.id')
                     ->where('pengajar.status_aktif', 'aktif')
                     ->whereNull('pengajar.deleted_at');
            })
            // join pengurus yang hanya berstatus aktif
            ->leftJoin('pengurus', function($join) {
                $join->on('pengurus.pegawai_id', '=', 'pegawai.id')
                     ->where('pengurus.status_aktif', 'aktif')
                     ->whereNull('pengurus.deleted_at');
            })
            // join karyawan yang hanya berstatus aktif
            ->leftJoin('karyawan', function($join) {
                $join->on('karyawan.pegawai_id', '=', 'pegawai.id')
                     ->where('karyawan.status_aktif', 'aktif')
                     ->whereNull('karyawan.deleted_at');
            })
            // join wali kelas yang hanya berstatus aktif
            ->leftJoin('wali_kelas', function($join) {
                $join->on('pegawai.id', '=', 'wali_kelas.pegawai_id')
                     ->where('wali_kelas.status_aktif', 'aktif')
                     ->whereNull('wali_kelas.deleted_at');
            })
            // join berkas pas foto terakhir
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            // join data wilayah untuk alamat lengkap
            ->leftJoin('kecamatan as kec', 'kec.id', 'b.kecamatan_id')
            ->leftJoin('kabupaten as kab', 'kab.id', 'b.kabupaten_id')
            ->leftJoin('provinsi as prov', 'prov.id', 'b.provinsi_id')
            // no kk
            ->leftJoin('keluarga as k','b.id','=','k.id_biodata')
            ->whereNull('pegawai.deleted_at')
            ->select(
                'b.nama as nama_lengkap',
                DB::raw("COALESCE(b.nik, b.no_passport) AS nik"),
                DB::raw("COALESCE(wp.niup, '-') AS niup"),
                DB::raw("CASE b.jenis_kelamin WHEN 'l' THEN 'Laki-laki' WHEN 'p' THEN 'Perempuan' ELSE b.jenis_kelamin END as jenis_kelamin"),
                DB::raw("CONCAT_WS(', ', 
                    b.jalan, 
                    COALESCE(kec.nama_kecamatan, b.kecamatan_id), 
                    COALESCE(kab.nama_kabupaten, b.kabupaten_id), 
                    COALESCE(prov.nama_provinsi, b.provinsi_id)
                ) as alamat"),
                 'k.no_kk',
                DB::raw("CONCAT(
                    b.tempat_lahir, 
                    ', ', 
                    DATE_FORMAT(b.tanggal_lahir, '%d-%m-%Y')
                ) as ttl"),
                DB::raw("TRIM(BOTH ', ' FROM CONCAT_WS(', ', 
                    GROUP_CONCAT(DISTINCT CASE WHEN pengajar.id IS NOT NULL THEN 'Pengajar' END SEPARATOR ', '),
                    GROUP_CONCAT(DISTINCT CASE WHEN karyawan.id IS NOT NULL THEN 'Karyawan' END SEPARATOR ', '),
                    GROUP_CONCAT(DISTINCT CASE WHEN pengurus.id IS NOT NULL THEN 'Pengurus' END SEPARATOR ', '),
                    GROUP_CONCAT(DISTINCT CASE WHEN wali_kelas.id IS NOT NULL THEN 'Wali Kelas' END SEPARATOR ', ')
                )) as status_aktif")
            )
            ->groupBy(
                'b.nama', 
                'b.nik', 
                'b.no_passport', 
                'wp.niup', 
                'k.no_kk',
                'b.jenis_kelamin', 
                'b.jalan', 
                'kec.nama_kecamatan', 
                'b.kecamatan_id', 
                'kab.nama_kabupaten', 
                'b.kabupaten_id', 
                'prov.nama_provinsi', 
                'b.provinsi_id', 
                'b.tempat_lahir', 
                'b.tanggal_lahir'
            )
            ->get();
    }

    public function map($row): array
    {
        return [
            $this->index++,
            $row->nama_lengkap,
            $row->nik,
            $row->no_kk,
            $row->niup ?? '-',
            $row->jenis_kelamin,
            $row->alamat,
            $row->ttl,
            $row->status_aktif ?: '-',
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'NIK / Passport',
            'NIUP',
            'NO KK',
            'Jenis Kelamin',
            'Alamat Lengkap',
            'Tempat, Tanggal Lahir',
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