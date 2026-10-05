<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Class GeneralExcelExport
 * Handles the export of data to an Excel file with customizable styles and events.
 */
class GeneralExcelExport implements FromView, WithEvents
{
    /**
     * @var string The HTML content of the table to be exported.
     */
    public $table_html;

    /**
     * @var string|null The name of the file to be exported.
     */
    public $file_name;

    /**
     * @var array The styles configuration for the Excel file.
     */
    public $classStyles;

    /**
     * Constructor to initialize the export class.
     *
     * @param string $table_html The HTML content of the table.
     * @param string|null $file_name The name of the file (optional).
     */
    public function __construct($table_html, $file_name = null)
    {
        $this->table_html = $table_html;
        $this->file_name = $file_name;
        $this->classStyles = config('excelstyles');
    }

    /**
     * Returns the view to be used for the Excel export.
     *
     * @return View The view containing the table HTML.
     */
    public function view(): View
    {
        $html = $this->table_html;
        $file_name = $this->file_name;

        return view('exports.generalexcelexport', compact('html', 'file_name'));
    }

    /**
     * Registers events to customize the Excel sheet after it is created.
     *
     * @return array The array of events and their corresponding handlers.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                // Define default styles for the sheet
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ];

                // Define styles for the first row
                $firstRowStyle = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE,
                        ],
                    ],
                ];

                // Define general styles for the sheet
                $generalStyle = [
                    'font' => [
                        'size' => 12,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ];

                // Apply styles to the entire sheet
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray($generalStyle);
                $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray($firstRowStyle);

                // Iterate through each cell to apply specific styles based on content
                for ($row = 1; $row <= $highestRow; $row++) {
                    for ($col = 'A'; $col <= $highestColumn; $col++) {

                        $cell = $sheet->getCell("{$col}{$row}");
                        $cellValue = $cell->getValue();

                        // Check for custom styles in cell content
                        if (preg_match('/\{(.+?)\}(.*)/', $cellValue, $matches)) {
                            $classes = explode(' ', $matches[1]);
                            $value = $matches[2];

                            $sheet->getStyle("{$col}{$row}")->applyFromArray($styleArray);
                            foreach ($classes as $class) {
                                if (isset($this->classStyles[$class])) {
                                    $sheet->getStyle("{$col}{$row}")->applyFromArray($this->classStyles[$class]);
                                }
                            }

                            $cell->setValue($value);
                        }
                    }
                }

                // Auto-size all columns
                foreach (range('A', $sheet->getHighestColumn()) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}