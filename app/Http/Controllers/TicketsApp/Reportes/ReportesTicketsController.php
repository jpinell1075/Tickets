<?php

namespace App\Http\Controllers\TicketsApp\Reportes;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

/**
 * Controlador para la emisión y configuración de Reportes de Tickets y Mantenimientos.
 */
class ReportesTicketsController extends Controller
{
    /**
     * Muestra la vista principal del catálogo de reportes.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('tickets_app.ticket_reports.index');
    }
}

