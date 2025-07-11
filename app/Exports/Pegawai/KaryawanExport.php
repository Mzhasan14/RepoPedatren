<?php

namespace App\Exports\Pegawai;

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

class KaryawanExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithStyles
{
    protected $index = 1;

    public function collection()
    {
        // Ambil ID jenis berkas "Pas foto"
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        // Subquery: foto terakhir
        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        // Subquery: warga pesantren terakhir
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        // Subquery: keluarga terakhir (KK)
        $kkLast = DB::table('keluarga')
            ->select('id_biodata', DB::raw('MAX(id) AS last_id'))
            ->groupBy('id_biodata');

        return DB::table('karyawan')
            ->join('pegawai', function ($join) {
                $join->on('pegawai.id', '=', 'karyawan.pegawai_id')
                    ->where('pegawai.status_aktif', 'aktif')
                    ->whereNull('pegawai.deleted_at');
            })
            ->join('biodata as b', 'b.id', '=', 'pegawai.biodata_id')
            ->leftJoinSub($fotoLast, 'fl', fn ($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas as br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn ($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren as wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoinSub($kkLast, 'kk_sub', fn ($j) => $j->on('kk_sub.id_biodata', '=', 'b.id'))
            ->leftJoin('keluarga as kk', 'kk.id', '=', 'kk_sub.last_id')
            ->leftJoin('golongan_jabatan as g', function ($join) {
                $join->on('karyawan.golongan_jabatan_id', '=', 'g.id')
                    ->where('g.status', true);
            })
            ->leftJoin('lembaga as l', 'l.id', '=', 'karyawan.lembaga_id')
            ->leftJoin('kecamatan as kec', 'kec.id', '=', 'b.kecamatan_id')
            ->leftJoin('kabupaten as kab', 'kab.id', '=', 'b.kabupaten_id')
            ->leftJoin('provinsi as prov', 'prov.id', '=', 'b.provinsi_id')
            ->select(
                'b.nama as nama_lengkap',
                DB::raw('COALESCE(b.nik, b.no_passport) AS nik'),
                DB::raw("COALESCE(kk.no_kk, '-') AS no_kk"),
                DB::raw("COALESCE(wp.niup, '-') AS niup"),
                DB::raw("CASE b.jenis_kelamin WHEN 'l' THEN 'Laki-laki' WHEN 'p' THEN 'Perempuan' ELSE b.jenis_kelamin END as jenis_kelamin"),
                'b.jalan',
                DB::raw("COALESCE(kec.nama_kecamatan, '-') AS kecamatan"),
                DB::raw("COALESCE(kab.nama_kabupaten, '-') AS kabupaten"),
                DB::raw("COALESCE(prov.nama_provinsi, '-') AS provinsi"),
                'b.tempat_lahir',
                DB::raw("DATE_FORMAT(b.tanggal_lahir, '%d-%m-%Y') as tanggal_lahir"),
                DB::raw("COALESCE(b.jenjang_pendidikan_terakhir, '-') AS pendidikan_terakhir"),
                DB::raw("COALESCE(b.email, '-') AS email"),
                DB::raw("COALESCE(b.no_telepon, '-') AS no_telepon"),
                DB::raw("COALESCE(l.nama_lembaga, '-') AS lembaga"),
                DB::raw("COALESCE(g.nama_golongan_jabatan, '-') AS golongan_jabatan"),
                DB::raw("COALESCE(karyawan.jabatan, '-') AS jabatan"),
                DB::raw("COALESCE(karyawan.keterangan_jabatan, '-') AS keterangan_jabatan"),
                DB::raw("DATE_FORMAT(karyawan.tanggal_mulai, '%d-%m-%Y') as tanggal_mulai"),
                DB::raw("DATE_FORMAT(karyawan.tanggal_selesai, '%d-%m-%Y') as tanggal_selesai"),
                DB::raw("CASE WHEN pegawai.status_aktif = 'aktif' THEN 'Aktif' ELSE 'Nonaktif' END AS status_aktif")
            )
            ->whereNull('karyawan.deleted_at')
            ->distinct() // Mencegah duplikat
            ->get();
    }

    public function map($row): array
    {
        return [
            $this->index++,
            $row->nama_lengkap,
            $row->nik,
            $row->no_kk,
            $row->niup,
            $row->jenis_kelamin,
            $row->jalan,
            $row->kecamatan,
            $row->kabupaten,
            $row->provinsi,
            $row->tempat_lahir,
            $row->tanggal_lahir,
            $row->pendidikan_terakhir,
            $row->email,
            $row->no_telepon,
            $row->lembaga,
            $row->golongan_jabatan,
            $row->jabatan,
            $row->keterangan_jabatan,
            $row->tanggal_mulai,
            $row->tanggal_selesai,
            $row->status_aktif,
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
