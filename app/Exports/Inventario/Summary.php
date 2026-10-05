<?php

namespace App\Exports\Inventario;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class Summary implements FromView, WithEvents, WithTitle
{
    protected Collection $data;
    protected int $totalAreas = 0;
    protected Collection $types;

    public function __construct(Collection $data)
    {
        $data = $data->filter(function ($tipos, $area) {
            $this->totalAreas++;
            return isset($tipos['total']) && $tipos['total'] > 0;
        });
        $this->data = $data;
        $this->types = collect(
            [
                'cpu' => 'CPU', 
                'monitor' => 'Monitor', 
                'ups' => 'BATERIAS UPS', 
                'switches' => 'SWITCH', 
                'aps' => 'APS', 
                'telefonia_ip' => 'TELEFONOS IP', 
                'impresoras' => 'IMPRESORAS', 
                'reloj_biometrico' => 'RELOJ BIOMETRICO', 
                'data_show' => 'DATA SHOW', 
                'pantalla_proyector' => 'PANTALLA DE PROYECCIÓN', 
                'laptops' => 'LAPTOPS'
            ]);
    }

    public function view(): View
    {
        $data = $this->data;
        $types = $this->types;

        return view('exports.Inventario.summary', compact('data', 'types'));
    }

    public function title(): string
    {
        return 'CONSOLIDADO GENERAL';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:XFD1048576')->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                    ],
                ]);

                $rows = $this->calculateRows();

                $sheet->getStyle('A2:' . $rows['final_column'] . '3')->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                $sheet->getStyle('A2:' . $rows['final_column'] . '2')->getFont()->setBold(true);
            },
        ];
    }

    public function calculateRows()
    {
        $totalcolumns = ($this->totalAreas > 0 ? $this->totalAreas : 1) * 8;
        $final_column = Coordinate::stringFromColumnIndex($totalcolumns);

        return [
            'totalcolumns' => $totalcolumns,
            'final_column' => $final_column,
        ];
    }
}