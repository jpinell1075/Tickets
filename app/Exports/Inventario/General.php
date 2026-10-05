<?php

namespace App\Exports\Inventario;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Collection;
use App\Models\Areas\Area;

class General implements FromView, WithEvents, WithTitle, WithDrawings
{
    protected Collection $equipos;
    protected ?Area $area;

    public function __construct(Collection $equipos, ?Area $area)
    {
        $this->equipos = $equipos;
        $this->area = $area;
    }

    public function view(): View
    {
        $equipos = $this->equipos;

        return view('exports.Inventario.general', compact('equipos'));
    }

    public function title(): string
    {
        return $this->area?->nombre ?? 'Sin Área';
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Banner');
        $drawing->setDescription('Banner');
        $drawing->setPath(public_path('img/banner_1.png'));
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(639.496063);
        $drawing->setOffsetY(23.055118);

        return [$drawing];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:XFD1048576')->applyFromArray([
                    'font' => [
                        'name' => 'Courier New',
                    ],
                ]);
                $sheet->mergeCells('A1:M11');

                $sheet->getStyle('A12:A15')->getFont()->setBold(true);
                $sheet->getStyle('A16:M17')->getFont()->setBold(true);

                $sheet->getColumnDimension('A')->setWidth(10.02, 'cm');
                $sheet->getColumnDimension('B')->setWidth(1.96, 'cm');
                $sheet->getColumnDimension('C')->setWidth(2.26, 'cm');
                $sheet->getColumnDimension('D')->setWidth(4.47, 'cm');
                $sheet->getColumnDimension('E')->setWidth(5.82, 'cm');
                $sheet->getColumnDimension('F')->setWidth(5.82, 'cm');
                $sheet->getColumnDimension('G')->setWidth(6.77, 'cm');
                $sheet->getColumnDimension('H')->setWidth(3.75, 'cm');
                $sheet->getColumnDimension('I')->setWidth(5.79, 'cm');
                $sheet->getColumnDimension('J')->setWidth(4.61, 'cm');
                $sheet->getColumnDimension('K')->setWidth(9.82, 'cm');
                $sheet->getColumnDimension('L')->setWidth(3.08, 'cm');
                $sheet->getColumnDimension('M')->setWidth(3.75, 'cm');

                $ranges = $this->calculateRows();

                foreach ($ranges as $tipo => $range) {
                    $startCell = 'A' . $range['start'];
                    $endCell = $range['end_column'] . $range['end'];
                    $sheet->getStyle($startCell . ':' . $endCell)->getAlignment()->setWrapText(true);

                    $headerCell = 'A' . ($range['start'] - 2) . ':' . $range['end_column'] . ($range['start'] - 1);
                    $sheet->getStyle($headerCell)->getFont()->setBold(true);

                    $firstRowCell = 'A' . ($range['start'] - 2);
                    $sheet->getStyle("{$firstRowCell}")->getFont()->setSize(20);
                }
            },
        ];
    }

    public function calculateRows()
    {
        $equipos = $this->equipos;

        $cpu_header_row = 17;
        $cpu_count = max(isset($equipos['cpu']) ? $equipos['cpu']->count() : 0, 1);
        $monitor_count = max(isset($equipos['monitor']) ? $equipos['monitor']->count() : 0, 1);
        $ups_count = max(isset($equipos['ups']) ? $equipos['ups']->count() : 0, 1);
        $switches_count = max(isset($equipos['switches']) ? $equipos['switches']->count() : 0, 1);
        $aps_count = max(isset($equipos['aps']) ? $equipos['aps']->count() : 0, 1);
        $telefonia_ip_count = max(isset($equipos['telefonia_ip']) ? $equipos['telefonia_ip']->count() : 0, 1);
        $impresoras_count = max(isset($equipos['impresoras']) ? $equipos['impresoras']->count() : 0, 1);
        $reloj_biometrico_count = max(isset($equipos['reloj_biometrico']) ? $equipos['reloj_biometrico']->count() : 0, 1);
        $data_show_count = max(isset($equipos['data_show']) ? $equipos['data_show']->count() : 0, 1);
        $pantalla_proyector_count = max(isset($equipos['pantalla_proyector']) ? $equipos['pantalla_proyector']->count() : 0, 1);
        $laptops_count = max(isset($equipos['laptops']) ? $equipos['laptops']->count() : 0, 1);

        $ranges = [];
        $ranges['cpu'] = [
            'start' => $cpu_header_row + 1,
            'end' => $cpu_header_row + $cpu_count,
            'end_column' => 'M',
        ];
        $ranges['monitor'] = [
            'start' => $ranges['cpu']['end'] + 3,
            'end' => $ranges['cpu']['end'] + 2 + $monitor_count,
            'end_column' => 'I',
        ];
        $ranges['ups'] = [
            'start' => $ranges['monitor']['end'] + 3,
            'end' => $ranges['monitor']['end'] + 2 + $ups_count,
            'end_column' => 'I',
        ];
        $ranges['switches'] = [
            'start' => $ranges['ups']['end'] + 3,
            'end' => $ranges['ups']['end'] + 2 + $switches_count,
            'end_column' => 'I',
        ];
        $ranges['aps'] = [
            'start' => $ranges['switches']['end'] + 3,
            'end' => $ranges['switches']['end'] + 2 + $aps_count,
            'end_column' => 'G',
        ];
        $ranges['telefonia_ip'] = [
            'start' => $ranges['aps']['end'] + 3,
            'end' => $ranges['aps']['end'] + 2 + $telefonia_ip_count,
            'end_column' => 'H',
        ];
        $ranges['impresoras'] = [
            'start' => $ranges['telefonia_ip']['end'] + 3,
            'end' => $ranges['telefonia_ip']['end'] + 2 + $impresoras_count,
            'end_column' => 'G',
        ];
        $ranges['reloj_biometrico'] = [
            'start' => $ranges['impresoras']['end'] + 3,
            'end' => $ranges['impresoras']['end'] + 2 + $reloj_biometrico_count,
            'end_column' => 'G',
        ];
        $ranges['data_show'] = [
            'start' => $ranges['reloj_biometrico']['end'] + 3,
            'end' => $ranges['reloj_biometrico']['end'] + 2 + $data_show_count,
            'end_column' => 'G',
        ];
        $ranges['pantalla_proyector'] = [
            'start' => $ranges['data_show']['end'] + 3,
            'end' => $ranges['data_show']['end'] + 2 + $pantalla_proyector_count,
            'end_column' => 'G',
        ];
        $ranges['laptops'] = [
            'start' => $ranges['pantalla_proyector']['end'] + 3,
            'end' => $ranges['pantalla_proyector']['end'] + 2 + $laptops_count,
            'end_column' => 'G',
        ];
        return $ranges;
    }
}