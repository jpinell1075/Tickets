<?php

namespace App\Http\Controllers\TicketsApp\Request;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Equipos\Equipo;
use App\Models\Tickets\Ticket;

class RequestController extends Controller
{
    public function index()
    {
        $user_equipos = Equipo::select('id', 'nombre')
        ->where('id_usuario', auth()->user()->id)->get()->toArray();
        $user_equipos = [];
        foreach (auth()->user()->equipos as $equipo) {
            $user_equipos[$equipo->id] = $equipo->nombre;
        }

        return Inertia::render('tickets_app.requests.index', [
            'user_equipos' => $user_equipos,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'tipo' => 'required|in:hardware,software,network,other',
            'id_equipo' => 'nullable|exists:equipos,id',
        ], [
            'titulo.required' => 'El título es obligatorio.',
            'description.required' => 'La descripción es obligatoria.',
            'tipo.required' => 'El tipo de solicitud es obligatorio.',
            'id_equipo.exists' => 'El equipo seleccionado no es válido.',
        ]);

        $ticket = new Ticket();
        $ticket->id_equipo = $request->id_equipo;
        $ticket->id_usuario = auth()->user()->id;
        $ticket->tipo = $request->tipo;
        $ticket->prioridad = 'baja';
        $ticket->titulo = $request->titulo;
        $ticket->descripcion = $request->description;
        $ticket->estado_actual = 'abierto';
        $ticket->fecha_creacion = now();

        if($ticket->save()) {
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
                broadcast(new \App\Events\Tickets\TicketKanbanUpdated($loadedTicket, 'created'));
            } catch (\Throwable $e) {}

            return redirect()->back()->with([
                'message' => [
                    'message' => 'Solicitud creada exitosamente.',
                    'type' => 'success',
                ],
            ]);
        }

        return redirect()->back()->with([
            'message' => [
                'message' => 'Error al crear la solicitud.',
                'type' => 'error',
            ],
        ]);
    }

    public function history()
    {
        $tickets = Ticket::where('id_usuario', auth()->user()->id)
            ->with(['equipo:id,nombre'])
            ->orderBy('fecha_creacion', 'desc')
            ->paginate();

        return Inertia::render('tickets_app.requests.history', [
            'tickets' => $tickets,
        ]);
    }
}
