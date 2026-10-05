<?php

namespace App\Exports\Inventario;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Collection;
use App\Exports\Inventario\General;
use App\Exports\Inventario\Summary;
use App\Models\Areas\Area;
use App\Models\Equipos\Equipo;

class Main implements WithMultipleSheets
{
    use Exportable;

    public Collection $areas;
    public Collection $equipos;

    public function __construct()
    {
        $this->areas = Area::all();

        $this->equipos = Equipo::with([
            'area',
            'caracteristicas',
            'componentes.caracteristicas',
        ])->get()->map(function ($equipo) {
            $equipo->setRelation('caracteristicas', $equipo->caracteristicas->keyBy('nombre'));

            $componentesModificados = $equipo->componentes->map(function ($componente) {
                $componente->setRelation('caracteristicas', $componente->caracteristicas->keyBy('nombre'));
                return $componente;
            })->keyBy('nombre');

            $equipo->setRelation('componentes', $componentesModificados);

            return $equipo;
        })
        ->groupBy('id_area')->map(function ($equipos) {
            return $equipos->groupBy('tipo');
        });
    }

    public function sheets(): array
    {
        $summary = $this->calculateSummary();
        $sheets = [
            'CONSOLIDADO GENERAL' => new Summary($summary),
        ];

        foreach ($this->areas as $area) {
            if(isset($summary->get($area->nombre)['total']) && $summary->get($area->nombre)['total'] > 0) {
                $sheets[$area->nombre] = new General($this->getEquiposByArea($area), $area);
            }
        }
        if( isset($summary->get('Sin Área')['total']) && $summary->get('Sin Área')['total'] > 0){
            $sheets['Sin Área'] = new General($this->getEquiposByArea(null), null);
        }

        return $sheets;
    }

    public function getEquiposByArea(?Area $area): Collection
    {
        if ($area) {
            return $this->equipos->get($area->id) ?? collect();
        }
        else {
            return $this->equipos->get("") ?? collect();
        }
    }

    public function calculateSummary(): Collection
    {
        $summary = collect();

        foreach ($this->areas as $area) {
            $total_area = 0;
            $equiposPorArea = $this->getEquiposByArea($area);

            $summary[$area->nombre] = $equiposPorArea->map(function ($equiposPorTipo) use (&$total_area) {
                
                $funcionales = $equiposPorTipo->filter(function ($equipo) {
                    return $equipo->caracteristicas->get('estado_funcional')->valor === 'funcional';
                })->count();
                $noFuncionales = $equiposPorTipo->filter(function ($equipo) {
                    return $equipo->caracteristicas->get('estado_funcional')->valor === 'no_funcional';
                })->count();
                $total_area = $total_area + $funcionales + $noFuncionales;
                return [
                    'funcionales' => $funcionales,
                    'no_funcionales' => $noFuncionales,
                ];
            });

            $summary[$area->nombre]['total'] = $total_area;
        }

        $total_sin_area = 0;
        $equiposSinArea = $this->getEquiposByArea(null);
        $summary['Sin Área'] = $equiposSinArea->map(function ($equiposPorTipo) use (&$total_sin_area) {
            $funcionales = $equiposPorTipo->filter(function ($equipo) {
                return $equipo->caracteristicas->get('estado_funcional')->valor === 'funcional';
            })->count();
            $noFuncionales = $equiposPorTipo->filter(function ($equipo) {
                return $equipo->caracteristicas->get('estado_funcional')->valor === 'no_funcional';
            })->count();
            $total_sin_area = $total_sin_area + $funcionales + $noFuncionales;
            return [
                'funcionales' => $funcionales,
                'no_funcionales' => $noFuncionales,
            ];
        });

        return $summary;
    }
}