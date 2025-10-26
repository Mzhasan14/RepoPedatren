<?php

namespace App\Exports\PesertaDidik;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class SantriExport implements FromCollection, WithColumnFormatting, WithEvents, WithHeadings
{
    protected $data;

    protected $headings;

    public function __construct($data, $headings)
    {
        $this->data = $data;
        $this->headings = $headings;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function columnFormats(): array
    {
        $colFormats = [];
        $asText = ['No. KK', 'NIK', 'No Passport', 'NIUP', 'NIS'];
        foreach ($asText as $label) {
            $idx = array_search($label, $this->headings);
            if ($idx !== false) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
                $colFormats[$colLetter] = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT;
            }
        }

        return $colFormats;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $sheet->getDelegate()->freezePane('A2');
                foreach (range('A', $sheet->getDelegate()->getHighestColumn()) as $col) {
                    $sheet->getDelegate()->getColumnDimension($col)->setAutoSize(true);
                }
                $cellRange = 'A1:'.$sheet->getDelegate()->getHighestColumn().'1';
                $sheet->getDelegate()->getStyle($cellRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => '318CE7',
                        ],
                    ],
                ]);
                $dataRange = 'A2:'.$sheet->getDelegate()->getHighestColumn().$sheet->getDelegate()->getHighestRow();
                $sheet->getDelegate()->getStyle($dataRange)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);
                $lastRow = $sheet->getDelegate()->getHighestRow();
                $lastCol = $sheet->getDelegate()->getHighestColumn();
                $allCell = 'A1:'.$lastCol.$lastRow;
                $sheet->getDelegate()->getStyle($allCell)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FFBBBBBB'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
