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
use App\Services\PesertaDidik\Filters\FilterKhadamService;

class KhadamExport implements
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

    private string $fileName = 'pelajar.xlsx';
    private Request $request;
    private FilterKhadamService $filterService;
    private array $availableColumns;
    private array $selected;
    private int $counter = 0;

    public function __construct(Request $request, FilterKhadamService $filterService)
    {
        $this->request       = $request;
        $this->filterService = $filterService;

        // Definisikan kolom dan ekspresi SQL-nya sebagai string
        $this->availableColumns = [
            // 'id'               => ['label' => 'Id',               'expr' => 'kh.id'],
            // 'nama'             => ['label' => 'Nama',             'expr' => 'b.nama'],
            'no_kk'            => ['label' => 'No KK',            'expr' => 'k.no_kk'],
            'identitas'        => ['label' => 'Identitas',        'expr' => 'COALESCE(b.nik, b.no_passport)'],
            'niup'             => ['label' => 'NIUP',             'expr' => 'wp.niup'],
            'anak_ke'          => ['label' => 'Anak Ke',          'expr' => 'b.anak_keberapa'],
            'jumlah_saudara'   => ['label' => 'Jumlah Saudara',   'expr' => 'COALESCE(siblings.jumlah_saudara, 0)'],
            'alamat'           => ['label' => 'Alamat',           'expr' => "CONCAT(b.jalan, ', ', kc.nama_kecamatan, ', ', kb.nama_kabupaten, ', ', pv.nama_provinsi)"],
            'domisili'         => ['label' => 'Domisili',         'expr' => "CONCAT(km.nama_kamar, ', ', bl.nama_blok, ', ', w.nama_wilayah)"],
            'pendidikan'   => ['label' => 'Pendidikan Terakhir',   'expr' => 'l.nama_lembaga'],
            'ibu'              => ['label' => 'Ibu Kandung',      'expr' => 'parents.nama_ibu'],
            'ayah'              => ['label' => 'Ayah Kandung',      'expr' => 'parents.nama_ayah'],
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
        $query = DB::table('khadam as kh')
            ->join('biodata as b', 'kh.biodata_id', '=', 'b.id')
            ->leftjoin('santri as s', 's.biodata_id', '=', 'b.id')
            ->leftJoin('riwayat_pendidikan as rp', 's.id', '=', 'rp.santri_id')
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
            ->leftjoin('lembaga as l', 'l.id', 'rp.lembaga_id')
            ->leftJoin(
                'riwayat_domisili as rd',
                fn($join) =>
                $join->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif')
            )
            ->leftJoin('wilayah as w', 'rd.wilayah_id', '=', 'w.id')
            ->leftJoin('blok as bl',     'rd.blok_id',     '=', 'bl.id')
            ->leftJoin('kamar as km',    'rd.kamar_id',    '=', 'km.id')
            ->whereNull('s.id')
            ->orWhere(fn($query) =>
                $query
                    ->where('s.status', 'aktif')
                    ->orWhere('rp.status', 'aktif')
            )
            ->orderBy('kh.id');

        // Tambahkan SELECT sesuai kolom terpilih
        foreach ($this->selected as $key) {
            $expr = $this->availableColumns[$key]['expr'];
            $query->addSelect(DB::raw("{$expr} as {$key}"));
        }

        // Terapkan filter bisnis dan kembalikan query
        return $this->filterService->khadamFilters($query, $this->request);
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
