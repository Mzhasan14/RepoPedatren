<?php

namespace App\Exports\Pegawai;

use App\Models\Pegawai\Pegawai;
use Carbon\Carbon;
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
        DB::statement("SET SESSION group_concat_max_len = 1000000;");

        $pasFotoId = DB::table('jenis_berkas')->where('nama_jenis_berkas', 'Pas foto')->value('id');

        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        $pengajarAktif = DB::table('pengajar')
            ->select('pegawai_id', DB::raw('MAX(id) as id'))
            ->where('status_aktif', 'aktif')
            ->whereNull('deleted_at')
            ->groupBy('pegawai_id');

        $karyawanAktif = DB::table('karyawan')
            ->select('pegawai_id', DB::raw('MAX(id) as id'))
            ->where('status_aktif', 'aktif')
            ->whereNull('deleted_at')
            ->groupBy('pegawai_id');

        $pengurusAktif = DB::table('pengurus')
            ->select('pegawai_id', DB::raw('MAX(id) as id'))
            ->where('status_aktif', 'aktif')
            ->whereNull('deleted_at')
            ->groupBy('pegawai_id');

        return Pegawai::active()
            ->join('biodata as b', 'b.id', 'pegawai.biodata_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoinSub($pengajarAktif, 'pa', fn($j) => $j->on('pegawai.id', '=', 'pa.pegawai_id'))
            ->leftJoin('pengajar', 'pengajar.id', '=', 'pa.id')
            ->leftJoinSub($karyawanAktif, 'ka', fn($j) => $j->on('pegawai.id', '=', 'ka.pegawai_id'))
            ->leftJoin('karyawan', 'karyawan.id', '=', 'ka.id')
            ->leftJoinSub($pengurusAktif, 'pg', fn($j) => $j->on('pegawai.id', '=', 'pg.pegawai_id'))
            ->leftJoin('pengurus', 'pengurus.id', '=', 'pg.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoin('kecamatan as kec', 'kec.id', 'b.kecamatan_id')
            ->leftJoin('kabupaten as kab', 'kab.id', 'b.kabupaten_id')
            ->leftJoin('provinsi as prov', 'prov.id', 'b.provinsi_id')
            ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
            ->whereNull('pegawai.deleted_at')
            ->select(
                'b.nama as nama_lengkap',
                DB::raw("COALESCE(b.nik, b.no_passport) AS nik"),
                'k.no_kk',
                DB::raw("COALESCE(wp.niup, '-') AS niup"),
                DB::raw("CASE b.jenis_kelamin WHEN 'l' THEN 'Laki-laki' WHEN 'p' THEN 'Perempuan' ELSE b.jenis_kelamin END as jenis_kelamin"),
                'b.jalan',
                DB::raw("COALESCE(kec.nama_kecamatan, b.kecamatan_id) as kecamatan"),
                DB::raw("COALESCE(kab.nama_kabupaten, b.kabupaten_id) as kabupaten"),
                DB::raw("COALESCE(prov.nama_provinsi, b.provinsi_id) as provinsi"),
                'b.tempat_lahir',
                'b.tanggal_lahir',
                'b.jenjang_pendidikan_terakhir',
                'b.no_telepon',
                'b.email',
                DB::raw("
                    CASE 
                        WHEN pengajar.id IS NOT NULL THEN 'Pengajar'
                        WHEN karyawan.id IS NOT NULL THEN 'Karyawan'
                        WHEN pengurus.id IS NOT NULL THEN 'Pengurus'
                        ELSE '-'
                    END as status_aktif
                "),
                'pegawai.created_at'
            )
            ->groupBy(
                'pegawai.id',
                'b.id',
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
                'b.jenjang_pendidikan_terakhir',
                'b.no_telepon',
                'b.email',
                'pegawai.created_at',
                'pengajar.id',
                'karyawan.id',
                'pengurus.id',
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
            $row->niup,
            $row->jenis_kelamin,
            $row->jalan,
            $row->kecamatan,
            $row->kabupaten,
            $row->provinsi,
            $row->tempat_lahir,
            Carbon::parse($row->tanggal_lahir)->format('d-m-Y'),
            $row->jenjang_pendidikan_terakhir,
            $row->email,
            $row->no_telepon,
            $row->status_aktif,
            Carbon::parse($row->created_at)->format('d-m-Y H:i'),
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
            'Status Kepegawaian',
            'Tanggal Buat Pegawai',
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