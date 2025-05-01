<?php

namespace App\Exports\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use App\Services\PesertaDidik\FilterPesertaDidikService;

class PesertaDidikExport implements
    FromQuery,
    WithMapping,
    WithHeadings,
    ShouldAutoSize,
    WithEvents,
    WithStyles,
    Responsable,
    WithCustomValueBinder,
    WithChunkReading
{
    use Exportable;

    private string $fileName = 'peserta_didik.xlsx';
    private Request $request;
    private FilterPesertaDidikService $filterService;
    private array $availableColumns;
    private array $selected;
    private int $counter = 0;

    public function __construct(Request $request, FilterPesertaDidikService $filterService)
    {
        $this->request       = $request;
        $this->filterService = $filterService;

        // Definisikan kolom dan ekspresi SQL-nya sebagai string
        $this->availableColumns = [
            // 'id'               => ['label' => 'Id',               'expr' => 's.id'],
            // 'nama'             => ['label' => 'Nama',             'expr' => 'b.nama'],
            'no_kk'            => ['label' => 'No KK',            'expr' => 'k.no_kk'],
            'identitas'        => ['label' => 'Identitas',        'expr' => 'COALESCE(b.nik, b.no_passport)'],
            'niup'             => ['label' => 'NIUP',             'expr' => 'wp.niup'],
            'anak_ke'          => ['label' => 'Anak Ke',          'expr' => 'b.anak_keberapa'],
            'jumlah_saudara'   => ['label' => 'Jumlah Saudara',   'expr' => 'COALESCE(siblings.jumlah_saudara, 0)'],
            'alamat'           => ['label' => 'Alamat',           'expr' => "CONCAT(b.jalan, ', ', kc.nama_kecamatan, ', ', kb.nama_kabupaten, ', ', pv.nama_provinsi)"],
            'domisili'         => ['label' => 'Domisili',         'expr' => "CONCAT(km.nama_kamar, ', ', bl.nama_blok, ', ', w.nama_wilayah)"],
            'angkatan_santri'  => ['label' => 'Angkatan Santri',  'expr' => 'YEAR(s.tanggal_masuk)'],
            'angkatan_pelajar' => ['label' => 'Angkatan Pelajar', 'expr' => 'YEAR(rp.tanggal_masuk)'],
            'status'           => ['label' => 'Status',           'expr' => "
                CASE
                    WHEN s.status = 'aktif' AND rp.status = 'aktif' THEN 'Santri Sekaligus Pelajar'
                    WHEN s.status != 'aktif' AND rp.status = 'aktif' THEN 'Pelajar'
                    WHEN s.status = 'aktif' AND (rp.status != 'aktif' OR rp.status IS NULL) THEN 'Santri'
                    ELSE 'Non-Aktif'
                END
            "],
            'ibu'              => ['label' => 'Ibu Kandung',      'expr' => 'parents.nama_ibu'],
        ];

        // Pilih kolom sesuai permintaan
        $cols = $request->input('columns', []);
        $this->selected = in_array('all', $cols)
            ? array_keys($this->availableColumns)
            : array_values(array_intersect(array_keys($this->availableColumns), $cols));
    }

    public function query()
    {
        // Subquery untuk pas foto terbaru
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $fotoSub = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) as last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        // Subquery untuk warga pesantren aktif terbaru
        $wpSub = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) as last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        // Subquery untuk nama ibu dan ayah per No KK
        $parents = DB::table('orang_tua_wali as otw')
            ->join('keluarga as k2', 'k2.id_biodata', '=', 'otw.id_biodata')
            ->join('biodata as b2', 'b2.id', '=', 'otw.id_biodata')
            ->join('hubungan_keluarga as hk', 'hk.id', '=', 'otw.id_hubungan_keluarga')
            ->select(
                'k2.no_kk',
                DB::raw("MAX(CASE WHEN hk.nama_status = 'ibu'  THEN b2.nama END) as nama_ibu"),
                DB::raw("MAX(CASE WHEN hk.nama_status = 'ayah' THEN b2.nama END) as nama_ayah")
            )
            ->groupBy('k2.no_kk');

        // Subquery untuk jumlah saudara (anak dalam keluarga) per No KK
        $siblings = DB::table('keluarga as k2')
            ->select(
                'k2.no_kk',
                DB::raw('CASE WHEN (COUNT(*) - 1) = 0 THEN 0 ELSE (COUNT(*) - 1) END as jumlah_saudara')
            )
            ->whereNotIn('k2.id_biodata', function ($q) {
                $q->select('id_biodata')->from('orang_tua_wali');
            })
            ->groupBy('k2.no_kk');

        // Query utama
        $query = DB::table('santri as s')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
            ->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
            ->leftJoin('keluarga as k', 'k.id_biodata', '=', 'b.id')
            ->leftJoinSub($parents,   'parents',  fn($join) => $join->on('k.no_kk', '=', 'parents.no_kk'))
            ->leftJoinSub($siblings,  'siblings', fn($join) => $join->on('k.no_kk', '=', 'siblings.no_kk'))
            ->leftJoinSub($fotoSub,   'fl',       fn($join) => $join->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas as br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpSub,     'wl',       fn($join) => $join->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren as wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('riwayat_pendidikan as rp', fn($join) =>
                $join->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif')
            )
            ->leftJoin('riwayat_domisili as rd', fn($join) =>
                $join->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif')
            )
            ->leftJoin('wilayah as w', 'rd.wilayah_id', '=', 'w.id')
            ->leftJoin('blok as bl',     'rd.blok_id',     '=', 'bl.id')
            ->leftJoin('kamar as km',    'rd.kamar_id',    '=', 'km.id')
            ->where(function ($q) {
                $q->where('s.status', 'aktif')
                  ->orWhere('rp.status', 'aktif');
            })
            ->orderBy('s.id');

        // Tambahkan SELECT sesuai kolom terpilih
        foreach ($this->selected as $key) {
            $expr = $this->availableColumns[$key]['expr'];
            $query->addSelect(DB::raw("{$expr} as {$key}"));
        }

        // Terapkan filter bisnis dan kembalikan query
        return $this->filterService->bersaudaraFilters($query, $this->request);
    }

    public function chunkSize(): int
    {
        return $this->request->input('chunk_size', 1000);
    }

    public function map($row): array
    {
        $this->counter++;
        $out = [$this->counter];
        foreach ($this->selected as $key) {
            $out[] = $row->{$key} ?? '';
        }
        return $out;
    }

    public function headings(): array
    {
        $heads = ['No'];
        foreach ($this->selected as $key) {
            $heads[] = $this->availableColumns[$key]['label'];
        }
        return $heads;
    }

    public function bindValue(Cell $cell, $value)
    {
        $cell->setValueExplicit((string) ($value ?? ''), DataType::TYPE_STRING);
        return true;
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $event->sheet->freezePane('A2');
                $lastRow = $this->counter + 1;
                $lastCol = $event->sheet->getDelegate()->getHighestColumn();
                $event->sheet->getDelegate()
                    ->getStyle("A1:{$lastCol}{$lastRow}")
                    ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            },
        ];
    }

    public function toResponse($request)
    {
        return $this->download($this->fileName, \Maatwebsite\Excel\Excel::XLSX);
    }
}



// namespace App\Exports;

// use Illuminate\Http\Request;
// use Maatwebsite\Excel\Excel;
// use Illuminate\Support\Facades\DB;
// use PhpOffice\PhpSpreadsheet\Cell\Cell;
// use Maatwebsite\Excel\Events\AfterSheet;
// use PhpOffice\PhpSpreadsheet\Style\Fill;
// use Maatwebsite\Excel\Concerns\FromQuery;
// use Maatwebsite\Excel\Concerns\WithEvents;
// use Maatwebsite\Excel\Concerns\WithStyles;
// use App\Services\FilterPesertaDidikService;
// use Maatwebsite\Excel\Concerns\WithMapping;
// use PhpOffice\PhpSpreadsheet\Cell\DataType;
// use Maatwebsite\Excel\Concerns\WithHeadings;
// use Illuminate\Contracts\Support\Responsable;
// use PhpOffice\PhpSpreadsheet\Style\Alignment;
// use Maatwebsite\Excel\Concerns\ShouldAutoSize;
// use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

// class PesertaDidikExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithEvents, WithStyles, Responsable, WithCustomValueBinder
// {
//     use \Maatwebsite\Excel\Concerns\Exportable;
//     private string $fileName = 'peserta_didik.xlsx';
//     private array $availableColumns;
//     private array $selected;

//     protected Request $request;
//     protected FilterPesertaDidikService $filterService;

//     // Pagination properties
//     private int $page;
//     private int $perPage;
//     private bool $exportAll;

//     private int $counter = 0;

//     public function __construct(Request $request, FilterPesertaDidikService $filterService)
//     {
//         $this->request       = $request;
//         $this->filterService = $filterService;

//         // Determine pagination or export all
//         $this->exportAll = $request->boolean('all_data', false);
//         $this->page      = (int) $request->input('page', 1);
//         $this->perPage   = (int) $request->input('per_page', 100);

//         $this->availableColumns = [
//             'no_kk'     => ['label' => 'No KK',      'expr' => DB::raw("k.no_kk as no_kk")],
//             'identitas' => ['label' => 'Identitas',  'expr' => DB::raw("COALESCE(b.nik,b.no_passport) as identitas")],
//             'nama'      => ['label' => 'Nama',       'expr' => DB::raw("b.nama as nama")],
//             'niup'      => ['label' => 'NIUP',       'expr' => DB::raw("wp.niup as niup")],
//             'lembaga'   => ['label' => 'Lembaga',    'expr' => DB::raw("l.nama_lembaga as lembaga")],
//             'wilayah'   => ['label' => 'Wilayah',    'expr' => DB::raw("w.nama_wilayah as wilayah")],
//             'kota_asal' => ['label' => 'Kota Asal',  'expr' => DB::raw("kb.nama_kabupaten as kota_asal")],
//         ];

//         // Columns selection: allow 'all' or specific
//         $cols = $request->input('columns', []);
//         $this->selected = in_array('all', $cols)
//             ? array_keys($this->availableColumns)
//             : array_intersect(array_keys($this->availableColumns), $cols);
//     }

//     /**
//      * Bangun query peserta didik dengan filter
//      */
//     public function query()
//     {
//         // join dan subquery sama seperti semula
//         $pasFotoId = DB::table('jenis_berkas')
//             ->where('nama_jenis_berkas', 'Pas foto')
//             ->value('id');

//         $fotoLast = DB::table('berkas')
//             ->select('biodata_id', DB::raw('MAX(id) as last_id'))
//             ->where('jenis_berkas_id', $pasFotoId)
//             ->groupBy('biodata_id');

//         $wpLast = DB::table('warga_pesantren')
//             ->select('biodata_id', DB::raw('MAX(id) as last_id'))
//             ->where('status', true)
//             ->groupBy('biodata_id');

//         // bangun select dinamis
//         $selects = [];
//         foreach ($this->selected as $key) {
//             $selects[] = $this->availableColumns[$key]['expr'];
//         }

//         // selalu tambahkan primary key untuk ordering / mapping
//         $selects[] = 's.id as __order';

//         $query = DB::table('santri as s')
//             ->join('biodata as b', 's.biodata_id', 'b.id')
//             ->leftjoin('keluarga as k', 'k.id_biodata', 'b.id')
//             ->leftJoin('riwayat_pendidikan as rp', fn($j) => $j->on('s.id', 'rp.santri_id')->where('rp.status', 'aktif'))
//             ->leftJoin('lembaga as l', 'rp.lembaga_id', 'l.id')
//             ->leftJoin('riwayat_domisili as rd', fn($j) => $j->on('s.id', 'rd.santri_id')->where('rd.status', 'aktif'))
//             ->leftJoin('wilayah as w', 'rd.wilayah_id', 'w.id')
//             ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', 'fl.biodata_id'))
//             ->leftJoin('berkas as br', 'br.id', 'fl.last_id')
//             ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', 'wl.biodata_id'))
//             ->leftJoin('warga_pesantren as wp', 'wp.id', 'wl.last_id')
//             ->leftJoin('kabupaten as kb', 'kb.id', 'b.kabupaten_id')
//             ->where(fn($q) => $q->where('s.status', 'aktif')->orWhere('rp.status', 'aktif'))
//             ->select($selects)
//             ->orderBy('s.id');

//          // apply filters
//          $filtered = $this->filterService->pesertaDidikFilters($query, $this->request);

//          // apply pagination if not export all
//          if (! $this->exportAll) {
//              $filtered = $filtered->forPage($this->page, $this->perPage);
//          }
 
//          return $filtered;
//     }

//     /**
//      * Mapping setiap baris ke format Excel
//      */
//     public function map($row): array
//     {
//         $this->counter++;
//         $out = [$this->counter];

//         foreach ($this->selected as $key) {
//             $out[] = $row->{$key} ?? '';
//         }

//         return $out;
//     }

//     /**
//      * Heading kolom di Excel
//      */
//     public function headings(): array
//     {
//         $heads = ['No'];
//         foreach ($this->selected as $key) {
//             $heads[] = $this->availableColumns[$key]['label'];
//         }
//         return $heads;
//     }

//     /**
//      * Override DefaultValueBinder supaya semua nilai di-set explicit sebagai STRING
//      */
//     public function bindValue(Cell $cell, $value)
//     {
//         $value = $value === null ? '' : $value;
//         $cell->setValueExplicit($value, DataType::TYPE_STRING);
//         return true;
//     }

//     /**
//      * Override default value binder supaya semua nilai di-set explicit sebagai STRING
//      */
//     public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
//     {
//         // styling header
//         $lastCol = chr(ord('A') + count($this->selected));
//         $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
//             'font'      => ['bold' => true],
//             'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
//             'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
//         ]);
//     }

//     /**
//      * Event AfterSheet untuk freeze header dan vertical align
//      */
//     public function registerEvents(): array
//     {
//         return [
//             AfterSheet::class => function ($e) {
//                 $e->sheet->freezePane('A2');
//                 $lastRow = $this->counter + 1;
//                 $lastCol = chr(ord('A') + count($this->selected));
//                 $e->sheet->getDelegate()
//                     ->getStyle("A1:{$lastCol}{$lastRow}")
//                     ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
//             },
//         ];
//     }

//     // untuk Responsable: method toResponse
//     public function toResponse($request)
//     {
//         return $this->download($this->fileName, Excel::XLSX);
//     }
// }
