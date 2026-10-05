<?php

namespace App\Http\Controllers\TicketsApp\TicketAdmin;

use App\Http\Controllers\Controller;
use App\Models\Areas\Area;
use App\Models\Equipos\Equipo;
use App\Models\Tickets\Mantenimiento;
use App\Models\Tickets\PlanificacionMantenimiento;
use App\Models\Tickets\Ticket;
use App\Models\User;
use App\Events\Tickets\TicketKanbanUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Controlador para la Administración de Tickets (Tablero Kanban en Tiempo Real).
 */
class TicketAdminController extends Controller
{
    /**
     * Muestra la vista principal del tablero Kanban.
     */
    public function index(Request $request)
    {
        $statuses = $request->input('statuses', ['abierto', 'en_proceso']);
        if (is_string($statuses)) {
            $statuses = explode(',', $statuses);
        }

        $tickets = Ticket::with([
            'equipo:id,nombre,cod_inventario,id_area',
            'equipo.area:id,nombre',
            'usuario:id,name,email,telefono',
            'encargado:id,name,email,especialidad',
            'programacionMantenimiento:id,id_area,fecha_inicio,fecha_fin',
            'programacionMantenimiento.area:id,nombre',
            'mantenimientos',
        ])
        ->whereIn('estado_actual', $statuses)
        ->orderBy('fecha_creacion', 'desc')
        ->get();

        $planificaciones = PlanificacionMantenimiento::with('area:id,nombre')
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        // Solo usuarios que pertenezcan al personal de TI (it_staff = true) y estén activos
        $encargados = User::select('id', 'name', 'especialidad', 'estado', 'it_staff')
            ->where('estado', 'activo')
            ->where('it_staff', true)
            ->orderBy('name', 'asc')
            ->get();

        $areas = Area::select('id', 'nombre')->orderBy('nombre', 'asc')->get();

        $equipos = Equipo::select('id', 'nombre', 'cod_inventario', 'id_area')
            ->orderBy('nombre', 'asc')
            ->get();

        $statusCounts = Ticket::selectRaw('estado_actual, count(*) as total')
            ->groupBy('estado_actual')
            ->pluck('total', 'estado_actual')
            ->toArray();

        return Inertia::render('tickets_app.ticket_admin.index', [
            'initialTickets' => $tickets,
            'planificaciones' => $planificaciones,
            'encargados' => $encargados,
            'areas' => $areas,
            'equipos' => $equipos,
            'initialStatuses' => $statuses,
            'globalStatusCounts' => $statusCounts,
        ]);
    }

    /**
     * Retorna los datos de tickets en formato JSON para el polling en tiempo real.
     */
    public function data(Request $request)
    {
        $statuses = $request->input('statuses', ['abierto', 'en_proceso']);
        if (is_string($statuses)) {
            $statuses = explode(',', $statuses);
        }

        $tickets = Ticket::with([
            'equipo:id,nombre,cod_inventario,id_area',
            'equipo.area:id,nombre',
            'usuario:id,name,email,telefono',
            'encargado:id,name,email,especialidad',
            'programacionMantenimiento:id,id_area,fecha_inicio,fecha_fin',
            'programacionMantenimiento.area:id,nombre',
            'mantenimientos',
        ])
        ->whereIn('estado_actual', $statuses)
        ->orderBy('fecha_creacion', 'desc')
        ->get();

        $planificaciones = PlanificacionMantenimiento::with('area:id,nombre')
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        // Solo usuarios de personal de TI (it_staff = true)
        $encargados = User::select('id', 'name', 'especialidad', 'estado', 'it_staff')
            ->where('estado', 'activo')
            ->where('it_staff', true)
            ->orderBy('name', 'asc')
            ->get();

        $statusCounts = Ticket::selectRaw('estado_actual, count(*) as total')
            ->groupBy('estado_actual')
            ->pluck('total', 'estado_actual')
            ->toArray();

        return response()->json([
            'tickets' => $tickets,
            'planificaciones' => $planificaciones,
            'encargados' => $encargados,
            'globalStatusCounts' => $statusCounts,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Actualiza la prioridad de un ticket con 1 clic.
     */
    public function updatePriority(Request $request, Ticket $ticket)
    {
        if (in_array($ticket->estado_actual, ['resuelto', 'cerrado', 'cancelado'])) {
            return response()->json([
                'message' => 'Los tickets resueltos, cerrados o cancelados no pueden ser modificados.',
            ], 422);
        }

        $request->validate([
            'prioridad' => 'required|in:baja,media,alta,critica',
        ], [
            'prioridad.required' => 'La prioridad es obligatoria.',
            'prioridad.in' => 'La prioridad seleccionada no es válida.',
        ]);

        $ticket->prioridad = $request->prioridad;
        $ticket->save();

        $this->broadcastTicketChange($ticket, 'priority_updated');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Prioridad actualizada con éxito.',
                'ticket' => $ticket->load(['equipo', 'usuario', 'encargado']),
            ]);
        }

        return redirect()->back()->with([
            'message' => [
                'message' => 'Prioridad actualizada correctamente.',
                'type' => 'success',
            ],
        ]);
    }

    /**
     * Actualiza el tipo de problema del ticket con 1 clic.
     */
    public function updateType(Request $request, Ticket $ticket)
    {
        if (in_array($ticket->estado_actual, ['resuelto', 'cerrado', 'cancelado'])) {
            return response()->json([
                'message' => 'Los tickets resueltos, cerrados o cancelados no pueden ser modificados.',
            ], 422);
        }

        $request->validate([
            'tipo' => 'required|in:hardware,software,network,other,preventivo,correctivo',
        ], [
            'tipo.required' => 'El tipo de problema es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
        ]);

        $ticket->tipo = $request->tipo;
        $ticket->save();

        $this->broadcastTicketChange($ticket, 'type_updated');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Tipo de solicitud actualizado con éxito.',
                'ticket' => $ticket->load(['equipo', 'usuario', 'encargado']),
            ]);
        }

        return redirect()->back()->with([
            'message' => [
                'message' => 'Tipo de solicitud actualizado correctamente.',
                'type' => 'success',
            ],
        ]);
    }

    /**
     * Asigna o reasigna el encargado / técnico responsable con 1 clic.
     */
    public function updateEncargado(Request $request, Ticket $ticket)
    {
        if (in_array($ticket->estado_actual, ['resuelto', 'cerrado', 'cancelado'])) {
            return response()->json([
                'message' => 'Los tickets resueltos, cerrados o cancelados no pueden ser modificados.',
            ], 422);
        }

        $request->validate([
            'id_encargado' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('it_staff', true)->where('estado', 'activo');
                }),
            ],
        ], [
            'id_encargado.exists' => 'El encargado seleccionado debe pertenecer al personal de TI (it_staff) y estar activo.',
        ]);

        $ticket->id_encargado = $request->id_encargado;
        $ticket->save();

        $this->broadcastTicketChange($ticket, 'assigned');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Encargado actualizado con éxito.',
                'ticket' => $ticket->load(['equipo', 'usuario', 'encargado']),
            ]);
        }

        return redirect()->back()->with([
            'message' => [
                'message' => 'Encargado actualizado correctamente.',
                'type' => 'success',
            ],
        ]);
    }

    /**
     * Actualiza el estado del ticket y gestiona resolución / mantenimiento.
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        if (in_array($ticket->estado_actual, ['resuelto', 'cerrado', 'cancelado'])) {
            return response()->json([
                'message' => 'Los tickets en estado resuelto, cerrado o cancelado se encuentran finalizados y no pueden cambiar de estado.',
            ], 422);
        }

        $request->validate([
            'estado_actual' => 'required|in:abierto,en_proceso,resuelto,cerrado,cancelado',
            'observacion_resolucion' => 'nullable|string|max:2000',
            'create_maintenance' => 'nullable|boolean',
            'maintenance_observation' => 'nullable|string|max:2000',
        ], [
            'estado_actual.required' => 'El estado actual es obligatorio.',
            'estado_actual.in' => 'El estado seleccionado no es válido.',
        ]);

        DB::transaction(function () use ($request, $ticket) {
            $nuevoEstado = $request->estado_actual;
            $ticket->estado_actual = $nuevoEstado;

            if (in_array($nuevoEstado, ['resuelto', 'cerrado', 'cancelado'])) {
                if ($request->filled('observacion_resolucion')) {
                    $ticket->observacion_resolucion = $request->observacion_resolucion;
                }
                $ticket->fecha_resolucion = now();

                // Registro automático de mantenimiento si se solicita y hay equipo asociado
                if ($request->boolean('create_maintenance') && $ticket->id_equipo) {
                    Mantenimiento::create([
                        'id_ticket' => $ticket->id,
                        'id_equipo' => $ticket->id_equipo,
                        'id_encargado' => $ticket->id_encargado ?? auth()->id(),
                        'tipo' => $request->maintenance_type ?? 'correctivo',
                        'fecha' => now(),
                        'observacion' => $request->maintenance_observation 
                            ?? $request->observacion_resolucion 
                            ?? 'Mantenimiento registrado tras resolución del ticket #' . $ticket->id,
                    ]);
                }
            } elseif (in_array($nuevoEstado, ['abierto', 'en_proceso'])) {
                // Si se reabre, se limpia la fecha de resolución
                $ticket->fecha_resolucion = null;
            }

            $ticket->save();
        });

        $this->broadcastTicketChange($ticket, in_array($request->estado_actual, ['resuelto', 'cerrado', 'cancelado']) ? 'resolved' : 'status_updated');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Estado del ticket actualizado con éxito.',
                'ticket' => $ticket->load(['equipo', 'usuario', 'encargado', 'mantenimientos']),
            ]);
        }

        return redirect()->back()->with([
            'message' => [
                'message' => 'Estado del ticket actualizado correctamente.',
                'type' => 'success',
            ],
        ]);
    }

    /**
     * Actualiza la información completa de un ticket.
     */
    public function update(Request $request, Ticket $ticket)
    {
        if (in_array($ticket->estado_actual, ['resuelto', 'cerrado', 'cancelado'])) {
            return response()->json([
                'message' => 'Los tickets resueltos, cerrados o cancelados no pueden ser modificados.',
            ], 422);
        }
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:2000',
            'tipo' => 'required|in:hardware,software,network,other,preventivo,correctivo',
            'prioridad' => 'required|in:baja,media,alta,critica',
            'estado_actual' => 'required|in:abierto,en_proceso,resuelto,cerrado,cancelado',
            'id_equipo' => 'nullable|exists:equipos,id',
            'id_encargado' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('it_staff', true)->where('estado', 'activo');
                }),
            ],
            'observacion_resolucion' => 'nullable|string|max:2000',
            'fecha_planificada' => 'nullable|date',
        ], [
            'titulo.required' => 'El título es obligatorio.',
            'tipo.required' => 'El tipo es obligatorio.',
            'prioridad.required' => 'La prioridad es obligatoria.',
            'estado_actual.required' => 'El estado actual es obligatorio.',
        ]);

        $ticket->fill($request->only([
            'titulo',
            'descripcion',
            'tipo',
            'prioridad',
            'estado_actual',
            'id_equipo',
            'id_encargado',
            'observacion_resolucion',
            'fecha_planificada',
        ]));

        if (in_array($ticket->estado_actual, ['resuelto', 'cerrado', 'cancelado']) && !$ticket->fecha_resolucion) {
            $ticket->fecha_resolucion = now();
        }

        $ticket->save();

        $this->broadcastTicketChange($ticket, 'updated');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Ticket actualizado con éxito.',
                'ticket' => $ticket->load(['equipo', 'usuario', 'encargado', 'mantenimientos']),
            ]);
        }

        return redirect()->back()->with([
            'message' => [
                'message' => 'Ticket actualizado correctamente.',
                'type' => 'success',
            ],
        ]);
    }

    /**
     * Elimina un ticket (Soft Delete).
     */
    public function delete(Ticket $ticket)
    {
        $ticketId = $ticket->id;
        $ticketState = $ticket->estado_actual;
        $ticket->delete();

        try {
            broadcast(new TicketKanbanUpdated([
                'id' => $ticketId,
                'estado_actual' => $ticketState,
            ], 'deleted'));
        } catch (\Throwable $e) {
            Log::warning('Error broadcasting ticket delete: ' . $e->getMessage());
        }

        return redirect()->back()->with([
            'message' => [
                'message' => 'Ticket eliminado correctamente.',
                'type' => 'success',
            ],
        ]);
    }

    /**
     * Helper para emitir cambios en tiempo real vía WebSockets sin bloquear si Reverb no está activo.
     */
    protected function broadcastTicketChange(Ticket $ticket, string $action): void
    {
        try {
            $loadedTicket = $ticket->load([
                'equipo:id,nombre,cod_inventario,id_area',
                'equipo.area:id,nombre',
                'usuario:id,name,email,telefono',
                'encargado:id,name,email,especialidad',
                'programacionMantenimiento:id,id_area,fecha_inicio,fecha_fin',
                'programacionMantenimiento.area:id,nombre',
                'mantenimientos',
            ]);

            broadcast(new TicketKanbanUpdated($loadedTicket, $action));
        } catch (\Throwable $e) {
            Log::warning('Error broadcasting ticket update: ' . $e->getMessage());
        }
    }
}

