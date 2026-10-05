<?php

namespace App\Http\Controllers\TicketsApp\Maintenances;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Tickets\PlanificacionMantenimiento;
use App\Models\Tickets\Ticket;
use App\Models\Areas\Area;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MaintenancesController extends Controller
{
    public function index()
    {
        return Inertia::render('tickets_app.maintenances.index', [
            'model' => PlanificacionMantenimiento::getStaticData(),
        ]);
    }

    public function show(PlanificacionMantenimiento $planificacionMantenimiento)
    {
        $planificacion = $planificacionMantenimiento->load('area', 'tickets.equipo', 'tickets.usuario', 'tickets.encargado');

        return Inertia::render('tickets_app.maintenances.show', [
            'planificacion' => $planificacion,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'id_area' => 'required|exists:areas,id',
        ], [
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'id_area.required' => 'El área es obligatoria.',
            'id_area.exists' => 'El área seleccionada no es válida.',
        ]);

        $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
        $fechaFin = Carbon::parse($request->fecha_fin)->startOfDay();
        $diasLaborables = $this->getWorkingDaysBetween($fechaInicio, $fechaFin);

        if (empty($diasLaborables)) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'El rango seleccionado no contiene días laborables (lunes a viernes).',
                    'type' => 'error',
                ],
            ]);
        }

        $area = Area::findOrFail($request->id_area);
        $equipos = $area->equipos()->get();

        DB::transaction(function () use ($request, $equipos, $diasLaborables) {
            $planificacion = PlanificacionMantenimiento::create([
                'id_area' => $request->id_area,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
            ]);

            $fechasAsignadas = $this->buildScheduledDates($equipos->count(), $diasLaborables);
            $usuarioId = auth()->id();

            foreach ($equipos as $index => $equipo) {
                Ticket::create([
                    'id_equipo' => $equipo->id,
                    'id_usuario' => $usuarioId,
                    'id_programacion_mantenimiento' => $planificacion->id,
                    'tipo' => 'other',
                    'prioridad' => 'media',
                    'titulo' => 'Mantenimiento planificado - ' . ($equipo->nombre ?? 'Equipo #' . $equipo->id),
                    'descripcion' => 'Ticket generado automáticamente desde planificación de mantenimiento.',
                    'estado_actual' => 'abierto',
                    'fecha_creacion' => now(),
                    'fecha_planificada' => $fechasAsignadas[$index] ?? $diasLaborables[0],
                ]);
            }
        });

        return redirect()->back()->with([
            'message' => [
                'message' => 'Planificación creada y tickets generados correctamente.',
                'type' => 'success',
            ],
        ]);
    }

    public function delete(PlanificacionMantenimiento $planificacionMantenimiento)
    {
        DB::transaction(function () use ($planificacionMantenimiento) {
            Ticket::where('id_programacion_mantenimiento', $planificacionMantenimiento->id)->delete();
            $planificacionMantenimiento->delete();
        });

        return redirect()->back()->with([
            'message' => [
                'message' => 'Planificación y tickets asociados eliminados correctamente.',
                'type' => 'success',
            ],
        ]);
    }

    private function getWorkingDaysBetween(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        $diasLaborables = [];
        $diaActual = $fechaInicio->copy();

        while ($diaActual->lte($fechaFin)) {
            if ($diaActual->isWeekday()) {
                $diasLaborables[] = $diaActual->format('Y-m-d');
            }

            $diaActual->addDay();
        }

        return $diasLaborables;
    }

    private function buildScheduledDates(int $ticketsCount, array $workingDays): array
    {
        if ($ticketsCount === 0 || empty($workingDays)) {
            return [];
        }

        $diasCount = count($workingDays);
        $fechas = [];

        if ($ticketsCount >= $diasCount) {
            $basePorDia = intdiv($ticketsCount, $diasCount);
            $extra = $ticketsCount % $diasCount;

            foreach ($workingDays as $index => $fecha) {
                $repeticiones = $basePorDia + ($index < $extra ? 1 : 0);

                for ($i = 0; $i < $repeticiones; $i++) {
                    $fechas[] = $fecha;
                }
            }

            return $fechas;
        }

        if ($ticketsCount === 1) {
            return [$workingDays[0]];
        }

        for ($i = 0; $i < $ticketsCount; $i++) {
            $posicion = (int) floor(($i * ($diasCount - 1)) / ($ticketsCount - 1));
            $fechas[] = $workingDays[$posicion];
        }

        return $fechas;
    }
}
