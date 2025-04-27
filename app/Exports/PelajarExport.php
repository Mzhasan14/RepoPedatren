<?php

namespace App\Exports;

use App\Services\FilterPelajarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use App\Services\FilterPesertaDidikService;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\Support\Responsable;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class PelajarExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithEvents, WithStyles, Responsable, WithCustomValueBinder
{
    use \Maatwebsite\Excel\Concerns\Exportable;

    private string $fileName = 'peserta_didik.xlsx';
    private array $availableColumns;
    private array $selected;

    protected Request $request;
    protected FilterPelajarService $filterService;
    private int $counter = 0;


    public function __construct(Request $request, FilterPelajarService $filterService)
    {
        $this->request       = $request;
        $this->filterService = $filterService;

        $this->availableColumns = [
            'no_kk'   => [
                'label' => 'No KK',
                'expr'  => DB::raw("k.no_kk as no_kk")
            ],
            'identitas'   => [
                'label' => 'Identitas',
                'expr'  => DB::raw("COALESCE(b.nik,b.no_passport) as identitas")
            ],
            'nama'        => [
                'label' => 'Nama',
                'expr'  => DB::raw("b.nama as nama")
            ],
            'niup'        => [
                'label' => 'NIUP',
                'expr'  => DB::raw("wp.niup as niup")
            ],
            'lembaga'     => [
                'label' => 'Lembaga',
                'expr'  => DB::raw("l.nama_lembaga as lembaga")
            ],
            'wilayah'     => [
                'label' => 'Wilayah',
                'expr'  => DB::raw("w.nama_wilayah as wilayah")
            ],
            'kota_asal'   => [
                'label' => 'Kota Asal',
                'expr'  => DB::raw("kb.nama_kabupaten as kota_asal")
            ],
        ];


        $cols = $request->input('columns', []);
        $this->selected = in_array('all', $cols)
            ? array_keys($this->availableColumns)
            : array_intersect(array_keys($this->availableColumns), $cols);
    }

    /**
     * Bangun query peserta didik dengan filter
     */
    public function query()
    {
        // join dan subquery sama seperti semula
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) as last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) as last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        // bangun select dinamis
        $selects = [];
        foreach ($this->selected as $key) {
            $selects[] = $this->availableColumns[$key]['expr'];
        }

        // selalu tambahkan primary key untuk ordering / mapping
        $selects[] = 's.id as __order';

        // Query utama: data peserta_didik all
        $query = DB::table('santri AS s')
        ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
        
        // wajib punya relasi riwayat pendidikan aktif
        ->join('riwayat_pendidikan AS rp', fn($j) => $j->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif'))
        ->leftJoin('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
        ->leftJoin('jurusan AS j', 'rp.jurusan_id', '=', 'j.id')
        ->leftJoin('kelas AS kls', 'rp.kelas_id', '=', 'kls.id')
        ->leftJoin('rombel AS r', 'rp.rombel_id', '=', 'r.id')
        // join riwayat domisili aktif
        ->leftjoin('riwayat_domisili AS rd', fn($join) => $join->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
        ->leftJoin('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
        // join berkas pas foto terakhir
        ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
        ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
        // join warga pesantren terakhir true (NIUP)
        ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
        ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
        ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
        // hanya yang berstatus aktif
        ->where('s.status', 'aktif')
        ->select($selects)
        ->orderBy('s.id');

        return $this->filterService->pelajarFilters($query, $this->request);
    }

    /**
     * Mapping setiap baris ke format Excel
     */
    public function map($row): array
    {
        $this->counter++;
        $out = [$this->counter];

        foreach ($this->selected as $key) {
            $out[] = $row->{$key} ?? '';
        }

        return $out;
    }

    /**
     * Heading kolom di Excel
     */
    public function headings(): array
    {
        $heads = ['No'];
        foreach ($this->selected as $key) {
            $heads[] = $this->availableColumns[$key]['label'];
        }
        return $heads;
    }

    /**
     * Override DefaultValueBinder supaya semua nilai di-set explicit sebagai STRING
     */
    public function bindValue(Cell $cell, $value)
    {
        $value = $value === null ? '' : $value;
        $cell->setValueExplicit($value, DataType::TYPE_STRING);
        return true;
    }

    /**
     * Override default value binder supaya semua nilai di-set explicit sebagai STRING
     */
    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        // styling header
        $lastCol = chr( ord('A') + count($this->selected) );
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true],
            'fill'      => ['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'F2F2F2']],
            'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER]
        ]);
    }

    /**
     * Event AfterSheet untuk freeze header dan vertical align
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function($e) {
                $e->sheet->freezePane('A2');
                $lastRow = $this->counter + 1;
                $lastCol = chr( ord('A') + count($this->selected) );
                $e->sheet->getDelegate()
                    ->getStyle("A1:{$lastCol}{$lastRow}")
                    ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            },
        ];
    }

    // untuk Responsable: method toResponse
    // public function toResponse($request)
    // {
    //     return $this->download($this->fileName, Excel::XLSX);
    // }
}
