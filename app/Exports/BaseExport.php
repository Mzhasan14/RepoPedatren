<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class BaseExport implements FromCollection, WithColumnFormatting, WithEvents, WithHeadings
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
                $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
                $colFormats[$colLetter] = NumberFormat::FORMAT_TEXT;
            }
        }

        return $colFormats;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $sheetDelegate = $sheet->getDelegate();

                // Freeze header
                $sheetDelegate->freezePane('A2');

                // Dapatkan kolom terakhir sebagai indeks
                $lastCol = $sheetDelegate->getHighestColumn();
                $lastColIndex = Coordinate::columnIndexFromString($lastCol);

                // Set auto size tiap kolom (ganti foreach -> for)
                for ($col = 1; $col <= $lastColIndex; $col++) {
                    $colLetter = Coordinate::stringFromColumnIndex($col);
                    $sheetDelegate->getColumnDimension($colLetter)->setAutoSize(true);
                }

                // Style header
                $cellRange = 'A1:' . $lastCol . '1';
                $sheetDelegate->getStyle($cellRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => '318CE7',
                        ],
                    ],
                ]);

                // Style data
                $lastRow = $sheetDelegate->getHighestRow();
                $dataRange = 'A2:' . $lastCol . $lastRow;
                $sheetDelegate->getStyle($dataRange)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                // Border all cells
                $allCell = 'A1:' . $lastCol . $lastRow;
                $sheetDelegate->getStyle($allCell)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFBBBBBB'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
